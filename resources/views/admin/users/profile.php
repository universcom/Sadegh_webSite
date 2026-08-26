<?php
/** @var array $user */

use App\Core\Csrf;
use App\Core\Url;
?>
<div class="panel">
    <div class="panel__head">
        <h2>My profile</h2>
        <p>Signed in as <strong><?= e($user['role']) ?></strong>.</p>
    </div>
    <form method="post" action="<?= e(Url::admin('profile')) ?>">
        <?= Csrf::field() ?>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="pname">Full name</label>
                    <input class="input" type="text" id="pname" name="name" required maxlength="120"
                           value="<?= e($user['name']) ?>">
                </div>
                <div class="field">
                    <label for="pemail">E-mail address</label>
                    <input class="input" type="email" id="pemail" name="email" required dir="ltr" maxlength="190"
                           value="<?= e($user['email']) ?>">
                </div>
            </div>

            <div class="divider"></div>

            <h3 style="font-size:.95rem" class="mb-3">Change password</h3>
            <p class="small muted mb-4">Leave these blank to keep your current password.</p>

            <div class="grid-3">
                <div class="field">
                    <label for="pcurrent">Current password</label>
                    <input class="input" type="password" id="pcurrent" name="current_password" autocomplete="current-password">
                </div>
                <div class="field">
                    <label for="pnew">New password <span class="hint">— min 10 characters</span></label>
                    <input class="input" type="password" id="pnew" name="password" minlength="10" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="pnew2">Repeat new password</label>
                    <input class="input" type="password" id="pnew2" name="password_confirmation" minlength="10" autocomplete="new-password">
                </div>
            </div>
        </div>
        <div class="panel__foot">
            <button class="btn primary" type="submit">Save profile</button>
        </div>
    </form>
</div>
