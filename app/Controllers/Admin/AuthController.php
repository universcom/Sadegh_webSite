<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Url;
use App\Core\View;

final class AuthController
{
    public function showLogin(Request $request): never
    {
        if (Auth::check($request)) {
            Response::redirect(Url::admin());
        }

        $this->render();
    }

    public function login(Request $request): never
    {
        if (Auth::check($request)) {
            Response::redirect(Url::admin());
        }

        if (!Csrf::check($request)) {
            $this->render('Your session expired. Please try again.', (string) $request->input('email', ''));
        }

        $email    = (string) $request->input('email', '');
        $password = (string) ($request->raw('password') ?? '');

        if ($email === '' || $password === '') {
            $this->render('Please enter both your e-mail address and password.', $email);
        }

        // Throttling is checked before the credential comparison so a locked-out
        // identity gets the same answer regardless of whether the password is right.
        if (Auth::isLockedOut($email, $request->ip())) {
            $minutes = (int) ceil(Auth::secondsUntilRetry($email, $request->ip()) / 60);

            Logger::warning('Blocked sign-in attempt during lockout', ['ip' => $request->ip()]);

            $this->render(
                sprintf('Too many failed attempts. Please try again in %d minute%s.', max(1, $minutes), $minutes === 1 ? '' : 's'),
                $email
            );
        }

        if (!Auth::attempt($email, $password, $request)) {
            // Deliberately identical wording for an unknown account and a wrong
            // password, so the form cannot be used to enumerate users.
            $this->render('Those credentials do not match our records.', $email);
        }

        $intended = (string) Session::get('_intended', '');
        Session::forget('_intended');

        $target = ($intended !== '' && str_starts_with($intended, '/admin'))
            ? Url::to(ltrim($intended, '/'))
            : Url::admin();

        Session::flash('success', 'Welcome back.');

        Response::redirect($target);
    }

    public function logout(Request $request): never
    {
        // A logout is a state change, so it is POST-only and CSRF-protected.
        if (!Csrf::check($request)) {
            Response::redirect(Url::admin());
        }

        Auth::logout();

        Session::start();
        Session::flash('success', 'You have been signed out.');

        Response::redirect(Url::admin('login'));
    }

    private function render(string $error = '', string $email = ''): never
    {
        $status = $error === '' ? 200 : 422;

        Response::html(
            View::render('admin.login', ['error' => $error, 'email' => $email]),
            $status
        );
    }
}
