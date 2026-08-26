#!/usr/bin/env bash
#
# Rahyaft Sanat — local development helper.
#
# Development only. It is never needed in production, where Apache serves the
# site through index.php and .htaccess. You may delete it before deploying.
#
# Usage:
#   ./dev.sh              start the site (same as `start`)
#   ./dev.sh start        start the database and PHP dev server
#   ./dev.sh stop         stop the dev server
#   ./dev.sh restart      stop, then start
#   ./dev.sh status       show what is running and what is configured
#   ./dev.sh install      wipe config and open the installation wizard
#   ./dev.sh fresh        rebuild the database and reimport content (no wizard)
#   ./dev.sh seed         re-import the content from database/content/
#   ./dev.sh logs         follow the application log
#   ./dev.sh check        run environment and route checks
#
# Options:
#   --port N              serve on a different port (default 8000)
#   --no-open             do not open a browser

set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")"

ROOT="$(pwd)"
PORT="${PORT:-8000}"
HOST="127.0.0.1"
LOG_FILE="$ROOT/storage/logs/dev-server.log"
OPEN_BROWSER=1
PID_FILE=""   # set after argument parsing, once PORT is known

# --- Presentation -----------------------------------------------------------

if [ -t 1 ]; then
    BOLD=$'\033[1m'; DIM=$'\033[2m'; RED=$'\033[31m'; GREEN=$'\033[32m'
    YELLOW=$'\033[33m'; BLUE=$'\033[34m'; RESET=$'\033[0m'
else
    BOLD=''; DIM=''; RED=''; GREEN=''; YELLOW=''; BLUE=''; RESET=''
fi

say()  { printf '%s\n' "$*"; }
ok()   { printf '  %s✓%s %s\n' "$GREEN" "$RESET" "$*"; }
warn() { printf '  %s!%s %s\n' "$YELLOW" "$RESET" "$*"; }
bad()  { printf '  %s✗%s %s\n' "$RED" "$RESET" "$*"; }
head_() { printf '\n%s%s%s\n' "$BOLD" "$*" "$RESET"; }
die()  { printf '\n%sError:%s %s\n\n' "$RED" "$RESET" "$*" >&2; exit 1; }

# --- Argument parsing -------------------------------------------------------

COMMAND=""
while [ $# -gt 0 ]; do
    case "$1" in
        --port)     PORT="${2:-}"; shift 2 ;;
        --port=*)   PORT="${1#*=}"; shift ;;
        --no-open)  OPEN_BROWSER=0; shift ;;
        -h|--help)  sed -n '2,25p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
        -*)         die "Unknown option: $1" ;;
        *)          [ -z "$COMMAND" ] && COMMAND="$1"; shift ;;
    esac
done
COMMAND="${COMMAND:-start}"

case "$PORT" in
    ''|*[!0-9]*) die "Port must be a number (got '$PORT')." ;;
esac

# One pid file per port, so two ports can run side by side.
PID_FILE="$ROOT/storage/dev-server-$PORT.pid"
BASE_URL="http://localhost:$PORT"

# --- Helpers ----------------------------------------------------------------

# Read a key from .env without sourcing it (values may contain spaces/quotes).
env_get() {
    local key="$1" line value
    [ -f "$ROOT/.env" ] || return 0
    line="$(grep -E "^${key}=" "$ROOT/.env" | tail -1 || true)"
    [ -z "$line" ] && return 0
    value="${line#*=}"
    value="${value%\"}"; value="${value#\"}"
    value="${value%\'}"; value="${value#\'}"
    printf '%s' "$value"
}

server_pid() {
    [ -f "$PID_FILE" ] || return 1
    local pid; pid="$(cat "$PID_FILE" 2>/dev/null || true)"
    [ -n "$pid" ] && kill -0 "$pid" 2>/dev/null && printf '%s' "$pid" && return 0
    rm -f "$PID_FILE"
    return 1
}

port_in_use() { lsof -nP -iTCP:"$PORT" -sTCP:LISTEN >/dev/null 2>&1; }

open_url() {
    [ "$OPEN_BROWSER" -eq 1 ] || return 0
    if command -v open >/dev/null 2>&1;      then open "$1" >/dev/null 2>&1 || true
    elif command -v xdg-open >/dev/null 2>&1; then xdg-open "$1" >/dev/null 2>&1 || true
    fi
}

# mysql client, and the arguments needed to reach the server as an admin.
mysql_bin() {
    for c in mysql mariadb; do command -v "$c" >/dev/null 2>&1 && { printf '%s' "$c"; return 0; }; done
    return 1
}

# Try, in order: the current OS user, root without a password, root via sudo.
mysql_admin() {
    local bin; bin="$(mysql_bin)" || return 1
    if "$bin" -u "$(whoami)" -e 'SELECT 1' >/dev/null 2>&1; then
        printf '%s -u %s' "$bin" "$(whoami)"; return 0
    fi
    if "$bin" -u root -e 'SELECT 1' >/dev/null 2>&1; then
        printf '%s -u root' "$bin"; return 0
    fi
    return 1
}

require_php() {
    command -v php >/dev/null 2>&1 || die "PHP is not installed. On macOS: brew install php"
    local version; version="$(php -r 'echo PHP_VERSION;')"
    php -r 'exit(PHP_VERSION_ID >= 80100 ? 0 : 1);' \
        || die "PHP 8.1 or newer is required (found $version)."
}

# Cached module list. Never pipe `php -m` straight into `grep -q`: grep exits on
# the first match, php dies of SIGPIPE, and `set -o pipefail` reports the whole
# pipeline as failed even though the extension is loaded.
PHP_MODULES=""
php_has_ext() {
    [ -n "$PHP_MODULES" ] || PHP_MODULES="$(php -m)"
    printf '%s\n' "$PHP_MODULES" | grep -qx "$1"
}

check_extensions() {
    local missing=()
    for ext in pdo_mysql mbstring json fileinfo; do
        php_has_ext "$ext" || missing+=("$ext")
    done
    [ ${#missing[@]} -eq 0 ] || die "Missing required PHP extensions: ${missing[*]}"
    php_has_ext gd || warn "The gd extension is missing — uploaded images will not get responsive sizes."
}

start_database() {
    local bin; bin="$(mysql_bin)" || {
        warn "No MySQL/MariaDB client found. On macOS: brew install mariadb"
        return 1
    }

    if "$bin" -u "$(whoami)" -e 'SELECT 1' >/dev/null 2>&1 \
       || "$bin" -u root -e 'SELECT 1' >/dev/null 2>&1; then
        ok "Database server is running"
        return 0
    fi

    local brew_services=""
    command -v brew >/dev/null 2>&1 && brew_services="$(brew services list 2>/dev/null || true)"
    if printf '%s\n' "$brew_services" | grep -q '^mariadb'; then
        warn "Database is not responding — starting MariaDB…"
        brew services start mariadb >/dev/null 2>&1 || true
        for _ in 1 2 3 4 5 6 7 8 9 10; do
            sleep 1
            "$bin" -u "$(whoami)" -e 'SELECT 1' >/dev/null 2>&1 && { ok "Database server started"; return 0; }
        done
    fi

    warn "Could not reach the database server. Start it yourself, then re-run."
    return 1
}

ensure_directories() {
    mkdir -p storage/logs storage/cache uploads/media uploads/files
    for d in storage/logs storage/cache uploads/media uploads/files; do
        [ -w "$d" ] || die "Directory not writable: $d"
    done
}

installed() { [ -f "$ROOT/.env" ] && [ -f "$ROOT/installed.lock" ]; }

# --- Commands ---------------------------------------------------------------

cmd_start() {
    head_ "Rahyaft Sanat — local development"

    require_php
    ok "PHP $(php -r 'echo PHP_VERSION;')"
    check_extensions
    ensure_directories
    ok "Writable directories ready"

    start_database || true

    if server_pid >/dev/null 2>&1; then
        ok "Dev server already running (pid $(server_pid))"
    elif port_in_use; then
        die "Port $PORT is already in use by another process. Try: ./dev.sh start --port 8001"
    else
        # Detached so this script can report status and exit; logs go to a file.
        php -S "$HOST:$PORT" server.php >"$LOG_FILE" 2>&1 &
        echo $! > "$PID_FILE"
        sleep 1
        if ! server_pid >/dev/null 2>&1; then
            bad "Server failed to start on port $PORT"
            sed 's/^/    /' <(tail -3 "$LOG_FILE")
            say ""
            say "  Try another port:  ${BOLD}./dev.sh start --port $((PORT + 1))${RESET}"
            say ""
            exit 1
        fi
        ok "Dev server started (pid $(server_pid)) on port $PORT"
    fi

    if ! installed; then
        head_ "Not installed yet"
        say "  Opening the installation wizard. Use these database details:"
        say ""
        say "    ${DIM}host${RESET}      127.0.0.1        ${DIM}port${RESET}  3306"
        say "    ${DIM}database${RESET}  a new empty database you have created"
        say "    ${DIM}user${RESET}      a MySQL user with full rights on it"
        say ""
        say "  Tip: ${BOLD}./dev.sh fresh${RESET} does all of that for you and skips the wizard."
        say ""
        say "  ${BLUE}$BASE_URL/install.php${RESET}"
        open_url "$BASE_URL/install.php"
        return 0
    fi

    head_ "Running"
    say "  Website   ${BLUE}$BASE_URL/${RESET}"
    say "  Admin     ${BLUE}$BASE_URL/admin${RESET}"
    say ""
    say "  ${DIM}Logs:${RESET} ./dev.sh logs      ${DIM}Stop:${RESET} ./dev.sh stop"
    say ""
    open_url "$BASE_URL/"
}

cmd_stop() {
    if pid="$(server_pid)"; then
        kill "$pid" 2>/dev/null || true
        sleep 1
        kill -0 "$pid" 2>/dev/null && kill -9 "$pid" 2>/dev/null || true
        rm -f "$PID_FILE"
        ok "Dev server stopped (pid $pid)"
    else
        warn "Dev server is not running"
    fi
}

cmd_status() {
    head_ "Status"

    if command -v php >/dev/null 2>&1; then ok "PHP $(php -r 'echo PHP_VERSION;')"; else bad "PHP not installed"; fi

    if mysql_admin >/dev/null 2>&1; then ok "Database server reachable"; else bad "Database server not reachable"; fi

    if pid="$(server_pid)"; then ok "Dev server running (pid $pid, port $PORT)"; else warn "Dev server not running"; fi

    if installed; then
        ok "Installed"
        say "     ${DIM}environment${RESET}  $(env_get APP_ENV)  ${DIM}debug${RESET} $(env_get APP_DEBUG)"
        say "     ${DIM}database${RESET}     $(env_get DB_NAME) @ $(env_get DB_HOST)"
        say "     ${DIM}app url${RESET}      $(env_get APP_URL)"
    else
        warn "Not installed (.env or installed.lock missing)"
    fi

    if pid="$(server_pid)" >/dev/null 2>&1; then
        local code; code="$(curl -sS -o /dev/null -w '%{http_code}' -m 3 "$BASE_URL/" 2>/dev/null || echo 000)"
        case "$code" in
            200|302) ok "Site responding ($code)" ;;
            000)     bad "Site not responding" ;;
            *)       warn "Site responded with $code" ;;
        esac
    fi
    say ""
}

# Ask the database for credentials it can use, creating them if needed.
cmd_fresh() {
    require_php
    start_database || die "The database server must be running."

    local admin; admin="$(mysql_admin)" \
        || die "Cannot connect to MySQL as an administrator. Create the database and user yourself, then use ./dev.sh install"

    local db="${DB_NAME:-rahyaft}" user="${DB_USER:-rahyaft_user}" pass="${DB_PASSWORD:-LocalDev!2026}"

    head_ "Rebuilding the database"
    warn "This drops and recreates '$db'. All content and admin accounts are lost."
    printf '  Continue? [y/N] '
    read -r answer
    case "$answer" in y|Y|yes|YES) ;; *) say "  Cancelled."; exit 0 ;; esac

    $admin <<SQL
DROP DATABASE IF EXISTS \`$db\`;
CREATE DATABASE \`$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$user'@'localhost' IDENTIFIED BY '$pass';
ALTER USER '$user'@'localhost' IDENTIFIED BY '$pass';
GRANT ALL PRIVILEGES ON \`$db\`.* TO '$user'@'localhost';
FLUSH PRIVILEGES;
SQL
    ok "Database '$db' created"

    ensure_directories

    # Write a development .env from the example, then override what we know.
    if [ ! -f "$ROOT/.env" ]; then
        cp "$ROOT/.env.example" "$ROOT/.env"
    fi
    php -r '
        $path = ".env";
        $set = [
            "APP_ENV" => "local",
            "APP_DEBUG" => "true",
            "APP_URL" => $argv[1],
            "APP_KEY" => bin2hex(random_bytes(24)),
            "APP_DEFAULT_LOCALE" => "fa",
            "APP_LOCALES" => "fa,en,ar",
            "DB_HOST" => "127.0.0.1", "DB_PORT" => "3306",
            "DB_NAME" => $argv[2], "DB_USER" => $argv[3], "DB_PASSWORD" => $argv[4],
            "DB_CHARSET" => "utf8mb4",
            "MAIL_MAILER" => "mail",
            "MAIL_FROM_ADDRESS" => "no-reply@localhost",
            "MAIL_FROM_NAME" => "Rahyaft Sanat",
            "MAIL_NOTIFY_TO" => "",
        ];
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $seen = [];
        foreach ($lines as $i => $line) {
            if (preg_match("/^([A-Z0-9_]+)=/", $line, $m) && isset($set[$m[1]])) {
                $v = $set[$m[1]];
                $lines[$i] = $m[1] . "=" . (preg_match("/[\s#\"\x27]/", $v) ? "\"$v\"" : $v);
                $seen[$m[1]] = true;
            }
        }
        foreach ($set as $k => $v) {
            if (!isset($seen[$k])) { $lines[] = "$k=" . (preg_match("/[\s#\"\x27]/", $v) ? "\"$v\"" : $v); }
        }
        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
        chmod($path, 0600);
    ' "$BASE_URL" "$db" "$user" "$pass"
    ok "Wrote .env (development settings)"

    local mysql_client; mysql_client="$(mysql_bin)"
    # Feed the password on stdin-adjacent config rather than argv, which also
    # avoids the client's "password on the command line is insecure" warning.
    MYSQL_PWD="$pass" "$mysql_client" -u "$user" "$db" < database/schema.sql
    ok "Schema loaded ($(grep -c 'CREATE TABLE' database/schema.sql) tables)"

    php database/seed.php | sed 's/^/  /'

    # A predictable local administrator, clearly marked as a development account.
    php -r '
        require "app/Core/Autoloader.php"; App\Core\Autoloader::register(__DIR__);
        require "app/Support/helpers.php";
        App\Core\Env::load(__DIR__ . "/.env");
        App\Core\Config::loadDirectory(__DIR__ . "/config");
        App\Core\Config::set("app.base_path", __DIR__);
        if (!App\Models\AdminUser::emailExists("admin@localhost")) {
            App\Models\AdminUser::create("Local Admin", "admin@localhost", "password1234", "owner");
        }
    '
    ok "Administrator created"

    printf 'installed %s\n' "$(date -u +%FT%TZ)" > "$ROOT/installed.lock"
    chmod 600 "$ROOT/installed.lock"
    ok "Installation locked"

    head_ "Ready"
    say "  ${BOLD}admin@localhost${RESET} / ${BOLD}password1234${RESET}   ${DIM}(development only)${RESET}"
    say ""
    if server_pid >/dev/null 2>&1; then
        say "  Website   ${BLUE}$BASE_URL/${RESET}"
        say "  Admin     ${BLUE}$BASE_URL/admin${RESET}"
    else
        say "  Start the server with: ${BOLD}./dev.sh start${RESET}"
    fi
    say ""
}

cmd_install() {
    head_ "Reset configuration"
    warn "This deletes .env and installed.lock so the wizard can run again."
    warn "The database itself is left alone."
    printf '  Continue? [y/N] '
    read -r answer
    case "$answer" in y|Y|yes|YES) ;; *) say "  Cancelled."; exit 0 ;; esac

    rm -f "$ROOT/.env" "$ROOT/installed.lock"
    ok "Configuration cleared"
    cmd_start
}

cmd_seed() {
    require_php
    installed || die "Not installed yet. Run ./dev.sh fresh or ./dev.sh install first."
    head_ "Importing content"
    warn "Specifications, capabilities and page sections are rewritten from source."
    php database/seed.php | sed 's/^/  /'
    say ""
}

cmd_logs() {
    local app_log="storage/logs/app-$(date +%Y-%m).log"
    head_ "Logs  ${DIM}(Ctrl+C to stop)${RESET}"
    touch "$app_log" "$LOG_FILE"
    tail -f "$app_log" "$LOG_FILE"
}

cmd_check() {
    require_php
    head_ "Environment"
    ok "PHP $(php -r 'echo PHP_VERSION;')"
    for ext in pdo_mysql mbstring json fileinfo gd openssl; do
        if php_has_ext "$ext"; then ok "ext: $ext"; else warn "ext: $ext (missing)"; fi
    done

    head_ "Syntax"
    local failed=0 count=0
    while IFS= read -r f; do
        count=$((count + 1))
        php -l "$f" >/dev/null 2>&1 || { bad "$f"; failed=1; }
    done < <(find app config database resources routes -name '*.php' -not -path '*/Vendor/*')
    [ $failed -eq 0 ] && ok "$count files parse cleanly"

    server_pid >/dev/null 2>&1 || { warn "Dev server not running — skipping route checks"; say ""; return 0; }

    head_ "Routes"
    local bad_routes=0
    for path in "/fa/" "/en/" "/ar/" "/fa/products" "/en/research" "/ar/about" "/fa/contact" "/sitemap.xml" "/robots.txt"; do
        local code; code="$(curl -sS -o /dev/null -w '%{http_code}' -m 5 "$BASE_URL$path" 2>/dev/null || echo 000)"
        case "$code" in 200) ;; *) bad "$path -> $code"; bad_routes=1 ;; esac
    done
    [ $bad_routes -eq 0 ] && ok "public routes respond"

    for path in "/.env" "/app/Core/Database.php" "/database/schema.sql"; do
        local code; code="$(curl -sS -o /dev/null -w '%{http_code}' -m 5 "$BASE_URL$path" 2>/dev/null || echo 000)"
        case "$code" in 403|404) ok "blocked: $path" ;; *) bad "EXPOSED: $path -> $code" ;; esac
    done

    local code; code="$(curl -sS -o /dev/null -w '%{http_code}' -m 5 "$BASE_URL/admin" 2>/dev/null || echo 000)"
    case "$code" in 302) ok "admin requires sign-in" ;; *) bad "admin returned $code" ;; esac
    say ""
}

case "$COMMAND" in
    start)   cmd_start ;;
    stop)    cmd_stop ;;
    restart) cmd_stop; cmd_start ;;
    status)  cmd_status ;;
    install) cmd_install ;;
    fresh)   cmd_fresh ;;
    seed)    cmd_seed ;;
    logs)    cmd_logs ;;
    check)   cmd_check ;;
    *)       die "Unknown command '$COMMAND'. Try: ./dev.sh --help" ;;
esac
