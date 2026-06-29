#!/usr/bin/env sh
# Production entrypoint. Deliberately NO `set -e`: a migrate/seed hiccup, a slow/unready
# Postgres, or a gateway failure must never crash the container or block serving. Container
# liveness is tied only to Laravel on :8080; the Node gateway is a detached best-effort child.
cd /app || exit 1

# 1. Ensure a .env exists.
[ -f .env ] || cp .env.production .env

# 2. Ensure a valid APP_KEY and export it. Read the base64 line SPECIFICALLY (an empty
#    `APP_KEY=` line from .env.production must never win) and generate one if absent.
KEYLINE="$(grep '^APP_KEY=base64:' .env 2>/dev/null | head -1)"
if [ -z "$KEYLINE" ]; then
    php artisan key:generate --force || true
    KEYLINE="$(grep '^APP_KEY=base64:' .env 2>/dev/null | head -1)"
fi
export APP_KEY="$(printf '%s' "$KEYLINE" | cut -d '=' -f2-)"

# 3. Ensure the SQLite database file and its directory exist (default path).
DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"
export DB_DATABASE
DB_DIR="$(dirname "$DB_DATABASE")"
mkdir -p "$DB_DIR" 2>/dev/null || true
[ -f "$DB_DATABASE" ] || touch "$DB_DATABASE" 2>/dev/null || true

# Make runtime dirs writable (best effort). storage/wa holds the single WhatsApp session
# creds — persist /app/storage (or /app/storage/wa) as a volume to survive redeploys.
mkdir -p storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/logs storage/wa bootstrap/cache 2>/dev/null || true
mkdir -p /app/storage/wa 2>/dev/null || true
chmod -R ug+rwX storage bootstrap/cache "$DB_DIR" 2>/dev/null || true

# 4. Start the embedded Baileys gateway in the BACKGROUND before serving. It binds
#    127.0.0.1:3001 and talks to Laravel over localhost. It has its own Baileys reconnect
#    logic, so it is NOT auto-respawned here; if it dies, the WhatsApp link can be re-scanned
#    at /conectar. Never let it block or crash the container.
(
    cd /app/gateway && PORT=3001 \
      PANEL_WEBHOOK_URL=http://127.0.0.1:8080/api/wa/inbound \
      GATEWAY_TOKEN="$GATEWAY_TOKEN" \
      AUTH_DIR=/app/storage/wa \
      node src/index.js >> /app/storage/logs/gateway.log 2>&1
) &

# 5. Migrate + seed in the BACKGROUND, best-effort with a retry loop, so a
#    slow/unready database never blocks or crashes serving.
(
    i=1
    while [ "$i" -le 40 ]; do
        if php artisan migrate --force 2>&1; then
            php artisan db:seed --force 2>&1 || true
            echo "[entrypoint] migrate/seed completed on attempt $i"
            break
        fi
        echo "[entrypoint] migrate attempt $i failed; retrying in 3s..."
        i=$((i + 1))
        sleep 3
    done
) &

# 6. Clear (do NOT cache) config so each request reads the live exported APP_KEY — caching risks
#    baking an empty key if the env wasn't ready at cache time, which 500s every route.
php artisan config:clear || true

# 7. Serve under an auto-respawn loop: if a request ever crashes the dev server, it restarts
#    immediately, so the app is never permanently down (no `set -e`, so the loop always continues).
while true; do
    php artisan serve --host 0.0.0.0 --port 8080
    echo "[entrypoint] server exited (code $?); restarting in 1s..."
    sleep 1
done
