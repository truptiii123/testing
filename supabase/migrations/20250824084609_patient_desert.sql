-- Auto Checkout System Database Schema
-- This file contains all necessary database changes for the auto checkout system

-- Create activity_logs table to track auto checkout activities
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_type VARCHAR(50) NOT NULL,
    room_id INT,
    guest_name VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at)
);

-- Create auto_checkout_settings table for configuration
CREATE TABLE IF NOT EXISTS auto_checkout_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checkout_time TIME DEFAULT '10:00:00',
    is_enabled BOOLEAN DEFAULT TRUE,
    last_run_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default auto checkout settings
INSERT INTO auto_checkout_settings (checkout_time, is_enabled) 
VALUES ('10:00:00', TRUE)
ON DUPLICATE KEY UPDATE checkout_time = '10:00:00';

-- Add auto_checkout_enabled column to rooms table if it doesn't exist
ALTER TABLE rooms 
ADD COLUMN IF NOT EXISTS auto_checkout_enabled BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS auto_checkout_notice TEXT DEFAULT 'Auto Checkout Daily 10am';

-- Update existing rooms to have auto checkout enabled
UPDATE rooms SET auto_checkout_enabled = TRUE, auto_checkout_notice = 'Auto Checkout Daily 10am' 
WHERE auto_checkout_enabled IS NULL;

-- Create stored procedure for auto checkout process
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS ProcessAutoCheckout()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE room_id INT;
    DECLARE guest_name VARCHAR(255);
    DECLARE checkout_cursor CURSOR FOR 
        SELECT r.id, b.guest_name 
        FROM rooms r 
        JOIN bookings b ON r.id = b.room_id 
        WHERE r.status = 'occupied' 
        AND r.auto_checkout_enabled = TRUE
        AND b.checkout_date <= CURDATE();
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Update last run date
    UPDATE auto_checkout_settings SET last_run_date = CURDATE();
    
    OPEN checkout_cursor;
    
    checkout_loop: LOOP
        FETCH checkout_cursor INTO room_id, guest_name;
        IF done THEN
            LEAVE checkout_loop;
        END IF;
        
        -- Update room status to available
        UPDATE rooms SET status = 'available' WHERE id = room_id;
        
        -- Update booking status to completed
        UPDATE bookings SET status = 'completed', actual_checkout_date = NOW() 
        WHERE room_id = room_id AND status = 'active';
        
        -- Log the activity
        INSERT INTO activity_logs (activity_type, room_id, guest_name, description)
        VALUES ('auto_checkout', room_id, guest_name, 
                CONCAT('Automatic checkout completed for room ', room_id, ' - Guest: ', guest_name));
        
    END LOOP;
    
    CLOSE checkout_cursor;
END //
DELIMITER ;

-- Create event scheduler for daily auto checkout at 10 AM
SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS daily_auto_checkout;

CREATE EVENT daily_auto_checkout
ON SCHEDULE EVERY 1 DAY
STARTS CONCAT(CURDATE(), ' 10:00:00')
DO
BEGIN
    CALL ProcessAutoCheckout();
END;