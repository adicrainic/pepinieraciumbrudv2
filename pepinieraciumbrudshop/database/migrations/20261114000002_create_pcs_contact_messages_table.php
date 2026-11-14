<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Baseline migration: pcs_contact_messages
 */
final class CreatePcsContactMessagesTable extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('pcs_contact_messages')) {
            $this->execute("
                CREATE TABLE IF NOT EXISTS `pcs_contact_messages` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(191) NOT NULL,
                    `email` VARCHAR(191) NOT NULL,
                    `phone` VARCHAR(64) NOT NULL DEFAULT '',
                    `message` LONGTEXT NOT NULL,
                    `status` VARCHAR(32) NOT NULL DEFAULT 'unread',
                    `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
                    `ip_address` VARCHAR(45) NULL,
                    `user_agent` VARCHAR(255) NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `read_at` DATETIME NULL,
                    `reply_message` LONGTEXT NULL,
                    `replied_at` DATETIME NULL,
                    `replied_by` VARCHAR(191) NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_status` (`status`),
                    KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `pcs_contact_messages`;");
    }
}

