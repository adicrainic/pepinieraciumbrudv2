<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: pcs_traffic_hits table for privacy-friendly storefront visitor & pageview tracking.
 */
final class CreatePcsTrafficHitsTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('pcs_traffic_hits')) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS `pcs_traffic_hits` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `visit_date` DATE NOT NULL,
                    `visitor_hash` CHAR(64) NOT NULL,
                    `page_path` VARCHAR(255) NOT NULL DEFAULT '/',
                    `page_title` VARCHAR(255) NOT NULL DEFAULT '',
                    `referrer_source` VARCHAR(100) NOT NULL DEFAULT 'Direct',
                    `referrer_url` VARCHAR(500) NULL,
                    `device_type` VARCHAR(20) NOT NULL DEFAULT 'desktop',
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_visit_date` (`visit_date`),
                    KEY `idx_date_visitor` (`visit_date`, `visitor_hash`),
                    KEY `idx_date_path` (`visit_date`, `page_path`(191)),
                    KEY `idx_date_source` (`visit_date`, `referrer_source`),
                    KEY `idx_date_device` (`visit_date`, `device_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `pcs_traffic_hits`;");
    }
}
