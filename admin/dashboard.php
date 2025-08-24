@@ .. @@
 <?php
 session_start();
 require_once '../includes/config.php';
+require_once '../includes/functions.php';
 
 // Check if admin is logged in
 if (!isset($_SESSION['admin_logged_in'])) {
@@ .. @@
 
 // Get dashboard statistics
 $total_rooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM rooms"))['count'];
 $occupied_rooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM rooms WHERE status = 'occupied'"))['count'];
 $available_rooms = $total_rooms - $occupied_rooms;
+
+// Get recent auto checkout activities
+$recent_activities = mysqli_query($conn, "SELECT * FROM activity_logs WHERE activity_type IN ('auto_checkout', 'auto_checkout_process') ORDER BY created_at DESC LIMIT 10");
+
+// Get auto checkout settings
+$auto_checkout_settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM auto_checkout_settings LIMIT 1"));
 ?>
 
 <!DOCTYPE html>
@@ .. @@
                     <p class="text-gray-600">Available Rooms</p>
                 </div>
             </div>
+            
+            <!-- Auto Checkout Status Card -->
+            <div class="bg-white rounded-lg shadow-md p-6">
+                <div class="flex items-center">
+                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
+                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
+                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
+                        </svg>
+                    </div>
+                    <div class="ml-4">
+                        <h3 class="text-lg font-semibold text-gray-800">Auto Checkout</h3>
+                        <p class="text-gray-600">
+                            <?php echo $auto_checkout_settings['is_enabled'] ? 'Enabled' : 'Disabled'; ?> - 
+                            Daily at <?php echo date('g:i A', strtotime($auto_checkout_settings['checkout_time'])); ?>
+                        </p>
+                        <?php if ($auto_checkout_settings['last_run_date']): ?>
+                            <p class="text-sm text-gray-500">Last run: <?php echo date('M j, Y', strtotime($auto_checkout_settings['last_run_date'])); ?></p>
+                        <?php endif; ?>
+                    </div>
+                </div>
+            </div>
         </div>
+        
+        <!-- Recent Auto Checkout Activities -->
+        <div class="bg-white rounded-lg shadow-md p-6 mt-8">
+            <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Auto Checkout Activities</h2>
+            <div class="overflow-x-auto">
+                <table class="min-w-full table-auto">
+                    <thead>
+                        <tr class="bg-gray-50">
+                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Date & Time</th>
+                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Activity</th>
+                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Room</th>
+                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Guest</th>
+                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Description</th>
+                        </tr>
+                    </thead>
+                    <tbody>
+                        <?php if (mysqli_num_rows($recent_activities) > 0): ?>
+                            <?php while ($activity = mysqli_fetch_assoc($recent_activities)): ?>
+                                <tr class="border-b border-gray-200">
+                                    <td class="px-4 py-2 text-sm text-gray-600">
+                                        <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
+                                    </td>
+                                    <td class="px-4 py-2">
+                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
+                                            <?php echo $activity['activity_type'] == 'auto_checkout' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
+                                            <?php echo ucwords(str_replace('_', ' ', $activity['activity_type'])); ?>
+                                        </span>
+                                    </td>
+                                    <td class="px-4 py-2 text-sm text-gray-600">
+                                        <?php echo $activity['room_id'] ? 'Room ' . $activity['room_id'] : '-'; ?>
+                                    </td>
+                                    <td class="px-4 py-2 text-sm text-gray-600">
+                                        <?php echo $activity['guest_name'] ?: '-'; ?>
+                                    </td>
+                                    <td class="px-4 py-2 text-sm text-gray-600">
+                                        <?php echo htmlspecialchars($activity['description']); ?>
+                                    </td>
+                                </tr>
+                            <?php endwhile; ?>
+                        <?php else: ?>
+                            <tr>
+                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
+                                    No auto checkout activities yet
+                                </td>
+                            </tr>
+                        <?php endif; ?>
+                    </tbody>
+                </table>
+            </div>
+        </div>
     </div>
 </body>
 </html>