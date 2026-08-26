<?php
/**
 * Administration sign-in. Rendered without the admin layout.
 *
 * @var string $error
 * @var string $email
 */

use App\Core\Csrf;
use App\Core\Url;
use App\Models\Setting;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · <?= e(Setting::get('site_name', 'Rahyaft Sanat')) ?></title>
    <link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="login-page">
    <main class="login-card">
        <div class="login-card__brand">
            <img src="<?= e(asset('img/logo-mark.png')) ?>" alt="" width="58" height="58">
            <h1><?= e(Setting::get('site_name', 'Rahyaft Sanat')) ?></h1>
            <p>Administration panel</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert error" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= e(Url::admin('login')) ?>" data-once>
            <?= Csrf::field() ?>

            <div class="field">
                <label for="email">E-mail address</label>
                <input class="input" type="email" id="email" name="email" required
                       autocomplete="username" dir="ltr" autofocus value="<?= e($email) ?>">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input class="input" type="password" id="password" name="password" required
                       autocomplete="current-password" dir="ltr">
            </div>

            <button class="btn primary block" type="submit">Sign in</button>
        </form>

        <p class="small muted mt-4" style="text-align:center">
            <a href="<?= e(Url::home()) ?>">← Back to the website</a>
        </p>
    </main>
</body>
</html>
