@@ .. @@
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Number</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
+                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Auto Checkout</th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                     </tr>
                 </thead>
@@ .. @@
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹<?php echo number_format($room['price']); ?></td>
                             <td class="px-6 py-4 whitespace-nowrap">
                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                     <?php echo $room['status'] == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                     <?php echo ucfirst($room['status']); ?>
                                 </span>
                             </td>
+                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
+                                <div class="flex flex-col">
+                                    <span class="<?php echo $room['auto_checkout_enabled'] ? 'text-green-600' : 'text-red-600'; ?>">
+                                        <?php echo $room['auto_checkout_enabled'] ? 'Enabled' : 'Disabled'; ?>
+                                    </span>
+                                    <?php if ($room['auto_checkout_enabled'] && $room['auto_checkout_notice']): ?>
+                                        <span class="text-xs text-gray-500 mt-1">
+                                            <?php echo htmlspecialchars($room['auto_checkout_notice']); ?>
+                                        </span>
+                                    <?php endif; ?>
+                                </div>
+                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                 <a href="edit_room.php?id=<?php echo $room['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                 <a href="delete_room.php?id=<?php echo $room['id']; ?>" class="text-red-600 hover:text-red-900" 
                                    onclick="return confirm('Are you sure you want to delete this room?')">Delete</a>
                             </td>
                         </tr>
                     <?php endwhile; ?>
                 </tbody>
             </table>
         </div>
+        
+        <!-- Auto Checkout Notice -->
+        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
+            <div class="flex items-center">
+                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
+                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
+                </svg>
+                <div>
+                    <h3 class="text-sm font-medium text-blue-800">Auto Checkout Information</h3>
+                    <p class="text-sm text-blue-700 mt-1">
+                        Rooms with auto checkout enabled will automatically check out guests daily at 10:00 AM. 
+                        The notice "Auto Checkout Daily 10am" will be displayed to guests in their rooms.
+                    </p>
+                </div>
+            </div>
+        </div>
     </div>
 </body>
 </html>