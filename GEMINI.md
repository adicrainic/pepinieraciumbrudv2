# Pepiniera Ciumbrud V2 — Project Rules & Memory

This file defines the core rules and persistent guidelines for all development on this repository.

---

## 🗄️ Database Architecture & Migrations Protocol (MANDATORY)

1. **Every Database Structure Change Requires a Phinx Migration**:
   - Whenever a new feature, bug fix, or refactor requires creating a table, adding a column, modifying a column, altering an index, or dropping a structure, **you MUST create and execute a Phinx migration** in `pepinieraciumbrudshop/database/migrations/`.
   - **NEVER** run raw DDL queries manually (`CREATE TABLE`, `ALTER TABLE`, etc.) without a versioned migration file.
   - **NEVER** rely on runtime auto-creation hacks (such as raw `dbDelta` in PHP endpoints) for production changes.

2. **Database Seeders (Only If Needed)**:
   - If initial seed data or baseline system rows are required for a feature to function, create a Phinx seeder in `pepinieraciumbrudshop/database/seeds/` or include an idempotent `INSERT IGNORE` / `ON DUPLICATE KEY UPDATE` inside the migration.

3. **Workflow for Database Changes**:
   ```bash
   cd pepinieraciumbrudshop
   vendor/bin/phinx create DescriptiveMigrationName -c database/phinx.php
   # Write migration logic in database/migrations/...
   composer migrate
   # Test and verify rollback if applicable
   ```

4. **Production Deployment**:
   - The production deployment script (`./deploy/deploy.sh`) automatically runs `vendor/bin/phinx migrate -c database/phinx.php`.
   - All migrations committed to the repository will be executed in sequence without manual intervention.

---

## 🏗️ Monorepo Architecture

- **Storefront** (`pepinieraciumbrud-storefront/`): Nuxt 3 (SSR + Vue 3 + Tailwind CSS + Pinia).
- **Admin Panel** (`pepinieraciumbrud-admin/`): Vue 3 + Vite SPA + Tailwind CSS + Pinia.
- **Backend API** (`pepinieraciumbrudshop/`): Headless WordPress + WooCommerce + Custom REST API plugin (`pepinieraciumbrud-api`).
- **Deploy Automation** (`deploy/`): Nginx configurations, PM2 ecosystem, setup scripts, and `deploy.sh`.

---

## 🔒 Security & Environment Rules

- **Never commit `.env` files** or real API secrets. Commit only `.env.example` and `.env.production.example`.
- All custom admin endpoints in `pepinieraciumbrud-api` require JWT authentication via `class-pcs-jwt.php`.
- Storefront public API uses WooCommerce Store API endpoints (`/wp-json/wc/store/v1`) without secret credentials.

---

## 🧪 Automated Testing & Pre-Push Verification Protocol

- **Before committing and pushing to GitHub**, always run `./.agents/skills/ship/scripts/pre-push-verify.sh` (or use the `ship` skill in `.agents/skills/ship/SKILL.md`).
- This verifies:
  1. No sensitive `.env` or `wp-config.php` files are staged.
  2. `npm ci --legacy-peer-deps --dry-run` succeeds in both `pepinieraciumbrud-storefront/` and `pepinieraciumbrud-admin/`.
  3. Phinx migrations apply cleanly (`composer migrate`).
  4. All automated checks & integration tests pass (`./deploy/run-tests.sh`).
  5. Both `pepinieraciumbrud-storefront` (`npm run build`) and `pepinieraciumbrud-admin` (`npm run build`) compile cleanly for production.


