#!/usr/bin/env bash
# ==============================================================================
# Pre-Commit & Pre-Push Verification Script for Pepiniera Ciumbrud V2
# Simulates CI + Deploy checks locally before allowing git commit & push.
# ==============================================================================

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/../../../.." && pwd)"

echo -e "${BLUE}=================================================================${NC}"
echo -e "${BLUE}   🛡️  Pepiniera Ciumbrud V2 — Pre-Push Build & Deploy Check     ${NC}"
echo -e "${BLUE}=================================================================${NC}"

cd "$ROOT_DIR"

# 1. Security Check: Ensure no .env or wp-config.php files are staged
echo -e "\n${BLUE}[1/6] 🔒 Checking for sensitive files in Git status...${NC}"
FORBIDDEN_FILES=$(git status --porcelain | awk '{print $2}' | grep -E '(^|/)\.env(\.|$)|wp-config\.php$' | grep -vE '\.env(\..+)?\.example$' || true)
if [ -n "$FORBIDDEN_FILES" ]; then
    echo -e "${RED}❌ Forbidden secret/environment file(s) detected in Git status:${NC}"
    echo "$FORBIDDEN_FILES"
    exit 1
fi
echo -e "${GREEN}✔ No sensitive environment files exposed.${NC}"

# 2. Validate Deployment Scripts Syntax
echo -e "\n${BLUE}[2/6] 📜 Validating deploy script syntax...${NC}"
bash -n "$ROOT_DIR/deploy/deploy.sh"
bash -n "$ROOT_DIR/deploy/run-tests.sh"
echo -e "${GREEN}✔ Deploy scripts syntax is valid.${NC}"

# 3. Verify npm ci Lockfile Sync (GitHub Actions CI simulation)
echo -e "\n${BLUE}[3/6] 📦 Verifying npm lockfile sync (npm ci --dry-run)...${NC}"
cd "$ROOT_DIR/pepinieraciumbrud-storefront"
npm ci --legacy-peer-deps --dry-run > /dev/null
echo -e "  ✔ Storefront package-lock.json is in sync"

cd "$ROOT_DIR/pepinieraciumbrud-admin"
npm ci --legacy-peer-deps --dry-run > /dev/null
echo -e "  ✔ Admin package-lock.json is in sync"

# 4. Verify Phinx Database Migrations
echo -e "\n${BLUE}[4/6] 🗄️  Verifying Phinx Database Migrations...${NC}"
cd "$ROOT_DIR/pepinieraciumbrudshop"
if [ -f "vendor/bin/phinx" ] && [ -f "wp-config.php" ]; then
    vendor/bin/phinx migrate -c database/phinx.php
    echo -e "${GREEN}✔ Phinx migrations verified.${NC}"
else
    echo -e "${YELLOW}ℹ️ Skipping live Phinx migration check (phinx or wp-config.php not present).${NC}"
fi

# 5. Run Full Automated Test Suite (Lint + Unit + Storefront + Admin + Order Lifecycle Integration)
echo -e "\n${BLUE}[5/6] 🧪 Running Automated Checks & Order Lifecycle Integration Tests...${NC}"
"$ROOT_DIR/deploy/run-tests.sh"

# 6. Verify Production Builds (Storefront Nuxt 3 + Admin Vue 3)
echo -e "\n${BLUE}[6/6] 🏗️  Verifying Storefront & Admin Production Builds...${NC}"
cd "$ROOT_DIR/pepinieraciumbrud-storefront"
npm run build

# If a standalone local storefront server is running from .output/server/index.mjs, restart it so it picks up the new build chunks
if pgrep -f "pepinieraciumbrud-storefront/\.output/server/index\.mjs" > /dev/null 2>&1; then
    echo -e "${BLUE}🔄 Restarting local Storefront server (:3000) with fresh build...${NC}"
    pkill -f "pepinieraciumbrud-storefront/\.output/server/index\.mjs" || true
    sleep 1
    if ! pgrep -f "pepinieraciumbrud-storefront/\.output/server/index\.mjs" > /dev/null 2>&1; then
        NODE_ENV=production PORT=3000 HOST=0.0.0.0 NITRO_PORT=3000 NITRO_HOST=0.0.0.0 nohup node "$ROOT_DIR/pepinieraciumbrud-storefront/.output/server/index.mjs" > /tmp/pepiniera-storefront.log 2>&1 &
        sleep 1
    fi
    echo -e "${GREEN}✔ Local Storefront server restarted on port 3000.${NC}"
fi

cd "$ROOT_DIR/pepinieraciumbrud-admin"
npm run build

echo -e "\n${GREEN}=================================================================${NC}"
echo -e "${GREEN}   🚀 All pre-push checks, tests, and builds succeeded!          ${NC}"
echo -e "${GREEN}   Safe to git commit and git push.                              ${NC}"
echo -e "${GREEN}=================================================================${NC}"
