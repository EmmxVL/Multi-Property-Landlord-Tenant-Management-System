<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Tenant') {
    header('Location: ../login.html');
    exit;
}
$path = __DIR__ . '/../tenant_dashboard.html';
if (file_exists($path)) {
    readfile($path);
    exit;
} else {
    echo "Tenant dashboard file not found.";
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unitly Tenant - My Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/styles.css">
      <script src="assets/script.js" defer></script>
    <script src="assets/tenant.js"></script>
</head> 
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans">
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
    <footer class="bg-blue-900 text-white mt-12">
        <div class="max-w-7xl mx-auto px-6 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-6 text-blue-100">CompanyName</h3>
                    <h4 class="text-lg font-semibold mb-3 text-blue-200">Our Vision</h4>
                    <p class="text-blue-100 leading-relaxed text-sm">To revolutionize property management by fostering seamless connections between landlords and tenants.</p>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Contact Us</h4>
                    <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
                    <p class="text-blue-100 text-sm">+63 (0906) 581-6503</p>
                    <p class="text-blue-100 text-sm">Unitlyph@gmail.com</p>
                    <p class="text-blue-100 text-sm">www.unitly.com</p>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Our Services</a></li>
                        <li><a href="#" class="footer-link">Developers</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Stay Connected</h4>
                    <div class="flex space-x-4 mb-6">
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775..."/></svg></a>
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M22.46 6c-.77.35-1.6..."/></svg></a>
                    </div>

                    <h5 class="text-lg font-medium mb-3 text-blue-200">Newsletter</h5>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="email" id="newsletter-email" placeholder="Enter your email" class="newsletter-input" required>
                        <button type="submit" class="newsletter-btn">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center">
                <div class="text-blue-200 text-sm">© 2024 Unitly. All rights reserved.</div>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="footer-bottom-link">Privacy Policy</a>
                    <a href="#" class="footer-bottom-link">Terms of Service</a>
                    <a href="#" class="footer-bottom-link">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>


    </script>
    <script src="assets/script.js"></script>
     <script src="assets/tenant.js"></script>
   <script src="assets/styles.css"></script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9881166ce658b9f0',t:'MTc1OTM3NTQ3NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
