<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Validator;
use App\Models\AdminUser;

final class UserController extends AdminController
{
    public function index(Request $request): never
    {
        $this->requireOwner($this->adminUrl());

        $this->view('admin.users.index', [
            'pageTitle' => 'Administrators',
            'activeNav' => 'users',
            'users'     => AdminUser::all(),
            'errors'    => [],
        ]);
    }

    public function store(Request $request): never
    {
        $url = $this->adminUrl('users');

        $this->requireCsrf($request, $url);
        $this->requireOwner($url);

        $name     = (string) $request->input('name', '');
        $email    = (string) $request->input('email', '');
        $password = (string) ($request->raw('password') ?? '');
        $role     = (string) $request->input('role', 'editor');

        $validator = Validator::make([
            'name'                  => $name,
            'email'                 => $email,
            'password'              => $password,
            'password_confirmation' => (string) ($request->raw('password_confirmation') ?? ''),
        ], [
            'name'     => 'required|min:2|max:120',
            'email'    => 'required|email|max:190',
            'password' => 'required|min:10|max:200|confirmed',
        ]);

        if ($validator->fails()) {
            $this->back($url, 'error', (string) $validator->first());
        }

        if (AdminUser::emailExists($email)) {
            $this->back($url, 'error', 'An administrator with that e-mail address already exists.');
        }

        AdminUser::create($name, $email, $password, in_array($role, AdminUser::ROLES, true) ? $role : 'editor');

        $this->back($url, 'success', 'Administrator created.');
    }

    public function update(Request $request, array $params): never
    {
        $url = $this->adminUrl('users');
        $id  = (int) $params['id'];

        $this->requireCsrf($request, $url);
        $this->requireOwner($url);

        $user = AdminUser::find($id);
        if ($user === null) {
            $this->back($url, 'error', 'That administrator no longer exists.');
        }

        $role     = (string) $request->input('role', $user['role']);
        $isActive = $request->boolean('is_active');

        // Never let the last active owner be demoted or deactivated — that would
        // lock everyone out of the panel.
        $wouldRemoveLastOwner = $user['role'] === 'owner'
            && ($role !== 'owner' || !$isActive)
            && AdminUser::activeOwnerCount() <= 1;

        if ($wouldRemoveLastOwner) {
            $this->back($url, 'error', 'This is the only active owner. Promote another owner first.');
        }

        $data = [
            'name'      => (string) $request->input('name', $user['name']),
            'role'      => in_array($role, AdminUser::ROLES, true) ? $role : $user['role'],
            'is_active' => $isActive ? 1 : 0,
        ];

        $password = (string) ($request->raw('password') ?? '');

        if ($password !== '') {
            $validator = Validator::make([
                'password'              => $password,
                'password_confirmation' => (string) ($request->raw('password_confirmation') ?? ''),
            ], ['password' => 'min:10|max:200|confirmed']);

            if ($validator->fails()) {
                $this->back($url, 'error', (string) $validator->first());
            }

            $data['password'] = $password;
        }

        AdminUser::update($id, $data);

        $this->back($url, 'success', 'Administrator updated.');
    }

    public function destroy(Request $request, array $params): never
    {
        $url = $this->adminUrl('users');
        $id  = (int) $params['id'];

        $this->requireCsrf($request, $url);
        $this->requireOwner($url);

        if ($id === Auth::id()) {
            $this->back($url, 'error', 'You cannot delete the account you are signed in with.');
        }

        $user = AdminUser::find($id);
        if ($user === null) {
            $this->back($url, 'error', 'That administrator no longer exists.');
        }

        if ($user['role'] === 'owner' && AdminUser::activeOwnerCount() <= 1) {
            $this->back($url, 'error', 'This is the only owner account and cannot be deleted.');
        }

        AdminUser::delete($id);

        $this->back($url, 'success', 'Administrator deleted.');
    }

    // --- Own profile --------------------------------------------------------

    public function profile(Request $request): never
    {
        $this->view('admin.users.profile', [
            'pageTitle' => 'My profile',
            'activeNav' => '',
            'user'      => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): never
    {
        $url = $this->adminUrl('profile');

        $this->requireCsrf($request, $url);

        $id   = (int) Auth::id();
        $user = AdminUser::find($id);

        if ($user === null) {
            $this->back($this->adminUrl(), 'error', 'Your account could not be loaded.');
        }

        $name  = (string) $request->input('name', $user['name']);
        $email = (string) $request->input('email', $user['email']);

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            ['name' => 'required|min:2|max:120', 'email' => 'required|email|max:190']
        );

        if ($validator->fails()) {
            $this->back($url, 'error', (string) $validator->first());
        }

        if (AdminUser::emailExists($email, $id)) {
            $this->back($url, 'error', 'Another administrator already uses that e-mail address.');
        }

        $data     = ['name' => $name, 'email' => $email];
        $password = (string) ($request->raw('password') ?? '');

        if ($password !== '') {
            // Changing your own password requires the current one.
            $current = (string) ($request->raw('current_password') ?? '');

            if (!password_verify($current, (string) $user['password_hash'])) {
                $this->back($url, 'error', 'Your current password is not correct.');
            }

            $validator = Validator::make([
                'password'              => $password,
                'password_confirmation' => (string) ($request->raw('password_confirmation') ?? ''),
            ], ['password' => 'min:10|max:200|confirmed']);

            if ($validator->fails()) {
                $this->back($url, 'error', (string) $validator->first());
            }

            $data['password'] = $password;
        }

        AdminUser::update($id, $data);

        $this->back($url, 'success', 'Profile updated.');
    }
}
