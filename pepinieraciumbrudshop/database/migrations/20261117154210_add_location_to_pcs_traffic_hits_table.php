<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Add visitor location columns (country, county/region, city) to pcs_traffic_hits.
 */
final class AddLocationToPcsTrafficHitsTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('pcs_traffic_hits')) {
            $table = $this->table('pcs_traffic_hits');
            if (!$table->hasColumn('country_code')) {
                $this->execute("
                    ALTER TABLE `pcs_traffic_hits`
                        ADD COLUMN `country_code` VARCHAR(10) NOT NULL DEFAULT '' AFTER `device_type`,
                        ADD COLUMN `country_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `country_code`,
                        ADD COLUMN `region_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `country_name`,
                        ADD COLUMN `city_name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `region_name`,
                        ADD KEY `idx_date_location` (`visit_date`, `country_code`, `region_name`, `city_name`);
                ");
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('pcs_traffic_hits')) {
            $table = $this->table('pcs_traffic_hits');
            if ($table->hasColumn('country_code')) {
                $this->execute("
                    ALTER TABLE `pcs_traffic_hits`
                        DROP KEY `idx_date_location`,
                        DROP COLUMN `city_name`,
                        DROP COLUMN `region_name`,
                        DROP COLUMN `country_name`,
                        DROP COLUMN `country_code`;
                ");
            }
        }
    }
}
