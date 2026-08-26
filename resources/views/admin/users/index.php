<?php
/** @var array $users */

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Url;

$roleBadge = static fn (string $role): string => match ($role) {
    'owner'  => 'blue',
    'admin'  => 'green',
    default  => 'gray',
};
?>
<div class="panel">
    <div class="panel__head">
        <h2>Administrator accounts</h2>
        <p><strong>Owner</strong> can manage accounts and settings · <strong>Admin</strong> can manage
           and delete content · <strong>Editor</strong> can create and edit content but not delete it.</p>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Name</th><th>E-mail</th><th>Role</th><th>Last sign-in</th><th>Status</th><th class="right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <span class="title"><?= e($user['name']) ?></span>
                            <?php if ((int) $user['id'] === Auth::id()): ?>
                                <span class="badge blue">You</span>
                            <?php endif; ?>
                        </td>
                        <td class="sub ltr"><?= e($user['email']) ?></td>
                        <td><span class="badge <?= $roleBadge((string) $user['role']) ?>"><?= e(ucfirst((string) $user['role'])) ?></span></td>
                        <td class="num sub nowrap">
                            <?= $user['last_login_at'] ? e(date('Y-m-d H:i', strtotime((string) $user['last_login_at']))) : '—' ?>
                        </td>
                        <td><span class="badge <?= $user['is_active'] ? 'green' : 'gray' ?>"><?= $user['is_active'] ? 'Active' : 'Disabled' ?></span></td>
                        <td class="actions">
                            <details>
                                <summary class="btn ghost sm" style="list-style:none">Edit</summary>
                                <form method="post" action="<?= e(Url::admin('users/' . $user['id'])) ?>"
                                      style="margin-top:10px;text-align:left;min-width:260px">
                                    <?= Csrf::field() ?>
                                    <div class="field">
                                        <label>Name</label>
                                        <input class="input" type="text" name="name" value="<?= e($user['name']) ?>" required>
                                    </div>
                                    <div class="field">
                                        <label>Role</label>
                                        <select class="select" name="role">
                                            <?php foreach (['owner', 'admin', 'editor'] as $role): ?>
                                                <option value="<?= e($role) ?>" <?= $user['role'] === $role ? 'selected' : '' ?>>
                                                    <?= e(ucfirst($role)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>New password <span class="hint">— leave blank to keep</span></label>
                                        <input class="input" type="password" name="password" minlength="10" autocomplete="new-password">
                                    </div>
                                    <div class="field">
                                        <label>Repeat new password</label>
                                        <input class="input" type="password" name="password_confirmation" minlength="10" autocomplete="new-password">
                                    </div>
                                    <label class="checkbox mb-3">
                                        <input type="checkbox" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>>
                                        <span>Account active</span>
                                    </label>
                                    <div class="btn-row">
                                        <button class="btn primary sm" type="submit">Save</button>
                                        <?php if ((int) $user['id'] !== Auth::id()): ?>
                                            <button class="btn danger sm" type="submit"
                                                    formaction="<?= e(Url::admin('users/' . $user['id'] . '/delete')) ?>"
                                                    formnovalidate
                                                    onclick="return confirm('Delete this administrator account?')">Delete</button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel__head"><h2>Add an administrator</h2></div>
    <form method="post" action="<?= e(Url::admin('users')) ?>" data-once>
        <?= Csrf::field() ?>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="uname">Full name</label>
                    <input class="input" type="text" id="uname" name="name" required maxlength="120">
                </div>
                <div class="field">
                    <label for="uemail">E-mail address</label>
                    <input class="input" type="email" id="uemail" name="email" required dir="ltr" maxlength="190">
                </div>
            </div>
            <div class="grid-3">
                <div class="field">
                    <label for="upassword">Password <span class="hint">— min 10 characters</span></label>
                    <input class="input" type="password" id="upassword" name="password" required minlength="10" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="upassword2">Repeat password</label>
                    <input class="input" type="password" id="upassword2" name="password_confirmation" required minlength="10" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="urole">Role</label>
                    <select class="select" id="urole" name="role">
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit">Create administrator</button>
        </div>
    </form>
</div>
