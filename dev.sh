#!/usr/bin/env bash
#
# Rahyaft Sanat — local development helper (Docker).
#
# Everything runs in containers: PHP, Apache, MariaDB, a mail catcher and a
# database browser. Nothing is installed on the host except Docker itself.
# Development only — production uses Apache and index.php directly.
#
# Usage:
#   ./dev.sh              start the stack (same as `start`)
#   ./dev.sh start        start the containers, installing on first run
#   ./dev.sh stop         stop the containers, keeping the database
#   ./dev.sh restart      stop, then start
#   ./dev.sh status       show what is running and what is configured
#   ./dev.sh install      re-run configuration and installation (keeps the data)
#   ./dev.sh fresh        rebuild the database from scratch and reimport content
#   ./dev.sh seed         re-import the content from database/content/
#   ./dev.sh logs         follow the container and application logs
#   ./dev.sh check        run environment, syntax and route checks
#   ./dev.sh shell        open a shell inside the web container
#   ./dev.sh db           open a MariaDB client on the site database
#   ./dev.sh build        rebuild the PHP image (after editing docker/)
#   ./dev.sh down         remove the containers (add --volumes to drop the data)
#
# Options:
#   --port N              publish the site on a different port (default 8100)
#   --no-open             do not open a browser
#   -y, --yes             do not ask for confirmation on destructive commands
#   --volumes             with `down`, also delete the database volume

set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")"

ROOT="$(pwd)"
WEB_PORT="${WEB_PORT:-8100}"
OPEN_BROWSER=1
ASSUME_YES=0
DROP_VOLUMES=0

# The container's own configuration, written by the entrypoint on every start.
# The .env in the project root belongs to the host and is never touched.
STATE_ENV="$ROOT/docker/state/app.env"
STATE_LOCK="$ROOT/docker/state/installed.lock"

# Set by ensure_docker(); "plugin" is `docker compose`, "standalone" is
# `docker-compose`.
COMPOSE_KIND="plugin"

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
        --port)     WEB_PORT="${2:-}"; shift 2 ;;
        --port=*)   WEB_PORT="${1#*=}"; shift ;;
        --no-open)  OPEN_BROWSER=0; shift ;;
        -y|--yes)   ASSUME_YES=1; shift ;;
        --volumes|-v) DROP_VOLUMES=1; shift ;;
        -h|--help)  sed -n '2,30p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
        -*)         die "Unknown option: $1" ;;
        *)          [ -z "$COMMAND" ] && COMMAND="$1"; shift ;;
    esac
done
COMMAND="${COMMAND:-start}"

case "$WEB_PORT" in
    ''|*[!0-9]*) die "Port must be a number (got '$WEB_PORT')." ;;
esac

# Compose reads these when publishing ports and when building APP_URL.
export WEB_PORT
BASE_URL="http://localhost:$WEB_PORT"

# --- Docker -----------------------------------------------------------------

# Prefer the v2 plugin, fall back to the standalone binary.
compose_cmd() {
    if docker compose version >/dev/null 2>&1; then
        printf 'plugin'
    elif command -v docker-compose >/dev/null 2>&1; then
        printf 'standalone'
    else
        return 1
    fi
}

dc() {
    case "$COMPOSE_KIND" in
        plugin)     docker compose "$@" ;;
        standalone) docker-compose "$@" ;;
    esac
}

# Make sure the daemon is up. On macOS that means launching Docker Desktop and
# waiting for it, which takes a while on a cold boot.
ensure_docker() {
    command -v docker >/dev/null 2>&1 \
        || die "Docker is not installed. Get Docker Desktop from https://docker.com/get-started"

    COMPOSE_KIND="$(compose_cmd)" \
        || die "Docker Compose is not available. Update Docker Desktop, or install the compose plugin."

    if docker info >/dev/null 2>&1; then
        return 0
    fi

    if [ "$(uname -s)" = "Darwin" ] && [ -d /Applications/Docker.app ]; then
        warn "Docker Desktop is not running — starting it…"
        open -a Docker >/dev/null 2>&1 || true
        local waited=0
        while [ "$waited" -lt 120 ]; do
            if docker info >/dev/null 2>&1; then
                ok "Docker Desktop is ready"
                return 0
            fi
            sleep 2
            waited=$((waited + 2))
            [ $((waited % 20)) -eq 0 ] && say "    ${DIM}still starting… (${waited}s)${RESET}"
        done
        die "Docker Desktop did not become ready in 120s. Open it manually and retry."
    fi

    die "The Docker daemon is not running. Start Docker and retry."
}

confirm() {
    [ "$ASSUME_YES" -eq 1 ] && return 0
    printf '  Continue? [y/N] '
    read -r answer
    case "$answer" in y|Y|yes|YES) return 0 ;; *) say "  Cancelled."; exit 0 ;; esac
}

# --- Helpers ----------------------------------------------------------------

# Read a key from the container's .env without sourcing it.
env_get() {
    local key="$1" line value
    [ -f "$STATE_ENV" ] || return 0
    line="$(grep -E "^${key}=" "$STATE_ENV" | tail -1 || true)"
    [ -z "$line" ] && return 0
    value="${line#*=}"
    value="${value%\"}"; value="${value#\"}"
    value="${value%\'}"; value="${value#\'}"
    printf '%s' "$value"
}

# Compose refuses to bind-mount a file that does not exist yet.
ensure_state_files() {
    mkdir -p "$ROOT/docker/state"
    [ -e "$STATE_ENV" ]  || : > "$STATE_ENV"
    [ -e "$STATE_LOCK" ] || : > "$STATE_LOCK"
}

app_running() { [ "$(dc ps -q app 2>/dev/null | wc -l | tr -d ' ')" != "0" ]; }

# curl prints "000" and exits non-zero when it cannot connect, so take its
# output and only substitute a value when there was none at all.
http_code() {
    local code
    code="$(curl -sS -o /dev/null -w '%{http_code}' -m 5 "$BASE_URL$1" 2>/dev/null || true)"
    printf '%s' "${code:-000}"
}

# The site is only usable once the entrypoint has finished installing.
wait_for_site() {
    local waited=0
    while [ "$waited" -lt 180 ]; do
        case "$(http_code /fa)" in
            200|302) return 0 ;;
        esac
        sleep 2
        waited=$((waited + 2))
    done
    return 1
}

open_url() {
    [ "$OPEN_BROWSER" -eq 1 ] || return 0
    if command -v open >/dev/null 2>&1;       then open "$1" >/dev/null 2>&1 || true
    elif command -v xdg-open >/dev/null 2>&1; then xdg-open "$1" >/dev/null 2>&1 || true
    fi
}

installed() { [ -s "$STATE_LOCK" ] && [ -s "$STATE_ENV" ]; }

# --- Commands ---------------------------------------------------------------

cmd_start() {
    head_ "Rahyaft Sanat — local development"

    ensure_docker
    ok "Docker $(docker version --format '{{.Server.Version}}' 2>/dev/null || echo '')"
    ensure_state_files

    say ""
    dc up -d 2>&1 | sed 's/^/  /'

    head_ "Waiting for the site"
    if wait_for_site; then
        ok "Site responding on port $WEB_PORT"
    else
        bad "The site did not respond within 180s"
        say ""
        dc logs --tail 25 app 2>&1 | sed 's/^/    /'
        say ""
        say "  Full logs:  ${BOLD}./dev.sh logs${RESET}"
        say ""
        exit 1
    fi

    head_ "Running"
    say "  Website   ${BLUE}$BASE_URL/${RESET}"
    say "  Admin     ${BLUE}$BASE_URL/admin${RESET}   ${DIM}admin@localhost / password1234${RESET}"
    say "  Mail      ${BLUE}http://localhost:${MAILPIT_PORT:-8025}${RESET}   ${DIM}every message the site sends${RESET}"
    say "  Database  ${BLUE}http://localhost:${ADMINER_PORT:-8081}${RESET}   ${DIM}server db, user rahyaft_user, password rahyaft${RESET}"
    say ""
    say "  ${DIM}Logs:${RESET} ./dev.sh logs      ${DIM}Stop:${RESET} ./dev.sh stop      ${DIM}Shell:${RESET} ./dev.sh shell"
    say ""
    open_url "$BASE_URL/"
}

cmd_stop() {
    ensure_docker
    head_ "Stopping"
    dc stop 2>&1 | sed 's/^/  /'
    ok "Containers stopped (the database is kept)"
    say ""
}

cmd_down() {
    ensure_docker
    head_ "Removing containers"
    if [ "$DROP_VOLUMES" -eq 1 ]; then
        warn "--volumes given: the database will be deleted as well."
        confirm
        dc down -v 2>&1 | sed 's/^/  /'
        : > "$STATE_ENV"; : > "$STATE_LOCK"
        ok "Containers and database removed"
    else
        dc down 2>&1 | sed 's/^/  /'
        ok "Containers removed (the database volume is kept)"
    fi
    say ""
}

cmd_status() {
    head_ "Status"

    if ! command -v docker >/dev/null 2>&1; then
        bad "Docker is not installed"; say ""; return 0
    fi
    COMPOSE_KIND="$(compose_cmd)" || { bad "Docker Compose is not available"; say ""; return 0; }

    if docker info >/dev/null 2>&1; then
        ok "Docker daemon running"
    else
        bad "Docker daemon not running — ./dev.sh start will launch it"
        say ""
        return 0
    fi

    head_ "Containers"
    local ps_out; ps_out="$(dc ps --format 'table {{.Service}}\t{{.Status}}' 2>/dev/null || true)"
    if [ -z "$ps_out" ] || [ "$(printf '%s\n' "$ps_out" | wc -l | tr -d ' ')" -le 1 ]; then
        warn "No containers running — start them with ./dev.sh start"
    else
        printf '%s\n' "$ps_out" | sed 's/^/  /'
    fi

    head_ "Site"
    local code; code="$(http_code /fa)"
    case "$code" in
        200) ok "Responding at $BASE_URL/ (200)" ;;
        000) bad "Not responding at $BASE_URL/" ;;
        *)   warn "Responded with $code" ;;
    esac

    if installed; then
        ok "Installed"
        say "     ${DIM}environment${RESET}  $(env_get APP_ENV)  ${DIM}debug${RESET} $(env_get APP_DEBUG)"
        say "     ${DIM}database${RESET}     $(env_get DB_NAME) @ $(env_get DB_HOST)"
        say "     ${DIM}app url${RESET}      $(env_get APP_URL)"
    else
        warn "Not installed yet — ./dev.sh start installs on first run"
    fi
    say ""
}

# Re-run configuration and installation against the existing database.
cmd_install() {
    ensure_docker
    head_ "Reinstall"
    warn "This rewrites the container's .env and re-runs the installation steps."
    warn "The database and its content are left alone."
    confirm

    ensure_state_files
    : > "$STATE_ENV"; : > "$STATE_LOCK"
    ok "Container configuration cleared"

    dc up -d 2>&1 | sed 's/^/  /'
    dc restart app >/dev/null 2>&1 || true
    wait_for_site || die "The site did not come back. See ./dev.sh logs"
    ok "Reinstalled"
    say ""
    cmd_status
}

# Destroy the database and rebuild it from schema.sql + database/content/.
cmd_fresh() {
    ensure_docker
    head_ "Rebuilding the database"
    warn "This drops the database volume. All content and admin accounts are lost."
    confirm

    ensure_state_files
    dc down -v 2>&1 | sed 's/^/  /'
    : > "$STATE_ENV"; : > "$STATE_LOCK"
    ok "Database removed"

    say ""
    dc up -d --build 2>&1 | sed 's/^/  /'

    head_ "Installing"
    if wait_for_site; then
        dc logs app 2>&1 \
            | sed -e 's/\x1b\[[0-9;]*m//g' -e 's/^[a-z-]*app *| *//' \
            | grep -E '✓|Content import|^ *[a-z_]+ +[0-9]+ *$' \
            | sed 's/^/  /' || true
    else
        bad "The site did not come up"
        dc logs --tail 25 app 2>&1 | sed 's/^/    /'
        exit 1
    fi

    head_ "Ready"
    say "  ${BOLD}admin@localhost${RESET} / ${BOLD}password1234${RESET}   ${DIM}(development only)${RESET}"
    say ""
    say "  Website   ${BLUE}$BASE_URL/${RESET}"
    say "  Admin     ${BLUE}$BASE_URL/admin${RESET}"
    say ""
    open_url "$BASE_URL/"
}

cmd_seed() {
    ensure_docker
    app_running || die "The containers are not running. Start them with ./dev.sh start"
    head_ "Importing content"
    warn "Specifications, capabilities and page sections are rewritten from source."
    dc exec -T app php database/seed.php | sed 's/^/  /'
    say ""
}

cmd_logs() {
    ensure_docker
    local app_log="$ROOT/storage/logs/app-$(date +%Y-%m).log"
    head_ "Logs  ${DIM}(Ctrl+C to stop)${RESET}"
    mkdir -p "$ROOT/storage/logs"
    touch "$app_log"

    # The application's own log lives on disk; Apache and the bootstrap write to
    # the container's output. Follow both.
    tail -f "$app_log" &
    local tail_pid=$!
    trap 'kill "$tail_pid" 2>/dev/null || true' EXIT INT TERM
    dc logs -f app
}

cmd_shell() {
    ensure_docker
    app_running || die "The containers are not running. Start them with ./dev.sh start"
    dc exec app bash
}

cmd_db() {
    ensure_docker
    app_running || die "The containers are not running. Start them with ./dev.sh start"
    dc exec db mariadb --disable-ssl-verify-server-cert \
        -u"$(env_get DB_USER)" -p"$(env_get DB_PASSWORD)" "$(env_get DB_NAME)"
}

cmd_build() {
    ensure_docker
    ensure_state_files
    head_ "Rebuilding the PHP image"
    dc build app 2>&1 | sed 's/^/  /'
    ok "Image rebuilt — ./dev.sh restart to use it"
    say ""
}

cmd_check() {
    ensure_docker
    app_running || die "The containers are not running. Start them with ./dev.sh start"

    head_ "Environment"
    ok "PHP $(dc exec -T app php -r 'echo PHP_VERSION;')"
    local modules; modules="$(dc exec -T app php -m)"
    for ext in pdo_mysql mbstring json fileinfo gd openssl zip; do
        if printf '%s\n' "$modules" | grep -qix "$ext"; then ok "ext: $ext"; else warn "ext: $ext (missing)"; fi
    done

    head_ "Syntax"
    local out
    out="$(dc exec -T app bash -c '
        failed=0; count=0
        while IFS= read -r f; do
            count=$((count + 1))
            php -l "$f" >/dev/null 2>&1 || { echo "FAIL $f"; failed=1; }
        done < <(find app config database resources routes -name "*.php" -not -path "*/Vendor/*")
        echo "COUNT $count"
        exit $failed
    ')" || true
    printf '%s\n' "$out" | grep '^FAIL ' | sed "s/^FAIL /  ${RED}✗${RESET} /" || true
    if ! printf '%s\n' "$out" | grep -q '^FAIL '; then
        ok "$(printf '%s\n' "$out" | sed -n 's/^COUNT //p') files parse cleanly"
    fi

    head_ "Routes"
    local bad_routes=0 code
    for path in "/fa" "/en" "/ar" "/fa/products" "/en/research" "/ar/about" "/fa/contact" "/sitemap.xml" "/robots.txt"; do
        code="$(http_code "$path")"
        case "$code" in 200) ;; *) bad "$path -> $code"; bad_routes=1 ;; esac
    done
    [ $bad_routes -eq 0 ] && ok "public routes respond"

    for path in "/.env" "/app/Core/Database.php" "/database/schema.sql" "/installed.lock"; do
        code="$(http_code "$path")"
        case "$code" in 403|404) ok "blocked: $path" ;; *) bad "EXPOSED: $path -> $code" ;; esac
    done

    code="$(http_code /admin)"
    case "$code" in 302) ok "admin requires sign-in" ;; *) bad "admin returned $code" ;; esac

    head_ "Mail"
    code="$(curl -sS -o /dev/null -w '%{http_code}' -m 5 \
        "http://localhost:${MAILPIT_PORT:-8025}/api/v1/messages" 2>/dev/null || true)"
    case "$code" in 200) ok "Mailpit reachable on ${MAILPIT_PORT:-8025}" ;; *) warn "Mailpit not reachable" ;; esac
    say ""
}

case "$COMMAND" in
    start)   cmd_start ;;
    stop)    cmd_stop ;;
    restart) cmd_stop; cmd_start ;;
    down)    cmd_down ;;
    status)  cmd_status ;;
    install) cmd_install ;;
    fresh)   cmd_fresh ;;
    seed)    cmd_seed ;;
    logs)    cmd_logs ;;
    shell)   cmd_shell ;;
    db)      cmd_db ;;
    build)   cmd_build ;;
    check)   cmd_check ;;
    *)       die "Unknown command '$COMMAND'. Try: ./dev.sh --help" ;;
esac
