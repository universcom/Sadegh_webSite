<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\ContactMessage;
use App\Models\Product;

final class MessageController extends AdminController
{
    public function index(Request $request): never
    {
        $filters = [
            'status' => (string) ($request->query('status') ?? ''),
            'search' => (string) ($request->query('q') ?? ''),
        ];

        $this->view('admin.messages.index', [
            'pageTitle' => 'Messages',
            'activeNav' => 'messages',
            'result'    => ContactMessage::listing($filters, max(1, $request->integer('page', 1)), 20),
            'filters'   => $filters,
        ]);
    }

    public function show(Request $request, array $params): never
    {
        $id      = (int) $params['id'];
        $message = ContactMessage::find($id);

        if ($message === null) {
            $this->back($this->adminUrl('messages'), 'error', 'That message no longer exists.');
        }

        // Opening a new enquiry marks it read; a later status is left alone.
        ContactMessage::markRead($id);

        $product = null;
        if (!empty($message['product_id'])) {
            $product = \App\Core\Database::instance()->first(
                'SELECT p.id, p.slug, t.name
                 FROM products p
                 LEFT JOIN product_translations t ON t.product_id = p.id AND t.lang = :lang
                 WHERE p.id = :id LIMIT 1',
                ['id' => (int) $message['product_id'], 'lang' => \App\Core\Lang::default()]
            );
        }

        $this->view('admin.messages.show', [
            'pageTitle' => 'Message from ' . $message['name'],
            'activeNav' => 'messages',
            'message'   => $message,
            'product'   => $product,
        ]);
    }

    public function status(Request $request, array $params): never
    {
        $id  = (int) $params['id'];
        $url = (string) ($request->input('redirect') ?: $this->adminUrl('messages/' . $id));

        $this->requireCsrf($request, $url);

        $status = (string) $request->input('status', '');

        if (!ContactMessage::setStatus($id, $status)) {
            $this->back($url, 'error', 'That status is not valid.');
        }

        $this->back($url, 'success', 'Message marked as ' . $status . '.');
    }

    public function destroy(Request $request, array $params): never
    {
        $this->requireCsrf($request, $this->adminUrl('messages'));
        $this->requireAdmin($this->adminUrl('messages'));

        ContactMessage::delete((int) $params['id']);

        $this->back($this->adminUrl('messages'), 'success', 'Message deleted.');
    }

    public function export(Request $request): never
    {
        $csv = ContactMessage::toCsv([
            'status' => (string) ($request->query('status') ?? ''),
            'search' => (string) ($request->query('q') ?? ''),
        ]);

        Response::download($csv, 'enquiries-' . date('Y-m-d') . '.csv');
    }
}
