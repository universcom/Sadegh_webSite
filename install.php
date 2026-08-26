<?php
declare(strict_types=1);

/**
 * Rahyaft Sanat — installation wizard.
 *
 * Standalone by design: it runs before .env exists and therefore does not boot
 * the application. It writes .env, creates the schema, seeds the content
 * extracted from the raw materials, creates the first administrator, and then
 * locks itself with installed.lock.
 *
 * To re-run it deliberately, delete installed.lock over SFTP/cPanel.
 */

// ---------------------------------------------------------------------------
// Guard rails
// ---------------------------------------------------------------------------

$basePath = __DIR__;
$lockFile = $basePath . '/installed.lock';

if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    exit('<!doctype html><meta charset="utf-8"><h1>PHP 8.1 or newer is required</h1><p>This server runs PHP '
        . htmlspecialchars(PHP_VERSION, ENT_QUOTES) . '.</p>');
}

session_name('rs_install');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443,
]);
session_start();

$installed = is_file($lockFile);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['_install_csrf'])) {
        $_SESSION['_install_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_install_csrf'];
}

function csrfValid(): bool
{
    return isset($_POST['csrf'], $_SESSION['_install_csrf'])
        && hash_equals((string) $_SESSION['_install_csrf'], (string) $_POST['csrf']);
}

function post(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;

    return is_string($value) ? trim($value) : $default;
}

/** Values remembered between steps (never includes the DB password after use). */
function remembered(string $key, string $default = ''): string
{
    return (string) ($_SESSION['_install'][$key] ?? $default);
}

function remember(string $key, string $value): void
{
    $_SESSION['_install'][$key] = $value;
}

function guessAppUrl(): string
{
    $secure = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';

    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $host)) {
        $host = 'localhost';
    }

    $dir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
    $dir = ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');

    return ($secure ? 'https://' : 'http://') . $host . $dir;
}

/**
 * Requirement checks. `fatal` entries block installation; the rest are advisory.
 */
function requirements(string $basePath): array
{
    $checks = [];

    $checks[] = [
        'label' => 'PHP 8.1 or newer',
        'value' => PHP_VERSION,
        'ok'    => PHP_VERSION_ID >= 80100,
        'fatal' => true,
    ];

    foreach (['pdo_mysql', 'mbstring', 'json', 'fileinfo'] as $extension) {
        $checks[] = [
            'label' => 'Extension: ' . $extension,
            'value' => extension_loaded($extension) ? 'loaded' : 'missing',
            'ok'    => extension_loaded($extension),
            'fatal' => true,
        ];
    }

    foreach (['gd', 'openssl', 'zip'] as $extension) {
        $checks[] = [
            'label' => 'Extension: ' . $extension,
            'value' => extension_loaded($extension) ? 'loaded' : 'missing',
            'ok'    => extension_loaded($extension),
            'fatal' => false,
            'note'  => $extension === 'gd'
                ? 'Needed to generate responsive image sizes on upload.'
                : 'Recommended.',
        ];
    }

    foreach ([
        ''                => 'Project root (to write .env)',
        '/storage/logs'   => 'storage/logs',
        '/storage/cache'  => 'storage/cache',
        '/uploads/media'  => 'uploads/media',
        '/uploads/files'  => 'uploads/files',
    ] as $relative => $label) {
        $path = $basePath . $relative;

        if (!is_dir($path)) {
            @mkdir($path, 0o755, true);
        }

        $checks[] = [
            'label' => 'Writable: ' . $label,
            'value' => is_writable($path) ? 'writable' : 'not writable',
            'ok'    => is_writable($path),
            'fatal' => true,
            'note'  => is_writable($path) ? null : 'Set this directory to 755 and make sure PHP owns it.',
        ];
    }

    $rewrite = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules(), true) : null;
    $checks[] = [
        'label' => 'Apache mod_rewrite',
        'value' => $rewrite === null ? 'cannot detect' : ($rewrite ? 'enabled' : 'disabled'),
        'ok'    => $rewrite !== false,
        'fatal' => false,
        'note'  => 'Without it the site still works, but URLs take the form /index.php/fa/products.',
    ];

    return $checks;
}

function requirementsPass(array $checks): bool
{
    foreach ($checks as $check) {
        if ($check['fatal'] && !$check['ok']) {
            return false;
        }
    }

    return true;
}

function connect(array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        (int) $config['port'],
        $config['name']
    );

    return new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

/** Split the schema file on semicolons that terminate a statement. */
function runSchema(PDO $pdo, string $file): int
{
    $sql = (string) file_get_contents($file);
    $sql = (string) preg_replace('/^\s*--.*$/m', '', $sql);

    $statements = array_filter(
        array_map('trim', preg_split('/;\s*[\r\n]/', $sql) ?: []),
        static fn (string $s): bool => $s !== '' && $s !== ';'
    );

    $count = 0;
    foreach ($statements as $statement) {
        $pdo->exec(rtrim($statement, "; \t\n\r"));
        $count++;
    }

    return $count;
}

function writeEnv(string $basePath, array $values): bool
{
    $template = $basePath . '/.env.example';
    $lines    = is_readable($template)
        ? (file($template, FILE_IGNORE_NEW_LINES) ?: [])
        : [];

    if ($lines === []) {
        foreach ($values as $key => $value) {
            $lines[] = $key . '=';
        }
    }

    $written = [];

    foreach ($lines as $index => $line) {
        if (!preg_match('/^([A-Z0-9_]+)=/', $line, $m)) {
            continue;
        }

        $key = $m[1];

        if (array_key_exists($key, $values)) {
            $lines[$index] = $key . '=' . quoteEnv((string) $values[$key]);
            $written[$key] = true;
        }
    }

    foreach ($values as $key => $value) {
        if (!isset($written[$key])) {
            $lines[] = $key . '=' . quoteEnv((string) $value);
        }
    }

    $result = @file_put_contents($basePath . '/.env', implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);

    if ($result !== false) {
        // Keep the credentials file off-limits to other accounts on the host.
        @chmod($basePath . '/.env', 0o600);
    }

    return $result !== false;
}

function quoteEnv(string $value): string
{
    return preg_match('/[\s#"\']/', $value) ? '"' . str_replace('"', '\"', $value) . '"' : $value;
}

// ---------------------------------------------------------------------------
// Flow
// ---------------------------------------------------------------------------

$steps = [
    1 => 'Requirements',
    2 => 'Database',
    3 => 'Website',
    4 => 'Administrator',
    5 => 'Finish',
];

$step   = max(1, min(5, (int) ($_GET['step'] ?? 1)));
$errors = [];
$notice = null;

if (!$installed && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $action = post('action');

        // --- Step 2: database ------------------------------------------------
        if ($action === 'database') {
            $config = [
                'host'     => post('db_host', 'localhost'),
                'port'     => post('db_port', '3306'),
                'name'     => post('db_name'),
                'user'     => post('db_user'),
                'password' => (string) ($_POST['db_password'] ?? ''),
            ];

            if ($config['name'] === '' || $config['user'] === '') {
                $errors[] = 'Database name and user are required.';
            } else {
                try {
                    $pdo = connect($config);

                    // Create the schema straight away so a failure surfaces here
                    // rather than after the operator has entered more details.
                    $created = runSchema($pdo, $basePath . '/database/schema.sql');

                    remember('db_host', $config['host']);
                    remember('db_port', $config['port']);
                    remember('db_name', $config['name']);
                    remember('db_user', $config['user']);
                    // Held only for the duration of the wizard, never echoed back.
                    remember('db_password', $config['password']);

                    $_SESSION['_install']['tables'] = $created;

                    header('Location: install.php?step=3');
                    exit;
                } catch (Throwable $e) {
                    $errors[] = 'Could not connect or create tables: ' . $e->getMessage();
                }
            }
        }

        // --- Step 3: website ---------------------------------------------------
        if ($action === 'website') {
            $url = rtrim(post('app_url'), '/');

            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = 'Please enter a valid website address, including https://';
            } else {
                remember('app_url', $url);
                remember('site_name', post('site_name', 'Rahyaft Sanat'));
                remember('default_locale', in_array(post('default_locale'), ['fa', 'en', 'ar'], true) ? post('default_locale') : 'fa');
                remember('import', isset($_POST['import']) ? '1' : '0');

                header('Location: install.php?step=4');
                exit;
            }
        }

        // --- Step 4: administrator + commit ------------------------------------
        if ($action === 'admin') {
            $name     = post('admin_name');
            $email    = post('admin_email');
            $password = (string) ($_POST['admin_password'] ?? '');
            $confirm  = (string) ($_POST['admin_password_confirmation'] ?? '');

            if ($name === '') {
                $errors[] = 'Please enter the administrator name.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid administrator e-mail address.';
            }
            if (strlen($password) < 10) {
                $errors[] = 'The password must be at least 10 characters long.';
            }
            if ($password !== $confirm) {
                $errors[] = 'The two passwords do not match.';
            }

            if ($errors === []) {
                try {
                    $config = [
                        'host'     => remembered('db_host', 'localhost'),
                        'port'     => remembered('db_port', '3306'),
                        'name'     => remembered('db_name'),
                        'user'     => remembered('db_user'),
                        'password' => remembered('db_password'),
                    ];

                    $pdo = connect($config);
                    runSchema($pdo, $basePath . '/database/schema.sql');

                    // 1. Write configuration.
                    $ok = writeEnv($basePath, [
                        'APP_ENV'            => 'production',
                        'APP_DEBUG'          => 'false',
                        'APP_URL'            => remembered('app_url', guessAppUrl()),
                        'APP_KEY'            => bin2hex(random_bytes(24)),
                        'APP_DEFAULT_LOCALE' => remembered('default_locale', 'fa'),
                        'APP_LOCALES'        => 'fa,en,ar',
                        'DB_HOST'            => $config['host'],
                        'DB_PORT'            => $config['port'],
                        'DB_NAME'            => $config['name'],
                        'DB_USER'            => $config['user'],
                        'DB_PASSWORD'        => $config['password'],
                        'DB_CHARSET'         => 'utf8mb4',
                        'MAIL_MAILER'        => 'mail',
                        'MAIL_FROM_ADDRESS'  => 'no-reply@' . (parse_url(remembered('app_url', ''), PHP_URL_HOST) ?: 'localhost'),
                        'MAIL_FROM_NAME'     => remembered('site_name', 'Rahyaft Sanat'),
                        'MAIL_NOTIFY_TO'     => $email,
                    ]);

                    if (!$ok) {
                        throw new RuntimeException('Could not write the .env file. Check that the project root is writable.');
                    }

                    // 2. Create the first administrator (owner role).
                    $existing = $pdo->prepare('SELECT id FROM admin_users WHERE email = ? LIMIT 1');
                    $existing->execute([$email]);

                    if ($existing->fetchColumn() === false) {
                        $insert = $pdo->prepare(
                            'INSERT INTO admin_users (name, email, password_hash, role, is_active)
                             VALUES (?, ?, ?, \'owner\', 1)'
                        );
                        $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                    }

                    // 3. Import the extracted content.
                    $report = [];
                    if (remembered('import', '1') === '1') {
                        require_once $basePath . '/app/Core/Autoloader.php';
                        App\Core\Autoloader::register($basePath);
                        require_once $basePath . '/app/Support/helpers.php';

                        App\Core\Env::load($basePath . '/.env');
                        App\Core\Config::loadDirectory($basePath . '/config');
                        App\Core\Config::set('app.base_path', $basePath);
                        App\Core\Lang::set(remembered('default_locale', 'fa'));

                        $report = (new Database\Seeder(App\Core\Database::instance(), $basePath))->run();
                    }

                    // 4. Store the operator's chosen site name over the seeded one.
                    $siteName = remembered('site_name', '');
                    if ($siteName !== '') {
                        $locale = remembered('default_locale', 'fa');
                        $upsert = $pdo->prepare(
                            'INSERT INTO settings (skey, lang, svalue, group_name) VALUES (\'site_name\', ?, ?, \'general\')
                             ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)'
                        );
                        $upsert->execute([$locale, $siteName]);
                    }

                    // 5. Lock the installer.
                    @file_put_contents($lockFile, sprintf(
                        "Installed %s\nDelete this file only if you intend to run install.php again.\n",
                        gmdate('c')
                    ), LOCK_EX);
                    @chmod($lockFile, 0o600);

                    $_SESSION['_install_report'] = $report;
                    $_SESSION['_install_email']  = $email;

                    // Clear the credentials held in the session.
                    unset($_SESSION['_install']);

                    header('Location: install.php?step=5');
                    exit;
                } catch (Throwable $e) {
                    $errors[] = 'Installation failed: ' . $e->getMessage();
                }
            }
        }
    }
}

// A completed install landing on step 5 is fine; anything else is refused.
if ($installed && $step !== 5) {
    $step = 0;
}

$checks = $step === 1 ? requirements($basePath) : [];
$report = $_SESSION['_install_report'] ?? [];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install · Rahyaft Sanat</title>
<link rel="icon" href="assets/img/favicon-32.png" sizes="32x32">
<style>
    :root{
        --brand:#353a96;--brand-dark:#232764;--brand-deep:#1a1c4a;--brand-50:#f2f4fd;--brand-100:#e3e7f9;
        --accent:#c0202c;--ok:#15803d;--ok-bg:#ecfdf3;--warn:#b45309;--warn-bg:#fffaeb;
        --ink:#1d2130;--muted:#6b7385;--border:#e0e3ea;--bg:#f7f8fb;--radius:12px;
    }
    *,*::before,*::after{box-sizing:border-box}
    body{margin:0;font:16px/1.7 'Segoe UI',system-ui,-apple-system,Tahoma,sans-serif;color:var(--ink);background:var(--bg)}
    .shell{max-width:820px;margin:0 auto;padding:40px 20px 80px}
    header{text-align:center;margin-bottom:32px}
    .logo{width:64px;height:64px;border-radius:10px}
    h1{margin:16px 0 4px;font-size:1.65rem;color:var(--brand-dark)}
    header p{margin:0;color:var(--muted)}
    .steps{display:flex;gap:6px;margin:28px 0;list-style:none;padding:0}
    .steps li{flex:1;text-align:center;font-size:.72rem;font-weight:600;color:var(--muted);
        padding-top:24px;position:relative;letter-spacing:.02em}
    .steps li::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);
        width:16px;height:16px;border-radius:50%;background:#fff;border:2px solid var(--border)}
    .steps li::after{content:'';position:absolute;top:7px;left:calc(-50% + 8px);width:calc(100% - 16px);height:2px;background:var(--border)}
    .steps li:first-child::after{display:none}
    .steps li.done{color:var(--brand)}
    .steps li.done::before{background:var(--brand);border-color:var(--brand)}
    .steps li.done::after{background:var(--brand)}
    .steps li.current::before{border-color:var(--brand);box-shadow:0 0 0 4px var(--brand-100)}
    .steps li.current{color:var(--brand-dark)}
    .card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:32px;
        box-shadow:0 8px 20px -12px rgba(26,28,74,.2)}
    @media(max-width:560px){.card{padding:22px}.shell{padding:24px 14px 60px}}
    h2{margin:0 0 6px;font-size:1.2rem;color:var(--brand-dark)}
    .lede{margin:0 0 24px;color:var(--muted);font-size:.94rem}
    table{width:100%;border-collapse:collapse;font-size:.9rem}
    td{padding:9px 4px;border-bottom:1px solid #f0f1f5;vertical-align:top}
    td:last-child{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
    .pill{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.76rem;font-weight:700}
    .pill.ok{background:var(--ok-bg);color:var(--ok)}
    .pill.no{background:#fdf3f4;color:var(--accent)}
    .pill.warn{background:var(--warn-bg);color:var(--warn)}
    .note{display:block;color:var(--muted);font-size:.8rem;margin-top:3px;font-weight:400}
    label{display:block;font-weight:600;font-size:.9rem;margin-bottom:6px}
    .hint{font-weight:400;color:var(--muted);font-size:.82rem}
    input[type=text],input[type=password],input[type=email],input[type=url],input[type=number],select{
        width:100%;padding:11px 13px;border:1.5px solid var(--border);border-radius:9px;font:inherit;font-size:.94rem;background:#fff}
    input:focus,select:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 4px var(--brand-100)}
    .field{margin-bottom:18px}
    .row{display:flex;gap:14px;flex-wrap:wrap}
    .row>.field{flex:1 1 200px}
    .check{display:flex;gap:10px;align-items:flex-start;background:var(--brand-50);
        border:1px solid var(--brand-100);border-radius:9px;padding:14px}
    .check input{margin-top:4px}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 26px;border:0;border-radius:99px;
        background:var(--brand);color:#fff;font:inherit;font-weight:600;cursor:pointer;text-decoration:none}
    .btn:hover{background:var(--brand-dark)}
    .btn.ghost{background:#fff;color:var(--brand);border:1.5px solid var(--border)}
    .btn.ghost:hover{border-color:var(--brand);background:var(--brand-50)}
    .btn[disabled]{opacity:.5;cursor:not-allowed}
    .actions{display:flex;gap:10px;align-items:center;margin-top:26px;flex-wrap:wrap}
    .alert{padding:13px 16px;border-radius:9px;margin-bottom:18px;font-size:.9rem;border:1px solid}
    .alert.err{background:#fdf3f4;border-color:#f3bfc4;color:#7f1420}
    .alert.ok{background:var(--ok-bg);border-color:#a6e9c2;color:#14532d}
    .alert.warn{background:var(--warn-bg);border-color:#fbe08a;color:#7a4c07}
    .alert ul{margin:6px 0 0;padding-left:18px}
    code{background:#f0f1f5;padding:2px 6px;border-radius:5px;font-size:.86em}
    .summary{list-style:none;padding:0;margin:18px 0 0;display:grid;gap:6px;font-size:.9rem}
    .summary li{display:flex;justify-content:space-between;padding:8px 12px;background:var(--brand-50);border-radius:8px}
    .summary b{font-variant-numeric:tabular-nums}
    footer{text-align:center;color:var(--muted);font-size:.8rem;margin-top:26px}
</style>
</head>
<body>
<div class="shell">
    <header>
        <img class="logo" src="assets/img/logo-mark.png" alt="">
        <h1>Rahyaft Sanat</h1>
        <p>Installation wizard</p>
    </header>

    <?php if ($step > 0): ?>
        <ol class="steps">
            <?php foreach ($steps as $number => $label): ?>
                <li class="<?= $number < $step ? 'done' : ($number === $step ? 'current done' : '') ?>"><?= h($label) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <div class="card">
        <?php if ($errors !== []): ?>
            <div class="alert err">
                <strong>Please fix the following:</strong>
                <ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if ($step === 0): ?>
            <h2>Already installed</h2>
            <p class="lede">This site has been installed, so the wizard is locked.</p>
            <div class="alert warn">
                To run the installer again — which will rewrite <code>.env</code> — delete
                <code>installed.lock</code> from the project root over SFTP or the cPanel file manager.
                Leaving the lock in place is the secure default.
            </div>
            <div class="actions">
                <a class="btn" href="./">Go to the website</a>
                <a class="btn ghost" href="admin">Administration</a>
            </div>

        <?php elseif ($step === 1): ?>
            <h2>Server requirements</h2>
            <p class="lede">Everything marked required must pass before the installation can continue.</p>
            <table>
                <?php foreach ($checks as $check): ?>
                    <tr>
                        <td>
                            <?= h($check['label']) ?>
                            <?php if (!empty($check['note']) && !$check['ok']): ?>
                                <span class="note"><?= h($check['note']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($check['ok']): ?>
                                <span class="pill ok"><?= h($check['value']) ?></span>
                            <?php elseif ($check['fatal']): ?>
                                <span class="pill no"><?= h($check['value']) ?></span>
                            <?php else: ?>
                                <span class="pill warn"><?= h($check['value']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="actions">
                <?php if (requirementsPass($checks)): ?>
                    <a class="btn" href="install.php?step=2">Continue</a>
                <?php else: ?>
                    <button class="btn" disabled>Continue</button>
                    <a class="btn ghost" href="install.php?step=1">Re-check</a>
                <?php endif; ?>
            </div>

        <?php elseif ($step === 2): ?>
            <h2>Database connection</h2>
            <p class="lede">Create an empty MySQL/MariaDB database and a user with full privileges on it, then enter the details here. The tables are created immediately.</p>
            <form method="post" action="install.php?step=2">
                <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="database">
                <div class="row">
                    <div class="field" style="flex:2 1 260px">
                        <label for="db_host">Database host</label>
                        <input type="text" id="db_host" name="db_host" required
                               value="<?= h(post('db_host', remembered('db_host', 'localhost'))) ?>">
                    </div>
                    <div class="field" style="flex:0 1 120px">
                        <label for="db_port">Port</label>
                        <input type="number" id="db_port" name="db_port" required
                               value="<?= h(post('db_port', remembered('db_port', '3306'))) ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="db_name">Database name</label>
                    <input type="text" id="db_name" name="db_name" required
                           value="<?= h(post('db_name', remembered('db_name'))) ?>">
                </div>
                <div class="row">
                    <div class="field">
                        <label for="db_user">Database user</label>
                        <input type="text" id="db_user" name="db_user" required autocomplete="off"
                               value="<?= h(post('db_user', remembered('db_user'))) ?>">
                    </div>
                    <div class="field">
                        <label for="db_password">Database password</label>
                        <input type="password" id="db_password" name="db_password" autocomplete="new-password">
                    </div>
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Test connection &amp; create tables</button>
                    <a class="btn ghost" href="install.php?step=1">Back</a>
                </div>
            </form>

        <?php elseif ($step === 3): ?>
            <h2>Website details</h2>
            <p class="lede">
                <?php if (!empty($_SESSION['_install']['tables'])): ?>
                    Database ready — <?= (int) $_SESSION['_install']['tables'] ?> statements executed.
                <?php endif; ?>
            </p>
            <form method="post" action="install.php?step=3">
                <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="website">
                <div class="field">
                    <label for="site_name">Company name</label>
                    <input type="text" id="site_name" name="site_name" required
                           value="<?= h(post('site_name', remembered('site_name', 'شرکت رهیافت صنعت'))) ?>">
                </div>
                <div class="field">
                    <label for="app_url">Website address <span class="hint">— no trailing slash</span></label>
                    <input type="url" id="app_url" name="app_url" required
                           value="<?= h(post('app_url', remembered('app_url', guessAppUrl()))) ?>">
                </div>
                <div class="field">
                    <label for="default_locale">Default language</label>
                    <select id="default_locale" name="default_locale">
                        <option value="fa" selected>فارسی (Persian) — recommended</option>
                        <option value="en">English</option>
                        <option value="ar">العربية (Arabic)</option>
                    </select>
                </div>
                <div class="field">
                    <label class="check">
                        <input type="checkbox" name="import" value="1" checked>
                        <span>
                            <strong>Import the prepared content</strong>
                            <span class="note">Loads the 17 products, 3 categories, 5 research projects, pages and
                            company details extracted from the supplied source materials, in all three languages.
                            Leave this ticked unless you want to start from an empty site.</span>
                        </span>
                    </label>
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Continue</button>
                    <a class="btn ghost" href="install.php?step=2">Back</a>
                </div>
            </form>

        <?php elseif ($step === 4): ?>
            <h2>Administrator account</h2>
            <p class="lede">This account gets the <strong>owner</strong> role and can create further administrators later.</p>
            <form method="post" action="install.php?step=4">
                <input type="hidden" name="csrf" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="admin">
                <div class="field">
                    <label for="admin_name">Full name</label>
                    <input type="text" id="admin_name" name="admin_name" required value="<?= h(post('admin_name')) ?>">
                </div>
                <div class="field">
                    <label for="admin_email">E-mail address <span class="hint">— also used for contact-form notifications</span></label>
                    <input type="email" id="admin_email" name="admin_email" required dir="ltr" value="<?= h(post('admin_email')) ?>">
                </div>
                <div class="row">
                    <div class="field">
                        <label for="admin_password">Password <span class="hint">— at least 10 characters</span></label>
                        <input type="password" id="admin_password" name="admin_password" required minlength="10" autocomplete="new-password">
                    </div>
                    <div class="field">
                        <label for="admin_password_confirmation">Repeat password</label>
                        <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" required minlength="10" autocomplete="new-password">
                    </div>
                </div>
                <div class="actions">
                    <button class="btn" type="submit">Install</button>
                    <a class="btn ghost" href="install.php?step=3">Back</a>
                </div>
            </form>

        <?php else: ?>
            <h2>Installation complete</h2>
            <div class="alert ok">
                The site is installed and the wizard has locked itself with <code>installed.lock</code>.
            </div>

            <?php if ($report !== []): ?>
                <p class="lede" style="margin-bottom:0">Imported from the supplied source materials:</p>
                <ul class="summary">
                    <?php foreach ($report as $key => $count): ?>
                        <li><span><?= h(ucfirst(str_replace('_', ' ', (string) $key))) ?></span><b><?= (int) $count ?></b></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="alert warn" style="margin-top:22px">
                <strong>Before you go live:</strong>
                <ul>
                    <li>Delete <code>install.php</code> from the server (the lock file already blocks it).</li>
                    <li>Confirm <code>.env</code> is not reachable over the web.</li>
                    <li>Enable HTTPS, then uncomment the redirect block in <code>.htaccess</code>.</li>
                    <li>Set your SMTP details in <strong>Settings</strong> or in <code>.env</code>.</li>
                </ul>
            </div>

            <div class="actions">
                <a class="btn" href="admin">Sign in to the administration panel</a>
                <a class="btn ghost" href="./">View the website</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>Rahyaft Sanat · installation wizard</footer>
</div>
</body>
</html>
