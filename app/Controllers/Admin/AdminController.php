<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Url;
use App\Core\View;
use App\Models\ContactMessage;

/**
 * Shared behaviour for the administration panel: layout rendering, CSRF
 * enforcement on writes, and role checks.
 */
abstract class AdminController
{
    /** Render an admin screen inside the admin layout. */
    protected function view(string $template, array $data = [], int $status = 200): never
    {
        $data += [
            'admin'       => Auth::user(),
            'unreadCount' => $this->safeUnreadCount(),
            'activeNav'   => $data['activeNav'] ?? '',
            'pageTitle'   => $data['pageTitle'] ?? '',
        ];

        Response::html(View::renderWithLayout($template, 'layouts.admin', $data), $status);
    }

    private function safeUnreadCount(): int
    {
        try {
            return ContactMessage::unreadCount();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Every state-changing request must carry a valid CSRF token. A failure
     * redirects back with a message rather than throwing.
     */
    protected function requireCsrf(Request $request, string $redirectTo): void
    {
        if (Csrf::check($request)) {
            return;
        }

        Session::flash('error', 'Your session expired. Please try again.');
        Response::redirect($redirectTo);
    }

    /** Restrict an action to owner/admin roles. */
    protected function requireAdmin(string $redirectTo): void
    {
        if (Auth::isAdmin()) {
            return;
        }

        Session::flash('error', 'You do not have permission to perform that action.');
        Response::redirect($redirectTo);
    }

    protected function requireOwner(string $redirectTo): void
    {
        if (Auth::isOwner()) {
            return;
        }

        Session::flash('error', 'Only an owner may manage administrator accounts.');
        Response::redirect($redirectTo);
    }

    protected function back(string $url, string $type, string $message): never
    {
        Session::flash($type, $message);

        Response::redirect($url);
    }

    /** Languages the admin forms expose tabs for. */
    protected function locales(): array
    {
        return Lang::enabled();
    }

    /**
     * Pull a per-language field group out of a submitted form.
     * Inputs are named tr[fa][name], tr[en][name], …
     *
     * @return array<string,array<string,string>>
     */
    protected function translationInput(Request $request, array $fields): array
    {
        $raw          = $request->raw('tr', []);
        $translations = [];

        if (!is_array($raw)) {
            return $translations;
        }

        foreach ($this->locales() as $locale) {
            $values = is_array($raw[$locale] ?? null) ? $raw[$locale] : [];
            $row    = [];

            foreach ($fields as $field) {
                $value       = $values[$field] ?? '';
                $row[$field] = is_string($value) ? trim($value) : '';
            }

            $translations[$locale] = $row;
        }

        return $translations;
    }

    protected function adminUrl(string $path = ''): string
    {
        return Url::admin($path);
    }
}
