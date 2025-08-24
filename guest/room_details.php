@@ .. @@
                 <div class="bg-white rounded-lg shadow-md p-6">
                     <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($room['room_type']); ?></h2>
                     <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($room['description']); ?></p>
                     
+                    <!-- Auto Checkout Notice -->
+                    <?php if ($room['auto_checkout_enabled'] && $room['auto_checkout_notice']): ?>
+                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
+                            <div class="flex items-center">
+                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
+                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
+                                </svg>
+                                <span class="text-blue-800 font-medium"><?php echo htmlspecialchars($room['auto_checkout_notice']); ?></span>
+                            </div>
+                        </div>
+                    <?php endif; ?>
+                    
                     <div class="grid grid-cols-2 gap-4 mb-6">
                         <div>
                             <span class="text-gray-500">Room Number:</span>
                             <span class="font-semibold"><?php echo htmlspecialchars($room['room_number']); ?></span>
                         </div>
                         <div>
                             <span class="text-gray-500">Price per night:</span>
                             <span class="font-semibold text-green-600">₹<?php echo number_format($room['price']); ?></span>
                         </div>
                     </div>
                     
                     <?php if ($room['status'] == 'available'): ?>
                         <a href="booking.php?room_id=<?php echo $room['id']; ?>" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-center block">
                             Book Now
                         </a>
                     <?php else: ?>
                         <div class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg text-center">
                             Room Not Available
                         </div>
                     <?php endif; ?>
                 </div>
             </div>
         </div>
     </div>
 </body>
 </html>