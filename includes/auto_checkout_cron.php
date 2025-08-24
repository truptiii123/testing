<?php
/**
 * Auto Checkout Cron Job
 * This file should be called by a cron job daily at 10 AM
 * Cron job command: 0 10 * * * /usr/bin/php /path/to/your/project/includes/auto_checkout_cron.php
 */

require_once 'config.php';
require_once 'functions.php';

// Check if this is being run from command line or web
$is_cli = php_sapi_name() === 'cli';

// Execute auto checkout
$result = processAutoCheckout($conn);

if ($result['success']) {
    $message = "Auto checkout process completed. Processed: " . $result['processed'] . " rooms.";
    if ($is_cli) {
        echo $message . "\n";
    } else {
        echo "<h2>Auto Checkout Process</h2>";
        echo "<p style='color: green;'>" . htmlspecialchars($message) . "</p>";
        echo "<p><a href='../admin/dashboard.php'>Back to Dashboard</a></p>";
    }
} else {
    $error_message = "Error during auto checkout: " . $result['error'];
    if ($is_cli) {
        echo $error_message . "\n";
    } else {
        echo "<h2>Auto Checkout Process</h2>";
        echo "<p style='color: red;'>" . htmlspecialchars($error_message) . "</p>";
        echo "<p><a href='../admin/dashboard.php'>Back to Dashboard</a></p>";
    }
    error_log($error_message);
}

mysqli_close($conn);
?>