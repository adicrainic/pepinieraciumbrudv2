---
name: ship
description: >-
  Run pre-flight automated tests, lockfile validation, Phinx migration verification,
  and full Storefront & Admin production builds before committing and pushing changes to GitHub.
  Use this skill whenever the user asks to commit and push, ship changes, deploy, or run `/ship`.
---

# Ship Workflow (Verify, Test, Build, Commit & Push)

This skill ensures that **no broken code, out-of-sync lockfile, failing test, or broken build** is ever pushed to GitHub or deployed to production.

## Workflow Steps

### 1. Run Pre-Push Verification Script
Always execute the verification script first from the repository root (`/var/www/html/pepinieraciumbrudV2`):

```bash
./.agents/skills/ship/scripts/pre-push-verify.sh
```

What [pre-push-verify.sh](./scripts/pre-push-verify.sh) verifies automatically:
1. **Secret Guard**: Ensures no `.env` or `wp-config.php` files are tracked or staged.
2. **Deploy Script Syntax**: Validates `deploy/deploy.sh` and `deploy/run-tests.sh` using `bash -n`.
3. **CI Lockfile Sync**: Runs `npm ci --legacy-peer-deps --dry-run` in both `pepinieraciumbrud-storefront/` and `pepinieraciumbrud-admin/` to catch `package-lock.json` mismatches before GitHub Actions runs.
4. **Phinx Database Migrations**: Runs `vendor/bin/phinx migrate -c database/phinx.php` to verify all database migrations apply cleanly.
5. **Automated Test Suite (`./deploy/run-tests.sh`)**:
   - Backend PHP syntax lint (`pepinieraciumbrudshop/tests/lint.php`)
   - Backend unit tests (`pepinieraciumbrudshop/tests/unit/order-unit-test.php`)
   - Storefront cart & order placement tests (`npm test` in `pepinieraciumbrud-storefront/`)
   - Admin order lifecycle tests (`npm test` in `pepinieraciumbrud-admin/`)
   - Full-stack WordPress + WooCommerce Order Placement & Lifecycle integration test (`pepinieraciumbrudshop/tests/integration/order-lifecycle-test.php`)
6. **Production Build Verification**:
   - Storefront SSR production build (`npm run build` in `pepinieraciumbrud-storefront/`)
   - Admin SPA production build (`npm run build` in `pepinieraciumbrud-admin/`)

### 2. Handle Failures Immediately
- If **any** step in `pre-push-verify.sh` fails (non-zero exit code):
  - **DO NOT** run `git commit` or `git push`.
  - Fix the underlying issue, re-run `./.agents/skills/ship/scripts/pre-push-verify.sh`, and confirm it exits with `0`.

### 3. Stage, Commit & Push
Once `pre-push-verify.sh` succeeds:
1. Inspect `git status -s` and `git diff --stat` to review all modified and untracked files.
2. Stage the intended files (`git add -A` or specific paths, ensuring no temporary/secret files are included).
3. Create a clear, descriptive commit message summarizing the changes (`git commit -m "..."`).
4. Push to the current remote branch (`git push origin HEAD`).
5. Report the commit hash, pushed branch, and verification summary to the user.
