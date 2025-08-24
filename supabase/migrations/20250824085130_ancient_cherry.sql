-- Auto Checkout System Database Schema for Hostinger/Shared Hosting
-- Compatible with shared hosting environments that don't support Event Scheduler

-- Create activity_logs table to track auto checkout activities
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `activity_type` varchar(50) NOT NULL,
    `room_id` int(11) DEFAULT NULL,
    `guest_name` varchar(255) DEFAULT NULL,
    `description` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activity_type` (`activity_type`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create auto_checkout_settings table for configuration
CREATE TABLE IF NOT EXISTS `auto_checkout_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `checkout_time` time NOT NULL DEFAULT '10:00:00',
    `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `last_run_date` date DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default auto checkout settings
INSERT INTO `auto_checkout_settings` (`checkout_time`, `is_enabled`) 
VALUES ('10:00:00', 1)
ON DUPLICATE KEY UPDATE `checkout_time` = '10:00:00';

-- Check if auto_checkout_enabled column exists in rooms table, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'rooms' 
     AND COLUMN_NAME = 'auto_checkout_enabled') > 0,
    'SELECT "Column auto_checkout_enabled already exists"',
    'ALTER TABLE `rooms` ADD COLUMN `auto_checkout_enabled` tinyint(1) NOT NULL DEFAULT 1'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if auto_checkout_notice column exists in rooms table, if not add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'rooms' 
     AND COLUMN_NAME = 'auto_checkout_notice') > 0,
    'SELECT "Column auto_checkout_notice already exists"',
    'ALTER TABLE `rooms` ADD COLUMN `auto_checkout_notice` text DEFAULT "Auto Checkout Daily 10am"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing rooms to have auto checkout enabled if the columns were just added
UPDATE `rooms` 
SET `auto_checkout_enabled` = 1, `auto_checkout_notice` = 'Auto Checkout Daily 10am' 
WHERE `auto_checkout_enabled` IS NULL OR `auto_checkout_notice` IS NULL;

-- Insert some sample activity log entries (optional)
INSERT INTO `activity_logs` (`activity_type`, `description`) 
VALUES ('system', 'Auto checkout system initialized successfully');