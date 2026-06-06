#!/bin/bash
# =============================================================
# PRB Klinik Dokterku — Production Deploy Script
# Jalankan: bash deploy.sh
# =============================================================
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✅ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠️  $1${NC}"; }
fail() { echo -e "${RED}❌ $1${NC}"; exit 1; }

echo "======================================================"
echo " PRB Klinik Dokterku — Deploy ke Production"
echo "======================================================"

# 1. Cek PHP
php --version | grep -q "8\." || fail "PHP 8+ diperlukan"
ok "PHP $(php --version | head -1 | cut -d' ' -f2)"

# 2. Cek .env production
[[ -f ".env" ]] || fail ".env tidak ditemukan. Copy dari env.production.sample"
APP_DEBUG=$(grep "^APP_DEBUG=" .env | cut -d= -f2)
[[ "$APP_DEBUG" == "false" ]] || warn "APP_DEBUG masih true — ubah ke false untuk production!"

# 3. Maintenance mode
php artisan down --secret="klinik-maintenance-2024"
ok "Maintenance mode aktif"

# 4. Pull latest code
git pull origin main 2>/dev/null || warn "Git pull skip (bukan git repo atau tidak ada remote)"

# 5. Install dependencies (no dev)
composer install --no-dev --optimize-autoloader --no-interaction
ok "Composer dependencies installed"

# 6. Run migrations
php artisan migrate --force
ok "Migrations selesai"

# 7. Clear & cache semua
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
ok "Config/route/view cache dibuild"

# 8. Storage link
php artisan storage:link 2>/dev/null || warn "Storage link sudah ada"
ok "Storage link"

# 9. Set permissions
chmod -R 775 storage bootstrap/cache
ok "Permissions set"

# 10. Restart WA service via PM2
if command -v pm2 &>/dev/null; then
    pm2 restart wa-klinik 2>/dev/null || pm2 start wa-service/server.js --name wa-klinik
    ok "WA Service restarted via PM2"
else
    warn "PM2 belum install. Jalankan: npm install -g pm2 && pm2 start wa-service/server.js --name wa-klinik"
fi

# 11. Restart queue worker
if command -v pm2 &>/dev/null; then
    pm2 restart queue-klinik 2>/dev/null || pm2 start --interpreter php --name queue-klinik -- artisan queue:work --sleep=3 --tries=3 --max-time=3600
    ok "Queue worker restarted"
fi

# 12. Out of maintenance
php artisan up
ok "Maintenance mode OFF — app live!"

echo ""
echo "======================================================"
echo " Deploy selesai!"
echo " Cek: $(grep '^APP_URL=' .env | cut -d= -f2)"
echo ""
echo " Langkah manual setelah deploy:"
echo "  1. Cek WA service: curl http://localhost:3001/status"
echo "  2. Set cron: crontab -e"
echo "     * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo "  3. Buka dashboard → Pusat Notifikasi → cek status WA"
echo "======================================================"
