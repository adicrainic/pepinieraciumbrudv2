<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Configure WooCommerce flat-rate shipping (30 lei) for Romania
 * and add shipping_cost setting to pcs_settings.
 */
final class ConfigureFlatRateShipping extends AbstractMigration
{
    public function up(): void
    {
        // 1. Seed default shipping_cost in pcs_settings (30 lei)
        if ($this->hasTable('pcs_settings')) {
            $this->execute("
                INSERT INTO `pcs_settings` (`setting_key`, `setting_value`)
                VALUES ('shipping_cost', '30')
                ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;
            ");
        }

        // 2. Ensure Romania shipping zone (zone_id = 1) exists
        if ($this->hasTable('pcs_woocommerce_shipping_zones')) {
            $this->execute("
                INSERT IGNORE INTO `pcs_woocommerce_shipping_zones` (`zone_id`, `zone_name`, `zone_order`)
                VALUES (1, 'Romania', 0);
            ");
        }

        if ($this->hasTable('pcs_woocommerce_shipping_zone_locations')) {
            $this->execute("
                INSERT IGNORE INTO `pcs_woocommerce_shipping_zone_locations` (`location_id`, `zone_id`, `location_code`, `location_type`)
                VALUES (1, 1, 'RO', 'country');
            ");
        }

        // 3. Configure shipping methods in zone 1:
        //    - Disable free_shipping (instance_id = 1)
        //    - Enable flat_rate (instance_id = 2)
        if ($this->hasTable('pcs_woocommerce_shipping_zone_methods')) {
            $this->execute("
                UPDATE `pcs_woocommerce_shipping_zone_methods`
                SET `is_enabled` = 0
                WHERE `method_id` = 'free_shipping';
            ");

            $existingFlatRate = $this->fetchRow("
                SELECT `instance_id` FROM `pcs_woocommerce_shipping_zone_methods`
                WHERE `zone_id` = 1 AND `method_id` = 'flat_rate'
                LIMIT 1
            ");

            if ($existingFlatRate) {
                $instanceId = (int) $existingFlatRate['instance_id'];
                $this->execute("
                    UPDATE `pcs_woocommerce_shipping_zone_methods`
                    SET `is_enabled` = 1, `method_order` = 1
                    WHERE `instance_id` = {$instanceId};
                ");
            } else {
                $this->execute("
                    INSERT INTO `pcs_woocommerce_shipping_zone_methods` (`zone_id`, `instance_id`, `method_id`, `method_order`, `is_enabled`)
                    VALUES (1, 2, 'flat_rate', 1, 1)
                    ON DUPLICATE KEY UPDATE `method_id` = 'flat_rate', `is_enabled` = 1, `method_order` = 1;
                ");
                $instanceId = 2;
            }

            // 4. Save WooCommerce flat_rate instance settings option and clear shipping transients
            if ($this->hasTable('pcs_options')) {
                $settingsSerialized = serialize([
                    'title'      => 'Livrare curier rapid',
                    'tax_status' => 'none',
                    'cost'       => '30',
                ]);
                $escapedSettings = addslashes($settingsSerialized);
                $optionName = "woocommerce_flat_rate_{$instanceId}_settings";

                $this->execute("
                    INSERT INTO `pcs_options` (`option_name`, `option_value`, `autoload`)
                    VALUES ('{$optionName}', '{$escapedSettings}', 'yes')
                    ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`);
                ");

                // Ensure shipping calculation is enabled without requiring full address upfront
                $this->execute("
                    INSERT INTO `pcs_options` (`option_name`, `option_value`, `autoload`)
                    VALUES ('woocommerce_calc_shipping', 'yes', 'yes')
                    ON DUPLICATE KEY UPDATE `option_value` = 'yes';
                ");
                $this->execute("
                    INSERT INTO `pcs_options` (`option_name`, `option_value`, `autoload`)
                    VALUES ('woocommerce_shipping_cost_requires_address', 'no', 'yes')
                    ON DUPLICATE KEY UPDATE `option_value` = 'no';
                ");

                // Clear cached WooCommerce shipping transients
                $this->execute("
                    DELETE FROM `pcs_options`
                    WHERE `option_name` LIKE '_transient_wc_ship%'
                       OR `option_name` LIKE '_transient_timeout_wc_ship%'
                       OR `option_name` = 'woocommerce_shipping_zones_cache';
                ");
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('pcs_woocommerce_shipping_zone_methods')) {
            $this->execute("
                DELETE FROM `pcs_woocommerce_shipping_zone_methods`
                WHERE `zone_id` = 1 AND `method_id` = 'flat_rate';
            ");
            $this->execute("
                UPDATE `pcs_woocommerce_shipping_zone_methods`
                SET `is_enabled` = 1
                WHERE `zone_id` = 1 AND `method_id` = 'free_shipping';
            ");
        }

        if ($this->hasTable('pcs_settings')) {
            $this->execute("
                DELETE FROM `pcs_settings` WHERE `setting_key` = 'shipping_cost';
            ");
        }
    }
}
