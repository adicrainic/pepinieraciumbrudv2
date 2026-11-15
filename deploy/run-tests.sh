#!/usr/bin/env bash
# ==============================================================================
# Automated Checks & Order Lifecycle Integration Test Runner
# Usage: ./deploy/run-tests.sh [--ci]
# ==============================================================================

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$DEPLOY_DIR")"
CI_ONLY=false

if [ "${1:-}" = "--ci" ]; then
    CI_ONLY=true
fi

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}   🧪 Pepiniera Ciumbrud V2 — Automated Test Suite    ${NC}"
echo -e "${BLUE}======================================================${NC}"

# 1. Backend PHP Syntax Lint
echo -e "\n${BLUE}[1/5] 🔍 Running Backend PHP Syntax Checks...${NC}"
php "$ROOT_DIR/pepinieraciumbrudshop/tests/lint.php"

# 2. Backend PHP Unit Tests (JWT & Order Note Translations)
echo -e "\n${BLUE}[2/5] 🧪 Running Backend Unit Tests...${NC}"
php "$ROOT_DIR/pepinieraciumbrudshop/tests/unit/order-unit-test.php"

# 3. Storefront Automated Tests (Cart & Order Placement)
echo -e "\n${BLUE}[3/5] 🛍️ Running Storefront Order Placement Tests...${NC}"
cd "$ROOT_DIR/pepinieraciumbrud-storefront"
npm test

# 4. Admin Panel Automated Tests (Order Lifecycle & Status Transitions)
echo -e "\n${BLUE}[4/5] ⚙️ Running Admin Panel Order Lifecycle Tests...${NC}"
cd "$ROOT_DIR/pepinieraciumbrud-admin"
npm test

# 5. Full-Stack Order Placement & Lifecycle Integration Test (WordPress + WooCommerce + PCS API)
echo -e "\n${BLUE}[5/5] 🔄 Running Full Order Lifecycle Integration Test...${NC}"
if [ "$CI_ONLY" = true ] || [ ! -f "$ROOT_DIR/pepinieraciumbrudshop/wp-config.php" ]; then
    echo -e "${YELLOW}ℹ️ Skipping live WordPress DB integration test (no local wp-config.php or --ci flag set).${NC}"
else
    php "$ROOT_DIR/pepinieraciumbrudshop/tests/integration/order-lifecycle-test.php"
fi

echo -e "\n${GREEN}======================================================${NC}"
echo -e "${GREEN}   ✔ All automated checks and tests passed!           ${NC}"
echo -e "${GREEN}======================================================${NC}"
