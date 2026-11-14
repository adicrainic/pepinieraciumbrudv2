<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Baseline migration: pcs_settings
 */
final class CreatePcsSettingsTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('pcs_settings')) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS `pcs_settings` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `setting_key` VARCHAR(191) NOT NULL,
                    `setting_value` LONGTEXT NULL,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_setting_key` (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `pcs_settings`;");
    }
}

