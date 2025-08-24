@@ .. @@
                         <div class="bg-white rounded-lg shadow-md overflow-hidden">
                             <div class="p-6">
                                 <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($room['room_type']); ?></h3>
                                 <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($room['description'], 0, 100)) . '...'; ?></p>
                                 
+                                <!-- Auto Checkout Notice Box -->
+                                <?php if ($room['auto_checkout_enabled'] && $room['auto_checkout_notice']): ?>
+                                    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
+                                        <div class="flex items-center text-sm">
+                                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
+                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
+                                            </svg>
+                                            <span class="text-blue-800 font-medium"><?php echo htmlspecialchars($room['auto_checkout_notice']); ?></span>
+                                        </div>
+                                    </div>
+                                <?php endif; ?>
+                                
                                 <div class="flex justify-between items-center mb-4">
                                     <span class="text-2xl font-bold text-green-600">₹<?php echo number_format($room['price']); ?></span>
                                     <span class="text-sm text-gray-500">per night</span>
                                 </div>
                                 
                                 <div class="flex justify-between items-center">
                                     <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                         <?php echo $room['status'] == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                         <?php echo ucfirst($room['status']); ?>
                                     </span>
                                     
                                     <?php if ($room['status'] == 'available'): ?>
                                         <a href="room_details.php?id=<?php echo $room['id']; ?>" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                             View Details
                                         </a>
                                     <?php else: ?>
                                         <span class="bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed">
                                             Not Available
                                         </span>
                                     <?php endif; ?>
                                 </div>
                             </div>
                         </div>
                     <?php endwhile; ?>
                 </div>
             <?php else: ?>
                 <div class="text-center py-8">
                     <p class="text-gray-500">No rooms available at the moment.</p>
                 </div>
             <?php endif; ?>
         </div>
     </div>
 </body>
 </html>