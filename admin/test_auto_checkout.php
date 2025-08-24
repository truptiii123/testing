<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Auto Checkout - Hotel Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Test Auto Checkout System</h1>
                
                <?php
                if (isset($_POST['test_checkout'])) {
                    echo "<div class='mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg'>";
                    echo "<h3 class='font-semibold text-blue-800 mb-2'>Running Auto Checkout Test...</h3>";
                    
                    $result = processAutoCheckout($conn);
                    
                    if ($result['success']) {
                        echo "<div class='p-3 bg-green-100 border border-green-300 rounded text-green-800'>";
                        echo "<strong>Success!</strong> " . htmlspecialchars($result['message']);
                        echo "</div>";
                    } else {
                        echo "<div class='p-3 bg-red-100 border border-red-300 rounded text-red-800'>";
                        echo "<strong>Error!</strong> " . htmlspecialchars($result['error']);
                        echo "</div>";
                    }
                    echo "</div>";
                }
                ?>
                
                <!-- Current Settings -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Current Auto Checkout Settings</h2>
                    <?php
                    $settings = getAutoCheckoutSettings($conn);
                    ?>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <span class="font-semibold <?php echo $settings['is_enabled'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $settings['is_enabled'] ? 'Enabled' : 'Disabled'; ?>
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-600">Checkout Time:</span>
                                <span class="font-semibold"><?php echo date('g:i A', strtotime($settings['checkout_time'])); ?></span>
                            </div>
                            <div>
                                <span class="text-gray-600">Last Run:</span>
                                <span class="font-semibold">
                                    <?php echo $settings['last_run_date'] ? date('M j, Y', strtotime($settings['last_run_date'])) : 'Never'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rooms Status -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Rooms Eligible for Auto Checkout</h2>
                    <?php
                    $eligible_query = "SELECT r.id, r.room_number, r.room_type, b.guest_name, b.checkout_date, r.auto_checkout_enabled
                                      FROM rooms r 
                                      LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'active'
                                      WHERE r.status = 'occupied' 
                                      ORDER BY r.room_number";
                    $eligible_result = mysqli_query($conn, $eligible_query);
                    ?>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 px-4 py-2 text-left">Room</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Type</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Guest</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Checkout Date</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Auto Checkout</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($eligible_result) > 0): ?>
                                    <?php while ($room = mysqli_fetch_assoc($eligible_result)): ?>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($room['room_number']); ?></td>
                                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($room['room_type']); ?></td>
                                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($room['guest_name'] ?: 'N/A'); ?></td>
                                            <td class="border border-gray-300 px-4 py-2">
                                                <?php if ($room['checkout_date']): ?>
                                                    <?php 
                                                    $checkout_date = strtotime($room['checkout_date']);
                                                    $today = strtotime(date('Y-m-d'));
                                                    $is_overdue = $checkout_date <= $today;
                                                    ?>
                                                    <span class="<?php echo $is_overdue ? 'text-red-600 font-semibold' : 'text-gray-800'; ?>">
                                                        <?php echo date('M j, Y', $checkout_date); ?>
                                                        <?php if ($is_overdue): ?>
                                                            (Overdue)
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2">
                                                <span class="<?php echo $room['auto_checkout_enabled'] ? 'text-green-600' : 'text-red-600'; ?>">
                                                    <?php echo $room['auto_checkout_enabled'] ? 'Enabled' : 'Disabled'; ?>
                                                </span>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2">
                                                <?php
                                                $will_checkout = $room['auto_checkout_enabled'] && 
                                                               $room['checkout_date'] && 
                                                               strtotime($room['checkout_date']) <= strtotime(date('Y-m-d'));
                                                ?>
                                                <span class="px-2 py-1 text-xs rounded-full <?php echo $will_checkout ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                    <?php echo $will_checkout ? 'Will Auto Checkout' : 'No Action'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                            No occupied rooms found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Test Button -->
                <div class="mb-6">
                    <form method="POST" class="inline">
                        <button type="submit" name="test_checkout" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('This will process auto checkout for all eligible rooms. Continue?')">
                            Run Auto Checkout Test
                        </button>
                    </form>
                    <p class="text-sm text-gray-600 mt-2">
                        This will simulate the daily auto checkout process and show you the results.
                    </p>
                </div>
                
                <!-- Recent Activities -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Auto Checkout Activities</h2>
                    <?php
                    $recent_activities = getRecentAutoCheckoutActivities($conn, 5);
                    ?>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 px-4 py-2 text-left">Date & Time</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Activity</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_activities) > 0): ?>
                                    <?php while ($activity = mysqli_fetch_assoc($recent_activities)): ?>
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2 text-sm">
                                                <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                    <?php echo $activity['activity_type'] == 'auto_checkout' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                    <?php echo ucwords(str_replace('_', ' ', $activity['activity_type'])); ?>
                                                </span>
                                            </td>
                                            <td class="border border-gray-300 px-4 py-2 text-sm">
                                                <?php echo htmlspecialchars($activity['description']); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
                                            No activities yet
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex justify-between">
                    <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                        Back to Dashboard
                    </a>
                    <a href="../includes/auto_checkout_cron.php" target="_blank" 
                       class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Test Cron URL
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>