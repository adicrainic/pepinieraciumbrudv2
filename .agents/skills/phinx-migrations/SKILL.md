---
name: phinx-migrations
description: >-
  Guide and workflow for database schema migrations and seeders using Phinx for Pepiniera Ciumbrud V2.
  Use whenever adding new features that alter database schema, create or modify tables/columns/indexes,
  or populate initial data.
---

# Database Migrations with Phinx

All database schema modifications and seeders in this project **must** be implemented via Phinx migrations. Never alter database tables manually or directly in production.

## Configuration & Paths

- Working Directory: `pepinieraciumbrudshop/`
- Config File: `database/phinx.php` (dynamically loads credentials from `wp-config.php`)
- Migrations Directory: `database/migrations/`
- Seeds Directory: `database/seeds/`
- Table Prefix: `pcs_` (e.g. `pcs_settings`, `pcs_contact_messages`, `pcs_phinxlog`)

---

## 1. Creating a Migration

From `pepinieraciumbrudshop/`:

```bash
vendor/bin/phinx create MyMigrationName -c database/phinx.php
```

This creates a timestamped migration in `database/migrations/YYYYMMDDHHMMSS_my_migration_name.php`.

---

## 2. Writing Migrations

Use either Phinx's fluent Table API or raw SQL statements via `$this->execute()`.

### Example A: Creating a New Table
```php
<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCustomTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('pcs_custom_features', [
            'id' => false,
            'primary_key' => 'id',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table->addColumn('id', 'biginteger', ['identity' => true, 'signed' => false])
              ->addColumn('title', 'string', ['limit' => 191])
              ->addColumn('description', 'text', ['null' => true])
              ->addColumn('is_active', 'boolean', ['default' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['title'], ['unique' => true, 'name' => 'idx_title'])
              ->create();
    }
}
```

### Example B: Adding Columns / Indexes to Existing Tables
```php
<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTrackingNumberToMessages extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('pcs_contact_messages');
        $table->addColumn('tracking_code', 'string', ['limit' => 64, 'null' => true, 'after' => 'status'])
              ->addIndex(['tracking_code'], ['name' => 'idx_tracking_code'])
              ->update();
    }
}
```

### Example C: Safe Raw SQL (with `hasTable` check)
```php
<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterTableRaw extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("ALTER TABLE `pcs_settings` ADD COLUMN `group_name` VARCHAR(64) DEFAULT 'general';");
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE `pcs_settings` DROP COLUMN `group_name`;");
    }
}
```

---

## 3. Database Seeders (Only when needed)

Seeders should only be created when essential baseline records are required (e.g., initial system config, default roles):

```bash
vendor/bin/phinx seed:create DefaultFeatureSeeder -c database/phinx.php
```

Run seeders:
```bash
vendor/bin/phinx seed:run -c database/phinx.php
```

---

## 4. Running and Testing Migrations

From `pepinieraciumbrudshop/`:

```bash
# Check status of applied vs pending migrations
composer status
# (or: vendor/bin/phinx status -c database/phinx.php)

# Apply all pending migrations
composer migrate
# (or: vendor/bin/phinx migrate -c database/phinx.php)

# Rollback last migration if needed
composer rollback
# (or: vendor/bin/phinx rollback -c database/phinx.php)
```

---

## 5. Automated Deployment Integration

The project's `./deploy/deploy.sh` script automatically runs `vendor/bin/phinx migrate -c database/phinx.php` on every production deploy.
Any new migration committed to Git will be executed automatically without downtime.

