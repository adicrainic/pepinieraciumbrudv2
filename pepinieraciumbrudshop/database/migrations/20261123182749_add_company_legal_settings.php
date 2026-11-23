<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration: Seed company legal identity and phytosanitary authorization settings
 * into pcs_settings and ensure business domain email.
 */
final class AddCompanyLegalSettings extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('pcs_settings')) {
            $defaults = [
                'company_legal_name'    => 'S.C. Pepiniera Ciumbrud S.R.L.',
                'company_cui'           => 'RO12345678',
                'company_reg_com'       => 'J01/123/2000',
                'company_authorization' => 'AB 1234 / 0000',
            ];

            foreach ($defaults as $key => $value) {
                $escapedKey   = addslashes($key);
                $escapedValue = addslashes($value);
                $this->execute("
                    INSERT INTO `pcs_settings` (`setting_key`, `setting_value`)
                    VALUES ('{$escapedKey}', '{$escapedValue}')
                    ON DUPLICATE KEY UPDATE `setting_value` = IF(`setting_value` = '' OR `setting_value` IS NULL, VALUES(`setting_value`), `setting_value`);
                ");
            }

            // Replace any personal @gmail.com contact email with business domain email
            $this->execute("
                UPDATE `pcs_settings`
                SET `setting_value` = 'contact@pepinieraciumbrud.ro'
                WHERE `setting_key` = 'contact_email'
                  AND (`setting_value` LIKE '%@gmail.com' OR `setting_value` = '' OR `setting_value` IS NULL);
            ");
        }
    }

    public function down(): void
    {
        if ($this->hasTable('pcs_settings')) {
            $this->execute("
                DELETE FROM `pcs_settings`
                WHERE `setting_key` IN (
                    'company_legal_name',
                    'company_cui',
                    'company_reg_com',
                    'company_authorization'
                );
            ");
        }
    }
}
