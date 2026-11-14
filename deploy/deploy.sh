#!/usr/bin/env bash
# ==============================================================================
# Deploy Script for Pepiniera Ciumbrud V2
# Usage: ./deploy/deploy.sh [branch]
# Default branch: main
# ==============================================================================

set -e

# Terminal colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Base directory (script resides in deploy/)
DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$DEPLOY_DIR")"
BRANCH="${1:-main}"

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}   🌿 Pepiniera Ciumbrud V2 — Automated Deployment   ${NC}"
echo -e "${BLUE}======================================================${NC}"
echo -e "Target directory: ${YELLOW}$ROOT_DIR${NC}"
echo -e "Target branch:    ${YELLOW}$BRANCH${NC}"
echo ""

cd "$ROOT_DIR"

# 1. Update source code from Git
echo -e "${BLUE}[1/6] 📥 Pulling latest code from GitHub...${NC}"
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull origin "$BRANCH"
echo -e "${GREEN}✔ Code updated successfully.${NC}"

# 2. Run Database Migrations (Phinx)
echo -e "${BLUE}[2/6] 🗄️ Running Database Migrations (Phinx)...${NC}"
cd "$ROOT_DIR/pepinieraciumbrudshop"
if [ -f "composer.json" ]; then
    composer install --no-dev --prefer-dist --no-interaction
    vendor/bin/phinx migrate -c database/phinx.php
fi
echo -e "${GREEN}✔ Database migrations applied successfully.${NC}"

# 3. Build Nuxt 3 Storefront
echo -e "${BLUE}[3/6] 🛍️ Building Nuxt 3 Storefront...${NC}"
cd "$ROOT_DIR/pepinieraciumbrud-storefront"

if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️ Warning: .env not found in storefront! Copying from .env.production.example...${NC}"
    cp .env.production.example .env
    echo -e "${YELLOW}Please check .env to make sure domain settings are correct!${NC}"
fi

npm install --prefer-offline --no-audit
npm run build
echo -e "${GREEN}✔ Storefront build completed.${NC}"

# 4. Reload PM2 Process (Zero Downtime)
echo -e "${BLUE}[4/6] 🔄 Reloading Storefront via PM2...${NC}"
if pm2 describe pepiniera-storefront > /dev/null 2>&1; then
    pm2 reload "$ROOT_DIR/deploy/ecosystem.config.cjs" --update-env
else
    pm2 start "$ROOT_DIR/deploy/ecosystem.config.cjs"
    pm2 save
fi
echo -e "${GREEN}✔ Storefront is live.${NC}"

# 5. Build Vue 3 Admin Panel
echo -e "${BLUE}[5/6] ⚙️ Building Vue 3 Admin Panel...${NC}"
cd "$ROOT_DIR/pepinieraciumbrud-admin"

if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚠️ Warning: .env not found in admin! Copying from .env.production.example...${NC}"
    cp .env.production.example .env
    echo -e "${YELLOW}Please check .env to make sure domain settings are correct!${NC}"
fi

npm install --prefer-offline --no-audit
npm run build
echo -e "${GREEN}✔ Admin build completed (static assets in dist/).${NC}"

# 6. Fix permissions for WordPress uploads & uploads folder
echo -e "${BLUE}[6/6] 🔒 Setting correct permissions on WordPress uploads...${NC}"
cd "$ROOT_DIR"
if [ -d "pepinieraciumbrudshop/wp-content/uploads" ]; then
    sudo chown -R www-data:www-data pepinieraciumbrudshop/wp-content/uploads
    sudo chmod -R 775 pepinieraciumbrudshop/wp-content/uploads
fi

# Optional: reload PHP-FPM to clear OPcache
if systemctl is-active --quiet php8.2-fpm; then
    sudo systemctl reload php8.2-fpm
elif systemctl is-active --quiet php8.3-fpm; then
    sudo systemctl reload php8.3-fpm
fi

echo ""
echo -e "${GREEN}======================================================${NC}"
echo -e "${GREEN}   🎉 Deployment completed successfully!            ${NC}"
echo -e "${GREEN}======================================================${NC}"
