#!/usr/bin/env bash
set -e

echo "================================================================"
echo " Laravel Production Boot — $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================================"

cd /var/www/html

# ── 1. Generate APP_KEY if missing ────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "[boot] Generating APP_KEY..."
    php artisan key:generate --force
else
    echo "[boot] APP_KEY is set."
fi

# ── 2. Create storage symlink (public disk) ───────────────────────────────────
echo "[boot] Linking storage..."
php artisan storage:link --force 2>/dev/null || true

# ── 3. Run database migrations ────────────────────────────────────────────────
echo "[boot] Running migrations..."
php artisan migrate --force

# ── 4. Clear & rebuild caches ─────────────────────────────────────────────────
echo "[boot] Caching config, routes, views, events..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── 5. Fix storage permissions ────────────────────────────────────────────────
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "[boot] Boot complete. Starting services via supervisord..."
echo "================================================================"

# ── 6. Start supervisor (manages PHP-FPM + Nginx + queue worker) ──────────────
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
