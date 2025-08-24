<?php
/**
 * Common functions for the hotel management system
 */

/**
 * Process automatic checkout for rooms
 * This function should be called by cron job daily
 */
function processAutoCheckout($conn) {
    try {
        // Get all occupied rooms that should be checked out
        $query = "SELECT r.id as room_id, r.room_number, b.id as booking_id, b.guest_name, b.checkout_date
                 FROM rooms r 
                 JOIN bookings b ON r.id = b.room_id 
                 WHERE r.status = 'occupied' 
                 AND r.auto_checkout_enabled = 1
                 AND b.status = 'active'
                 AND b.checkout_date <= CURDATE()";
        
        $result = mysqli_query($conn, $query);
        $processed_count = 0;
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Update room status to available
                $update_room = "UPDATE rooms SET status = 'available' WHERE id = " . intval($row['room_id']);
                mysqli_query($conn, $update_room);
                
                // Update booking status to completed
                $update_booking = "UPDATE bookings SET status = 'completed', actual_checkout_date = NOW() 
                                 WHERE id = " . intval($row['booking_id']);
                mysqli_query($conn, $update_booking);
                
                // Log the activity
                $guest_name = mysqli_real_escape_string($conn, $row['guest_name']);
                $room_number = mysqli_real_escape_string($conn, $row['room_number']);
                $description = "Automatic checkout completed for room " . $room_number . " - Guest: " . $guest_name;
                
                $log_activity = "INSERT INTO activity_logs (activity_type, room_id, guest_name, description) 
                               VALUES ('auto_checkout', " . intval($row['room_id']) . ", '" . $guest_name . "', '" . 
                               mysqli_real_escape_string($conn, $description) . "')";
                mysqli_query($conn, $log_activity);
                
                $processed_count++;
            }
        }
        
        // Update last run date
        $update_settings = "UPDATE auto_checkout_settings SET last_run_date = CURDATE() WHERE id = 1";
        mysqli_query($conn, $update_settings);
        
        // Log the overall process
        $overall_description = "Auto checkout process completed. Processed " . $processed_count . " rooms.";
        $overall_log = "INSERT INTO activity_logs (activity_type, description) 
                       VALUES ('auto_checkout_process', '" . mysqli_real_escape_string($conn, $overall_description) . "')";
        mysqli_query($conn, $overall_log);
        
        return array('success' => true, 'processed' => $processed_count, 'message' => $overall_description);
        
    } catch (Exception $e) {
        error_log("Auto checkout error: " . $e->getMessage());
        return array('success' => false, 'error' => $e->getMessage());
    }
}

/**
 * Get recent auto checkout activities
 */
function getRecentAutoCheckoutActivities($conn, $limit = 10) {
    $query = "SELECT * FROM activity_logs 
              WHERE activity_type IN ('auto_checkout', 'auto_checkout_process') 
              ORDER BY created_at DESC 
              LIMIT " . intval($limit);
    
    return mysqli_query($conn, $query);
}

/**
 * Get auto checkout settings
 */
function getAutoCheckoutSettings($conn) {
    $query = "SELECT * FROM auto_checkout_settings LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    // Return default settings if none exist
    return array(
        'checkout_time' => '10:00:00',
        'is_enabled' => 1,
        'last_run_date' => null
    );
}

/**
 * Update auto checkout settings
 */
function updateAutoCheckoutSettings($conn, $checkout_time, $is_enabled) {
    $checkout_time = mysqli_real_escape_string($conn, $checkout_time);
    $is_enabled = intval($is_enabled);
    
    $query = "UPDATE auto_checkout_settings 
              SET checkout_time = '$checkout_time', is_enabled = $is_enabled 
              WHERE id = 1";
    
    return mysqli_query($conn, $query);
}
?>