<?php
/**
 * Auto Checkout Cron Job
 * This file should be called by a cron job daily at 10 AM
 * Cron job command: 0 10 * * * /usr/bin/php /path/to/your/project/includes/auto_checkout_cron.php
 */

require_once 'config.php';

class AutoCheckoutSystem {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function processAutoCheckout() {
        try {
            // Get all occupied rooms that should be checked out
            $query = "SELECT r.id as room_id, r.room_number, b.id as booking_id, b.guest_name, b.checkout_date
                     FROM rooms r 
                     JOIN bookings b ON r.id = b.room_id 
                     WHERE r.status = 'occupied' 
                     AND r.auto_checkout_enabled = TRUE
                     AND b.status = 'active'
                     AND b.checkout_date <= CURDATE()";
            
            $result = mysqli_query($this->conn, $query);
            $processed_count = 0;
            
            while ($row = mysqli_fetch_assoc($result)) {
                // Update room status to available
                $update_room = "UPDATE rooms SET status = 'available' WHERE id = " . $row['room_id'];
                mysqli_query($this->conn, $update_room);
                
                // Update booking status to completed
                $update_booking = "UPDATE bookings SET status = 'completed', actual_checkout_date = NOW() 
                                 WHERE id = " . $row['booking_id'];
                mysqli_query($this->conn, $update_booking);
                
                // Log the activity
                $log_activity = "INSERT INTO activity_logs (activity_type, room_id, guest_name, description) 
                               VALUES ('auto_checkout', " . $row['room_id'] . ", '" . 
                               mysqli_real_escape_string($this->conn, $row['guest_name']) . "', 
                               'Automatic checkout completed for room " . $row['room_number'] . " - Guest: " . 
                               mysqli_real_escape_string($this->conn, $row['guest_name']) . "')";
                mysqli_query($this->conn, $log_activity);
                
                $processed_count++;
            }
            
            // Update last run date
            $update_settings = "UPDATE auto_checkout_settings SET last_run_date = CURDATE()";
            mysqli_query($this->conn, $update_settings);
            
            // Log the overall process
            $overall_log = "INSERT INTO activity_logs (activity_type, description) 
                           VALUES ('auto_checkout_process', 'Auto checkout process completed. Processed " . 
                           $processed_count . " rooms.')";
            mysqli_query($this->conn, $overall_log);
            
            echo "Auto checkout process completed. Processed: " . $processed_count . " rooms.\n";
            
        } catch (Exception $e) {
            error_log("Auto checkout error: " . $e->getMessage());
            echo "Error during auto checkout: " . $e->getMessage() . "\n";
        }
    }
}

// Execute auto checkout
$autoCheckout = new AutoCheckoutSystem($conn);
$autoCheckout->processAutoCheckout();

mysqli_close($conn);
?>