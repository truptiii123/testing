@@ .. @@
     $room_type = $_POST['room_type'];
     $price = $_POST['price'];
     $description = $_POST['description'];
+    $auto_checkout_enabled = isset($_POST['auto_checkout_enabled']) ? 1 : 0;
+    $auto_checkout_notice = $_POST['auto_checkout_notice'] ?: 'Auto Checkout Daily 10am';
     
-    $query = "INSERT INTO rooms (room_number, room_type, price, description, status) VALUES (?, ?, ?, ?, 'available')";
+    $query = "INSERT INTO rooms (room_number, room_type, price, description, status, auto_checkout_enabled, auto_checkout_notice) VALUES (?, ?, ?, ?, 'available', ?, ?)";
     $stmt = mysqli_prepare($conn, $query);
-    mysqli_stmt_bind_param($stmt, "ssds", $room_number, $room_type, $price, $description);
+    mysqli_stmt_bind_param($stmt, "ssdsls", $room_number, $room_type, $price, $description, $auto_checkout_enabled, $auto_checkout_notice);
     
     if (mysqli_stmt_execute($stmt)) {
         $success_message = "Room added successfully!";
@@ .. @@
                     <textarea name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Room description..."></textarea>
                 </div>

+                <!-- Auto Checkout Settings -->
+                <div class="border-t pt-6">
+                    <h3 class="text-lg font-medium text-gray-900 mb-4">Auto Checkout Settings</h3>
+                    
+                    <div class="mb-4">
+                        <label class="flex items-center">
+                            <input type="checkbox" name="auto_checkout_enabled" value="1" checked
+                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
+                            <span class="ml-2 text-sm text-gray-700">Enable Auto Checkout Daily at 10:00 AM</span>
+                        </label>
+                    </div>
+                    
+                    <div>
+                        <label for="auto_checkout_notice" class="block text-sm font-medium text-gray-700">Auto Checkout Notice</label>
+                        <input type="text" name="auto_checkout_notice" id="auto_checkout_notice" 
+                               value="Auto Checkout Daily 10am"
+                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
+                        <p class="mt-1 text-sm text-gray-500">This notice will be displayed to guests in the room</p>
+                    </div>
+                </div>
+
                 <div class="flex justify-end space-x-3">
                     <a href="rooms.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                         Cancel
                     </a>
                     <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                         Add Room
                     </button>
                 </div>
             </form>
         </div>
     </div>
 </body>
 </html>