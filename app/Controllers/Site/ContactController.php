<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Controllers\Controller;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Logger;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Url;
use App\Core\Validator;
use App\Core\View;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;

final class ContactController extends Controller
{
    /** Maximum enquiries accepted from one IP address per hour. */
    private const HOURLY_LIMIT = 5;

    public function show(Request $request): never
    {
        $this->render($request);
    }

    public function submit(Request $request): never
    {
        // 1. CSRF. A stale token means an expired session, not an attack, so the
        //    message is friendly and the form is redisplayed with its values.
        if (!Csrf::check($request)) {
            Session::flash('error', Lang::get('contact.csrf'));
            Session::flashInput($request->all());

            Response::redirect(Url::contact());
        }

        // 2. Spam heuristics: an untouched honeypot and a plausible fill time.
        if (trim((string) $request->input('website', '')) !== '') {
            // Silently accept-and-drop so the bot sees success and moves on.
            Session::flash('success', Lang::get('contact.success'));

            Response::redirect(Url::contact());
        }

        $elapsed = time() - (int) $request->input('form_time', 0);
        if ($elapsed < 3) {
            Session::flash('error', Lang::get('contact.throttled'));
            Session::flashInput($request->all());

            Response::redirect(Url::contact());
        }

        // 3. Per-IP rate limit.
        if (ContactMessage::recentFromIp($request->ip()) >= self::HOURLY_LIMIT) {
            Session::flash('error', Lang::get('contact.throttled'));

            Response::redirect(Url::contact());
        }

        // 4. Server-side validation, mirroring the client-side constraints.
        $validator = Validator::make($request->all(), [
            'name'    => 'required|min:2|max:120',
            'email'   => 'required|email|max:190',
            'phone'   => 'required|phone|max:40',
            'company' => 'max:190',
            'subject' => 'required|min:3|max:190',
            'message' => 'required|min:10|max:4000|no_urls',
        ], [
            'name'    => Lang::get('contact.name'),
            'email'   => Lang::get('contact.email_label'),
            'phone'   => Lang::get('contact.phone_label'),
            'company' => Lang::get('contact.company'),
            'subject' => Lang::get('contact.subject'),
            'message' => Lang::get('contact.message'),
        ]);

        if ($validator->fails()) {
            $this->render($request, $validator->errors(), $request->all(), 422);
        }

        // 5. Persist first — storage must never depend on mail succeeding.
        $productId = $request->integer('product_id', 0);

        $id = ContactMessage::create([
            'name'       => (string) $request->input('name'),
            'email'      => (string) $request->input('email'),
            'phone'      => (string) $request->input('phone'),
            'company'    => (string) $request->input('company', '') ?: null,
            'subject'    => (string) $request->input('subject'),
            'message'    => (string) $request->input('message'),
            'status'     => 'new',
            'lang'       => Lang::current(),
            'product_id' => $productId > 0 ? $productId : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // 6. Notify, best effort. A delivery failure is logged, never shown.
        $this->notify($id, $request);

        Session::flash('success', Lang::get('contact.success'));

        // Redirect after POST so a refresh cannot resubmit the enquiry.
        Response::redirect(Url::contact());
    }

    private function notify(int $id, Request $request): void
    {
        $to = (string) Config::get('mail.notify_to', '');

        if ($to === '') {
            $to = Setting::emails()[0] ?? '';
        }

        if ($to === '') {
            return;
        }

        $rows = [
            Lang::get('contact.name')        => (string) $request->input('name'),
            Lang::get('contact.email_label') => (string) $request->input('email'),
            Lang::get('contact.phone_label') => (string) $request->input('phone'),
            Lang::get('contact.company')     => (string) $request->input('company', '—'),
            Lang::get('contact.subject')     => (string) $request->input('subject'),
        ];

        $html = '<div style="font-family:Tahoma,Arial,sans-serif;font-size:14px;line-height:1.9;'
              . 'direction:' . Lang::direction() . ';text-align:' . (Lang::isRtl() ? 'right' : 'left') . '">'
              . '<h2 style="color:#353a96;margin:0 0 12px">' . e(Lang::get('contact.form_title')) . '</h2>'
              . '<table cellpadding="6" style="border-collapse:collapse">';

        foreach ($rows as $label => $value) {
            $html .= '<tr>'
                . '<td style="border:1px solid #e0e3ea;background:#f7f8fb;font-weight:bold">' . e($label) . '</td>'
                . '<td style="border:1px solid #e0e3ea">' . e($value) . '</td>'
                . '</tr>';
        }

        $html .= '</table>'
              . '<p style="margin-top:16px;font-weight:bold">' . e(Lang::get('contact.message')) . '</p>'
              . '<p style="white-space:pre-wrap;background:#f7f8fb;padding:12px;border-radius:6px">'
              . e((string) $request->input('message')) . '</p>'
              . '<p style="color:#6b7385;font-size:12px">'
              . e(Url::admin('messages/' . $id)) . '</p></div>';

        $sent = Mailer::send(
            $to,
            '[' . Setting::get('site_name', 'Rahyaft Sanat') . '] '
                . Lang::get('contact.form_title') . ' — ' . (string) $request->input('subject'),
            $html,
            (string) $request->input('email'),
            (string) $request->input('name')
        );

        if ($sent) {
            \App\Core\Database::instance()->update(
                'contact_messages',
                ['notified' => 1],
                'id = :id',
                ['id' => $id]
            );
        } else {
            Logger::warning('Contact notification not delivered', ['message_id' => $id]);
        }
    }

    private function render(Request $request, array $errors = [], array $old = [], int $status = 200): never
    {
        $page = Page::findBySlug('contact') ?? [];

        // A product enquiry arrives as ?product=slug and prefills the subject.
        $subjectHint = '';
        $productId   = null;
        $slug        = (string) ($request->query('product') ?? '');

        if ($slug !== '') {
            $product = Product::findBySlug($slug);

            if ($product !== null) {
                $productId   = (int) $product['id'];
                $subjectHint = Lang::get('contact.subject_product', ['product' => (string) $product['name']]);
            }
        }

        if ($errors !== []) {
            Session::flash('error', Lang::get('contact.error'));
        }

        $meta = $page !== []
            ? $this->meta($page, 'title', 'subtitle')
            : ['metaTitle' => Lang::get('contact.title'), 'metaDescription' => Lang::get('contact.lead')];

        View::start('schema');
        echo '<script type="application/ld+json">' . $this->breadcrumbSchema([
            ['name' => Lang::get('common.home'),   'url' => Url::home()],
            ['name' => Lang::get('nav.contact'),   'url' => Url::contact()],
        ]) . '</script>';
        View::stop();

        $this->view('site.contact', $meta + [
            'page'        => $page,
            'errors'      => $errors,
            'old'         => $old !== [] ? $old : Session::oldInput(),
            'subjectHint' => $subjectHint,
            'productId'   => $productId,
            'canonical'   => $this->canonical('/' . Lang::current() . '/contact'),
        ], $status);
    }
}
