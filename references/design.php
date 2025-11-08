<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unitly Tenant - My Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans">

<style>
body {
  box-sizing: border-box;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { transform: translateX(-100%); }
    to { transform: translateX(0); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.fade-in {
    animation: fadeIn 0.6s ease-out;
}

.slide-in {
    animation: slideIn 0.5s ease-out;
}

.property-card {
    transition: all 0.3s ease;
}

.property-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.action-btn {
    transition: all 0.2s ease;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 1rem;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
    animation: fadeIn 0.3s ease-out;
    max-height: 90vh;
    overflow-y: auto;
}

/* File Upload Styles */
.file-upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
}

.file-upload-area.dragover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

/* Receipt Gallery */
.receipt-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.receipt-item {
    position: relative;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.receipt-item:hover {
    transform: scale(1.05);
}

/* Maintenance Request Status */
.status-pending { 
    background-color: #fef3c7; 
    color: #92400e; 
}

.status-in-progress { 
    background-color: #dbeafe; 
    color: #1e40af; 
}

.status-completed { 
    background-color: #dcfce7; 
    color: #166534; 
}

.status-urgent { 
    background-color: #fecaca; 
    color: #991b1b; 
}

/* Property Info Cards */
.info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(30px, -30px);
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 2rem;
    right: 2rem;
    background-color: white;
    border-radius: 0.5rem;
    padding: 1rem 1.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #10b981;
    z-index: 1001;
    animation: slideIn 0.3s ease-out;
    max-width: 400px;
}

.notification.error {
    border-left-color: #ef4444;
}

.notification.warning {
    border-left-color: #f59e0b;
}

/* Responsive Design */
@media (max-width: 768px) {
    .modal-content {
        margin: 10% auto;
        width: 95%;
        padding: 1.5rem;
    }
    
    .receipt-gallery {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid #f3f4f6;
    border-radius: 50%;
    border-top-color: #3b82f6;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Interactive Elements */
.interactive-hover:hover {
    background-color: #f8fafc;
    cursor: pointer;
}

/* Form Enhancements */
input:focus, select:focus, textarea:focus {
    outline: none;
    ring: 2px;
    ring-color: #3b82f6;
    border-color: transparent;
}

/* Button Variants */
.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background-color: #e2e8f0;
}

.btn-success {
    background-color: #10b981;
    color: white;
}

.btn-success:hover {
    background-color: #059669;
}

.btn-warning {
    background-color: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background-color: #d97706;
}
</style>

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly Tenant</h1>
                    <p class="text-xs text-slate-500">My Dashboard</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button class="relative p-2 text-slate-600 hover:text-blue-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 1 0-15 0v5h5l-5 5-5-5h5V7a12 12 0 1 1 24 0v10z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full"></span>
                </button>
                <div class="flex items-center space-x-2">
                    <span class="text-slate-700 text-sm">Alice Brown</span>
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-semibold">
                        AB
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-8">
         <div>
                <label for="receipt" class="block text-sm font-medium text-slate-700 mb-1">Upload Receipt</label>
                <input type="file" name="receipt" id="receipt" accept=".jpg,.jpeg,.png,.pdf" required
                       class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Monthly Rent</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">$1,200</p>
                        <p class="text-xs text-green-600 mt-1">Due: Dec 1st</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>
</div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Payment Status</p>
                        <p class="text-xl font-bold text-green-600 mt-1">Paid</p>
                        <p class="text-xs text-slate-600 mt-1">Last: Nov 28, 2024</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Maintenance Requests</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">2</p>
                        <p class="text-xs text-orange-600 mt-1">1 pending</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Lease Expires</p>
                        <p class="text-xl font-bold text-slate-800 mt-1">8 months</p>
                        <p class="text-xs text-slate-600 mt-1">Aug 15, 2025</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6"><!-- Next Payment Due --> <!--?php if ($nextPayment): ?-->
   <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-4">
     <h3 class="text-xl font-semibold text-slate-800">Next Payment Due</h3>
     <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
      <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
      </svg>
     </div>
    </div>
    <div class="space-y-3">
     <div class="flex justify-between items-center py-2 border-b border-slate-100"><span class="text-slate-600 text-sm">Unit</span> <span class="font-semibold text-slate-800"><!--?= htmlspecialchars($nextPayment['unit_name']) ?--></span>
     </div>
     <div class="flex justify-between items-center py-2 border-b border-slate-100"><span class="text-slate-600 text-sm">Amount Due</span> <span class="font-bold text-lg text-slate-800">₱<!--?= number_format($nextPayment['balance'], 2) ?--></span>
     </div>
     <div class="flex justify-between items-center py-2 border-b border-slate-100"><span class="text-slate-600 text-sm">Due Date</span> <span class="font-semibold text-slate-800"><!--?= htmlspecialchars($nextPayment['lease_end_date']) ?--></span>
     </div>
    </div>
    <div class="mt-6"><!--?php if ($nextPayment['balance'] --> 0): ?&gt; <a href="../makePayment.php?lease_id=<?= $nextPayment['lease_id'] ?>" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg><span>Pay Now</span> </a>
    </div><a href="../makePayment.php?lease_id=<?= $nextPayment['lease_id'] ?>" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2"> <!--?php else: ?-->
     <div class="w-full bg-green-50 border border-green-200 text-green-700 font-semibold py-3 px-4 rounded-lg text-center flex items-center justify-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg><span>Paid in Full</span>
     </div><!--?php endif; ?--> </a>
   </section>
  </div><a href="../makePayment.php?lease_id=<?= $nextPayment['lease_id'] ?>" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2"> <!--?php endif; ?--> <!-- Active Leases -->
   <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-4">
     <h3 class="text-xl font-semibold text-slate-800">My Active Leases</h3>
     <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
      <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewbox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
      </svg>
     </div>
    </div><!--?php if ($leases): ?-->
    <div class="overflow-x-auto">
     <table class="w-full text-sm">
      <thead>
       <tr class="border-b border-slate-200">
        <th class="text-left py-3 px-2 font-semibold text-slate-700">Unit</th>
        <th class="text-left py-3 px-2 font-semibold text-slate-700">Start</th>
        <th class="text-left py-3 px-2 font-semibold text-slate-700">End</th>
        <th class="text-left py-3 px-2 font-semibold text-slate-700">Balance</th>
        <th class="text-left py-3 px-2 font-semibold text-slate-700">Status</th>
       </tr>
      </thead>
      <tbody><!--?php foreach ($leases as $lease): ?-->
       <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
        <td class="py-3 px-2 font-medium text-slate-800"><!--?= htmlspecialchars($lease['unit_name']) ?--></td>
        <td class="py-3 px-2 text-slate-600"><!--?= htmlspecialchars($lease['lease_start_date']) ?--></td>
        <td class="py-3 px-2 text-slate-600"><!--?= htmlspecialchars($lease['lease_end_date']) ?--></td>
        <td class="py-3 px-2 font-semibold text-slate-800">₱<!--?= number_format((float)$lease['balance'], 2) ?--></td>
        <td class="py-3 px-2"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?= $lease['lease_status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>"> <!--?= htmlspecialchars($lease['lease_status']) ?--> </span></td>
       </tr><!--?php endforeach; ?-->
      </tbody>
     </table>
    </div><!--?php else: ?-->
    <div class="text-center py-8">
     <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
     </svg>
     <p class="text-slate-500 italic">No active leases found.</p>
    </div><!--?php endif; ?-->
   </section> </a>
        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Property Information -->
            <div class="lg:col-span-2 space-y-8">
                <!-- My Property -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 fade-in">
                    <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-slate-800">My Property</h2>
                        <button id="view-property-details" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            View Full Details
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="info-card mb-6">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-xl font-bold mb-2">Unit 3B, Sunset Manor</h3>
                                    <p class="text-blue-100 mb-4">1234 Maple Street, Downtown District</p>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-blue-200">Bedrooms:</span>
                                            <span class="font-semibold ml-2">2</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-200">Bathrooms:</span>
                                            <span class="font-semibold ml-2">1.5</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-200">Square Feet:</span>
                                            <span class="font-semibold ml-2">850 sq ft</span>
                                        </div>
                                        <div>
                                            <span class="text-blue-200">Parking:</span>
                                            <span class="font-semibold ml-2">1 Space</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Features -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-semibold text-slate-800 mb-3">Amenities</h4>
                                <ul class="space-y-2 text-sm text-slate-600">
                                    <li class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Air Conditioning</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Dishwasher</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Balcony</span>
                                    </li>
                                    <li class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Fitness Center</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="bg-slate-50 rounded-lg p-4">
                                <h4 class="font-semibold text-slate-800 mb-3">Contact Information</h4>
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <p class="text-slate-600">Property Manager</p>
                                        <p class="font-medium text-slate-800">Sarah Johnson</p>
                                        <p class="text-blue-600">sarah.johnson@email.com</p>
                                        <p class="text-slate-600">+1 (555) 123-4567</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-600">Emergency Maintenance</p>
                                        <p class="font-medium text-red-600">+1 (555) 911-HELP</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History & Receipts -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 fade-in">
                    <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-slate-800">Payment History & Receipts</h2>
                        <button id="upload-receipt-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Upload Receipt
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-800">December 2024 Rent</h3>
                                        <p class="text-sm text-slate-600">Paid on Nov 28, 2024 • Bank Transfer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-slate-800">$1,200.00</p>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm">View Receipt</button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-800">November 2024 Rent</h3>
                                        <p class="text-sm text-slate-600">Paid on Oct 30, 2024 • Credit Card</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-slate-800">$1,200.00</p>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm">View Receipt</button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border border-slate-200">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-slate-800">Security Deposit</h3>
                                        <p class="text-sm text-slate-600">Paid on Aug 15, 2024 • Bank Transfer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-slate-800">$1,200.00</p>
                                    <button class="text-blue-600 hover:text-blue-700 text-sm">View Receipt</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 fade-in">
                    <div class="p-6 border-b border-slate-200">
                        <h2 class="text-xl font-semibold text-slate-800">Quick Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <button id="maintenance-request-btn" class="action-btn w-full bg-orange-600 hover:bg-orange-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Request Maintenance</span>
                        </button>
                        <button id="upload-receipt-btn-2" class="action-btn w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span>Upload Receipt</span>
                        </button>
                        <button class="action-btn w-full bg-white hover:bg-slate-50 text-slate-700 font-medium py-3 px-4 rounded-lg border border-slate-200 transition-all duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>Contact Manager</span>
                        </button>
                        <button class="action-btn w-full bg-white hover:bg-slate-50 text-slate-700 font-medium py-3 px-4 rounded-lg border border-slate-200 transition-all duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>View Lease</span>
                        </button>
                    </div>
                </div>

                <!-- Maintenance Requests -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 fade-in">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">My Maintenance Requests</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-medium text-slate-800">Kitchen Faucet Leak</h4>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full status-pending">Pending</span>
                            </div>
                            <p class="text-sm text-slate-600 mb-2">Submitted on Dec 1, 2024</p>
                            <p class="text-sm text-slate-700">The kitchen faucet has been dripping constantly for the past week.</p>
                        </div>

                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-medium text-slate-800">Bathroom Light Bulb</h4>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full status-completed">Completed</span>
                            </div>
                            <p class="text-sm text-slate-600 mb-2">Completed on Nov 25, 2024</p>
                            <p class="text-sm text-slate-700">Light bulb in main bathroom needs replacement.</p>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 fade-in">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-800">Upcoming Events</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800">Rent Due</p>
                                <p class="text-xs text-slate-600">January 1, 2025 • $1,200.00</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg border border-purple-200">
                            <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800">Property Inspection</p>
                                <p class="text-xs text-slate-600">December 15, 2024 • 2:00 PM</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg border border-green-200">
                            <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800">Lease Renewal Notice</p>
                                <p class="text-xs text-slate-600">February 15, 2025</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Upload Receipt Modal -->
    <div id="receipt-modal" class="modal">
        <div class="modal-content">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">Upload Payment Receipt</h3>
                <p class="text-slate-600 text-sm">Upload your payment receipt for record keeping</p>
            </div>
            
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment Type</label>
                    <select class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>Monthly Rent</option>
                        <option>Utility Payment</option>
                        <option>Security Deposit</option>
                        <option>Late Fee</option>
                        <option>Other</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Amount</label>
                        <input type="number" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="1200.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Payment Date</label>
                        <input type="date" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method</label>
                    <select class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option>Bank Transfer</option>
                        <option>Credit Card</option>
                        <option>Debit Card</option>
                        <option>Check</option>
                        <option>Cash</option>
                        <option>Money Order</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Receipt File</label>
                    <div class="file-upload-area" id="file-upload-area">
                        <input type="file" id="receipt-file" class="hidden" accept="image/*,.pdf">
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-slate-600 text-sm mb-2">Click to upload or drag and drop</p>
                        <p class="text-slate-400 text-xs">PNG, JPG, PDF up to 10MB</p>
                    </div>
                    <div id="file-preview" class="mt-4 hidden">
                        <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800" id="file-name"></p>
                                <p class="text-xs text-slate-600" id="file-size"></p>
                            </div>
                            <button type="button" id="remove-file" class="text-red-600 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Notes (Optional)</label>
                    <textarea class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" rows="3" placeholder="Add any additional notes about this payment..."></textarea>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button id="save-receipt-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    Upload Receipt
                </button>
                <button id="close-receipt-modal" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium py-3 px-4 rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Maintenance Request Modal -->
    <div id="maintenance-modal" class="modal">
        <div class="modal-content">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">Submit Maintenance Request</h3>
                <p class="text-slate-600 text-sm">Report an issue that needs attention in your unit</p>
            </div>
            
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Issue Category</label>
                    <select class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option>Select category</option>
                        <option>Plumbing</option>
                        <option>Electrical</option>
                        <option>HVAC/Heating</option>
                        <option>Appliances</option>
                        <option>Flooring</option>
                        <option>Windows/Doors</option>
                        <option>Pest Control</option>
                        <option>Other</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Priority Level</label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="priority" value="low" class="w-4 h-4 text-green-600 border-slate-300 focus:ring-green-500">
                            <span class="ml-2 text-sm text-slate-700">Low</span>
                        </label>
                        <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="priority" value="medium" class="w-4 h-4 text-yellow-600 border-slate-300 focus:ring-yellow-500">
                            <span class="ml-2 text-sm text-slate-700">Medium</span>
                        </label>
                        <label class="flex items-center p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="priority" value="urgent" class="w-4 h-4 text-red-600 border-slate-300 focus:ring-red-500">
                            <span class="ml-2 text-sm text-slate-700">Urgent</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Location in Unit</label>
                    <select class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option>Select location</option>
                        <option>Kitchen</option>
                        <option>Living Room</option>
                        <option>Master Bedroom</option>
                        <option>Second Bedroom</option>
                        <option>Main Bathroom</option>
                        <option>Half Bathroom</option>
                        <option>Balcony</option>
                        <option>Hallway</option>
                        <option>Entire Unit</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Issue Title</label>
                    <input type="text" class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Brief description of the issue">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Detailed Description</label>
                    <textarea class="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" rows="4" placeholder="Please provide a detailed description of the issue, including when it started and any relevant details..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Photos (Optional)</label>
                    <div class="file-upload-area" id="maintenance-file-upload">
                        <input type="file" id="maintenance-photos" class="hidden" accept="image/*" multiple>
                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-slate-600 text-sm mb-2">Click to upload photos or drag and drop</p>
                        <p class="text-slate-400 text-xs">PNG, JPG up to 5MB each (max 5 photos)</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Preferred Contact Method</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="contact-method" value="email" class="w-4 h-4 text-orange-600 border-slate-300 focus:ring-orange-500">
                            <span class="ml-2 text-sm text-slate-700">Email</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="contact-method" value="phone" class="w-4 h-4 text-orange-600 border-slate-300 focus:ring-orange-500">
                            <span class="ml-2 text-sm text-slate-700">Phone</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="contact-method" value="text" class="w-4 h-4 text-orange-600 border-slate-300 focus:ring-orange-500">
                            <span class="ml-2 text-sm text-slate-700">Text Message</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button id="submit-maintenance-btn" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    Submit Request
                </button>
                <button id="close-maintenance-modal" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium py-3 px-4 rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Property Details Modal -->
    <div id="property-modal" class="modal">
        <div class="modal-content">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">Property Details</h3>
                <p class="text-slate-600 text-sm">Complete information about your rental unit</p>
            </div>
            
            <div class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <h4 class="font-semibold text-slate-800 mb-3">Basic Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Unit Number</p>
                            <p class="font-semibold text-slate-800">3B</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Building</p>
                            <p class="font-semibold text-slate-800">Sunset Manor</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Floor</p>
                            <p class="font-semibold text-slate-800">3rd Floor</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Unit Type</p>
                            <p class="font-semibold text-slate-800">2BR/1.5BA</p>
                        </div>
                    </div>
                </div>

                <!-- Lease Information -->
                <div>
                    <h4 class="font-semibold text-slate-800 mb-3">Lease Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Lease Start</p>
                            <p class="font-semibold text-slate-800">Aug 15, 2024</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Lease End</p>
                            <p class="font-semibold text-slate-800">Aug 15, 2025</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Monthly Rent</p>
                            <p class="font-semibold text-slate-800">$1,200.00</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-slate-600">Security Deposit</p>
                            <p class="font-semibold text-slate-800">$1,200.00</p>
                        </div>
                    </div>
                </div>

                <!-- Utilities & Services -->
                <div>
                    <h4 class="font-semibold text-slate-800 mb-3">Utilities & Services</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Water Included</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Trash Collection</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Electricity (Tenant)</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Internet (Tenant)</span>
                        </div>
                    </div>
                </div>

                <!-- Building Amenities -->
                <div>
                    <h4 class="font-semibold text-slate-800 mb-3">Building Amenities</h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Fitness Center</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Laundry Room</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Parking Garage</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Rooftop Terrace</span>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <div>
                    <h4 class="font-semibold text-slate-800 mb-3">Emergency Contacts</h4>
                    <div class="space-y-3 text-sm">
                        <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                            <p class="font-medium text-red-800">Emergency Maintenance</p>
                            <p class="text-red-700">+1 (555) 911-HELP</p>
                            <p class="text-red-600 text-xs">Available 24/7 for urgent issues</p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <p class="font-medium text-blue-800">Building Security</p>
                            <p class="text-blue-700">+1 (555) 123-SAFE</p>
                            <p class="text-blue-600 text-xs">24/7 security desk</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button id="close-property-modal" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium py-3 px-6 rounded-lg transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container"></div>

    <script>
// Tenant Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Modal Management
    const modals = {
        receipt: document.getElementById('receipt-modal'),
        maintenance: document.getElementById('maintenance-modal'),
        property: document.getElementById('property-modal')
    };

    // Button Event Listeners
    const buttons = {
        uploadReceipt: document.getElementById('upload-receipt-btn'),
        uploadReceipt2: document.getElementById('upload-receipt-btn-2'),
        maintenanceRequest: document.getElementById('maintenance-request-btn'),
        viewPropertyDetails: document.getElementById('view-property-details'),
        saveReceipt: document.getElementById('save-receipt-btn'),
        submitMaintenance: document.getElementById('submit-maintenance-btn')
    };

    // Modal Functions
    function openModal(modalName) {
        if (modals[modalName]) {
            modals[modalName].style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalName) {
        if (modals[modalName]) {
            modals[modalName].style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function closeAllModals() {
        Object.keys(modals).forEach(modalName => {
            closeModal(modalName);
        });
    }

    // Event Listeners for Opening Modals
    if (buttons.uploadReceipt) {
        buttons.uploadReceipt.addEventListener('click', () => openModal('receipt'));
    }

    if (buttons.uploadReceipt2) {
        buttons.uploadReceipt2.addEventListener('click', () => openModal('receipt'));
    }

    if (buttons.maintenanceRequest) {
        buttons.maintenanceRequest.addEventListener('click', () => openModal('maintenance'));
    }

    if (buttons.viewPropertyDetails) {
        buttons.viewPropertyDetails.addEventListener('click', () => openModal('property'));
    }

    // Event Listeners for Closing Modals
    const closeButtons = [
        'close-receipt-modal',
        'close-maintenance-modal',
        'close-property-modal'
    ];

    closeButtons.forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            button.addEventListener('click', closeAllModals);
        }
    });

    // Close modals when clicking outside
    Object.values(modals).forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeAllModals();
                }
            });
        }
    });

    // File Upload Functionality
    function setupFileUpload(uploadAreaId, fileInputId, previewId) {
        const uploadArea = document.getElementById(uploadAreaId);
        const fileInput = document.getElementById(fileInputId);
        const preview = document.getElementById(previewId);

        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());

            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(files[0], preview);
                }
            });

            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0], preview);
                }
            });
        }
    }

    function handleFileSelect(file, preview) {
        if (preview) {
            const fileName = document.getElementById('file-name');
            const fileSize = document.getElementById('file-size');
            
            if (fileName) fileName.textContent = file.name;
            if (fileSize) fileSize.textContent = formatFileSize(file.size);
            
            preview.classList.remove('hidden');
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Setup file uploads
    setupFileUpload('file-upload-area', 'receipt-file', 'file-preview');
    setupFileUpload('maintenance-file-upload', 'maintenance-photos', null);

    // Remove file functionality
    const removeFileBtn = document.getElementById('remove-file');
    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', function() {
            const fileInput = document.getElementById('receipt-file');
            const preview = document.getElementById('file-preview');
            
            if (fileInput) fileInput.value = '';
            if (preview) preview.classList.add('hidden');
        });
    }

    // Receipt Upload
    if (buttons.saveReceipt) {
        buttons.saveReceipt.addEventListener('click', function() {
            const form = document.querySelector('#receipt-modal');
            const requiredInputs = form.querySelectorAll('select, input[type="number"], input[type="date"]');
            
            let isValid = true;
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444';
                } else {
                    input.style.borderColor = '#e2e8f0';
                }
            });

            if (isValid) {
                showNotification('Payment receipt uploaded successfully!', 'success');
                closeAllModals();
                // Clear form
                requiredInputs.forEach(input => input.value = '');
                const fileInput = document.getElementById('receipt-file');
                const preview = document.getElementById('file-preview');
                if (fileInput) fileInput.value = '';
                if (preview) preview.classList.add('hidden');
            } else {
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }

    // Maintenance Request Submission
    if (buttons.submitMaintenance) {
        buttons.submitMaintenance.addEventListener('click', function() {
            const form = document.querySelector('#maintenance-modal');
            const requiredInputs = form.querySelectorAll('select, input[type="text"], textarea');
            const priorityRadios = form.querySelectorAll('input[name="priority"]');
            const contactRadios = form.querySelectorAll('input[name="contact-method"]');
            
            let isValid = true;
            
            // Check required fields
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444';
                } else {
                    input.style.borderColor = '#e2e8f0';
                }
            });

            // Check priority selection
            const prioritySelected = Array.from(priorityRadios).some(radio => radio.checked);
            if (!prioritySelected) {
                isValid = false;
                showNotification('Please select a priority level', 'error');
            }

            // Check contact method selection
            const contactSelected = Array.from(contactRadios).some(radio => radio.checked);
            if (!contactSelected) {
                isValid = false;
                showNotification('Please select a preferred contact method', 'error');
            }

            if (isValid) {
                const requestId = generateRequestId();
                showNotification(`Maintenance request #${requestId} submitted successfully!`, 'success');
                closeAllModals();
                
                // Clear form
                requiredInputs.forEach(input => input.value = '');
                priorityRadios.forEach(radio => radio.checked = false);
                contactRadios.forEach(radio => radio.checked = false);
            } else if (isValid !== false) {
                showNotification('Please fill in all required fields', 'error');
            }
        });
    }

    // Notification System
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        const icon = getNotificationIcon(type);
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-800">${message}</p>
                </div>
                <button class="flex-shrink-0 text-slate-400 hover:text-slate-600" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;

        container.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    function getNotificationIcon(type) {
        const icons = {
            success: `<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>`,
            error: `<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>`,
            warning: `<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                      </svg>`,
            info: `<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                   </svg>`
        };
        return icons[type] || icons.info;
    }

    // Utility Functions
    function generateRequestId() {
        return 'MR' + Math.random().toString(36).substr(2, 6).toUpperCase();
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    function formatDate(date) {
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(new Date(date));
    }

    // Initialize UI enhancements
    function initializeUI() {
        // Add loading states to buttons
        const actionButtons = document.querySelectorAll('.action-btn');
        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 1000);
                }
            });
        });

        // Initialize fade-in animations for cards
        const cards = document.querySelectorAll('.property-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Set current date as default for receipt upload
        const dateInput = document.querySelector('input[type="date"]');
        if (dateInput) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape key closes all modals
        if (e.key === 'Escape') {
            closeAllModals();
        }
        
        // Ctrl/Cmd + U opens upload receipt modal
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            openModal('receipt');
        }
        
        // Ctrl/Cmd + M opens maintenance request modal
        if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
            e.preventDefault();
            openModal('maintenance');
        }
    });

    // Form Validation Enhancement
    function addRealTimeValidation() {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') || this.value.trim()) {
                    if (!this.value.trim()) {
                        this.style.borderColor = '#ef4444';
                    } else {
                        this.style.borderColor = '#e2e8f0';
                    }
                }
            });

            input.addEventListener('input', function() {
                if (this.style.borderColor === 'rgb(239, 68, 68)') {
                    if (this.value.trim()) {
                        this.style.borderColor = '#e2e8f0';
                    }
                }
            });
        });
    }

    // Initialize everything
    initializeUI();
    addRealTimeValidation();

    // Show welcome message
    setTimeout(() => {
        showNotification('Welcome to your Unitly tenant dashboard!', 'success');
    }, 1000);

    // Simulate real-time updates (in a real app, this would come from a server)
    function simulateRealTimeUpdates() {
        // Simulate maintenance request status updates
        setTimeout(() => {
            const maintenanceCards = document.querySelectorAll('.status-pending');
            if (maintenanceCards.length > 0) {
                // This would be triggered by real server updates
                console.log('Maintenance request status updated');
            }
        }, 30000);
    }

    simulateRealTimeUpdates();
});

// Export functions for external use
window.TenantDashboard = {
    showNotification: function(message, type) {
        const event = new CustomEvent('showNotification', {
            detail: { message, type }
        });
        document.dispatchEvent(event);
    },
    
    uploadReceipt: function(receiptData) {
        // This would handle programmatic receipt uploads
        console.log('Receipt uploaded:', receiptData);
    },
    
    submitMaintenanceRequest: function(requestData) {
        // This would handle programmatic maintenance requests
        console.log('Maintenance request submitted:', requestData);
    }
};
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'994a85bac43384a6',t:'MTc2MTQ4NzY3MS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
