<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant" || !isset($_SESSION["user_id"])) {
    header("Location: ../../login_page_user.php");
    exit;
}


require_once "../dbConnect.php";
require_once "../leaseManager.php";
require_once "../paymentManager.php";
require_once "../propertyManager.php";
require_once "../maintenanceManager.php";

// Create DB connection
$database = new Database();
$db = $database->getConnection();

// Initialize managers
$userId = (int) $_SESSION["user_id"];

// Fetch maintenance requests
$maintenanceManager = new MaintenanceManager($db);
$requests = $maintenanceManager->getRequestsByTenant($userId);
$maintenanceCount = count($requests);
$pendingRequests = 0;
foreach ($requests as $r) {
    if ($r['maintenance_status'] === 'Ongoing') { // Assuming 'Ongoing' is the pending state
        $pendingRequests++;
    }
}

// Fetch leases & payments
$leaseManager = new LeaseManager($db);
$paymentManager = new PaymentManager($db);
$leases = $leaseManager->getLeasesByTenant($userId);
$totalLeases = $leases ? count($leases) : 0;
$payments = $paymentManager->getPaymentsByTenant($userId);
$nextPayment = $paymentManager->getNextDuePayment($userId);

// Fetch tenant's current ACTIVE property location directly
$stmt = $db->prepare("
    SELECT 
        p.property_name,
        un.unit_name,
        loc.latitude,
        loc.longitude,
        l.lease_status
    FROM lease_tbl l
    INNER JOIN unit_tbl un ON l.unit_id = un.unit_id
    INNER JOIN property_tbl p ON un.property_id = p.property_id  
    INNER JOIN location_tbl loc ON p.location_id = loc.location_id
    WHERE l.user_id = :user_id 
    AND l.lease_status = 'Active'
    ORDER BY l.lease_start_date DESC
    LIMIT 1
");
$stmt->execute(['user_id' => $userId]);
$currentProperty = $stmt->fetch(PDO::FETCH_ASSOC);

// Use coordinates from active lease or fallback to default
$lat = $currentProperty['latitude'] ?? 13.940;
$lng = $currentProperty['longitude'] ?? 121.163;
$propertyName = $currentProperty['property_name'] ?? 'N/A';
$unitName = $currentProperty['unit_name'] ?? 'N/A';

// Fetch tenant info for other details
$stmt = $db->prepare("
    SELECT
        u.full_name, u.phone_no,
        t.email, t.birthdate, t.age, t.gender, t.id_type,
        t.id_number, t.id_photo, t.birth_certificate, t.tenant_photo,
        t.occupation, t.employer_name, t.monthly_income, t.proof_of_income,
        t.emergency_name, t.emergency_contact, t.relationship
    FROM tenant_info_tbl t
    LEFT JOIN user_tbl u ON t.user_id = u.user_id
    WHERE t.user_id = :user_id
");
$stmt->execute(['user_id' => $userId]);
$tenantInfo = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate lease expiration
$leaseExpiryMonths = null;
$leaseEndDate = null;
if (!empty($leases)) {
    $currentLease = $leases[0]; // Assuming the first one is the most relevant
    if (!empty($currentLease['lease_end_date'])) {
        $endDate = new DateTime($currentLease['lease_end_date']);
        $now = new DateTime();
        $interval = $now->diff($endDate);
        $leaseExpiryMonths = ($interval->y * 12) + $interval->m;
        $leaseEndDate = $endDate->format('M d, Y');
    } else if ($currentLease['lease_status'] === 'Active') {
        // *** THIS IS THE FIX ***
        // If end date is NULL but lease is Active, it's open-ended
        $leaseExpiryMonths = 'Open';
        $leaseEndDate = 'Present';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unitly - Tenant Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <!-- ✅ LEAFLET LOADED IN HEAD -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #tenantMap { 
            width: 100%; 
            height: 192px; 
            border-radius: 8px; 
            border: 1px solid #d1d5db;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">

    <!-- Header -->
    <?php include '../../assets/header.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">
        <h2 class="text-2xl font-semibold text-slate-800 mb-6">Overview</h2>
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in">
            <!-- Current Apartment -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card flex flex-col justify-between">
                <?php if ($currentProperty): ?>
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-slate-600 text-sm font-medium">Apartment</p>
                            <p class="text-2xl font-semibold text-slate-800 mt-1">
                                <?= htmlspecialchars($propertyName) ?>
                            </p>
                            <p class="text-xs text-slate-600 mt-1">
                                Unit: <?= htmlspecialchars($unitName) ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                            </svg>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- No Active Apartment -->
                    <div class="flex flex-col items-center justify-center text-center py-10">
                        <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2
                                   3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402
                                   2.599 1M12 8V7m0 1v8m0 0v1m0-1
                                   c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        <p class="text-slate-500 italic text-sm">No active apartment found</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Next Payment -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Due Payment</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1">
                            ₱<?= isset($nextPayment['balance']) ? number_format($nextPayment['balance'], 2) : '0.00' ?>
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            Due Date: <?= isset($nextPayment['lease_end_date']) ? htmlspecialchars($nextPayment['lease_end_date']) : 'N/A' ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Maintenance Requests -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Maintenance Requests</p>
                        <p class="text-3xl font-bold text-slate-800 mt-1"><?= $maintenanceCount ?? 0 ?></p>
                        <p class="text-xs text-orange-600 mt-1"><?= $pendingRequests ?? 0 ?> pending</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066
                                       c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572
                                       c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573
                                       c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065
                                       c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066
                                       c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572
                                       c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573
                                       c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <a href="../tenantMaintenance.php"
                           class="mt-3 text-sm font-medium text-orange-600 hover:underline">
                           View Requests
                        </a>
                    </div>
                </div>
            </div>

            <!-- Lease Expiration -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium">Lease Expires</p>
                        <!-- *** UPDATED LOGIC *** -->
                        <p class="text-xl font-bold text-slate-800 mt-1">
                            <?= isset($leaseExpiryMonths) 
                                ? ($leaseExpiryMonths === 'Open' 
                                    ? 'Open-Ended' 
                                    : htmlspecialchars($leaseExpiryMonths) . ' months') 
                                : 'N/A' ?>
                        </p>
                        <p class="text-xs text-slate-600 mt-1">
                            <?= isset($leaseEndDate) 
                                ? ($leaseEndDate === 'Present' 
                                    ? 'Active Lease (Present)' 
                                    : htmlspecialchars($leaseEndDate)) 
                                : 'No active lease' ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div> <!-- End Stats Grid -->

        <!-- *** START MAIN GRID *** -->
        <!-- *** THIS IS THE HTML FIX. The grid and columns are redefined correctly *** -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
            
            <!-- Left Column (Payment + Info) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Next Payment Due -->
                <?php if ($nextPayment && $nextPayment['balance'] > 0): ?>
                    <div class="bg-white rounded-xl shadow-sm p-8 border border-slate-200 property-card">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <p class="text-slate-600 text-sm font-medium">Next Payment Due</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">
                                    ₱<?= number_format($nextPayment['balance'], 2) ?>
                                </p>
                                <p class="text-xs text-slate-600 mt-1">
                                    Unit: <?= htmlspecialchars($nextPayment['unit_name']) ?>
                                </p>
                                <p class="text-xs text-orange-600 mt-1">
                                    Due: <?= isset($nextPayment['lease_end_date']) ? htmlspecialchars($nextPayment['lease_end_date']) : 'N/A' // Fallback for due date ?>
                                </p>
                            </div>
                            <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 
                                           3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 
                                           2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </div>

                        <a href="../makePayment.php?lease_id=<?= $nextPayment['lease_id'] ?>"
                           class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 
                                       0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 
                                       0 00-2-2H9a2 2 0 00-2 2v6a2 2 
                                       0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Pay Now</span>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Payments Section (This was missing) -->
                <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div class="flex items-center space-x-3 mb-4 sm:mb-0">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800">My Payments</h3>
                        </div>
                         <!-- Sorting and Filtering -->
                         <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-slate-600">Sort by:</label>
                                <select id="sortBy" class="text-sm border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                                    <option value="date-desc">Date (Newest)</option>
                                    <option value="date-asc">Date (Oldest)</option>
                                    <option value="unit-asc">Unit (A–Z)</option>
                                    <option value="unit-desc">Unit (Z–A)</option>
                                    <option value="amount-desc">Amount (High–Low)</option>
                                    <option value="amount-asc">Amount (Low–High)</option>
                                </select>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-slate-600">Unit:</label>
                                <select id="filterUnit" class="text-sm border rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Units</option>
                                    <?php if ($payments): 
                                        $units = array_unique(array_column($payments, 'unit_name'));
                                        sort($units);
                                        foreach ($units as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit) ?>"><?= htmlspecialchars($unit) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <?php if ($payments): ?>
                        <div class="overflow-x-auto mb-8">
                            <table class="w-full text-sm" id="paymentsTable">
                                <thead>
                                    <tr class="border-b-2 border-slate-200 bg-slate-50">
                                        <?php 
                                        $headers = ['Unit','Date','Amount Paid','Balance After','Status','Receipt'];
                                        foreach ($headers as $h) {
                                            echo "<th class='text-left py-4 px-4 font-semibold text-slate-700'>" . htmlspecialchars($h) . "</th>";
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody id="paymentsTableBody">
                                    <?php foreach ($payments as $payment): ?>
                                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                            <td class="py-4 px-4 font-medium text-slate-800"><?= htmlspecialchars($payment['unit_name']) ?></td>
                                            <td class="py-4 px-4 text-slate-600">
                                                <?= date('M d, Y', strtotime($payment['payment_date'])) ?><br>
                                                <span class="text-xs text-slate-500"><?= date('g:i A', strtotime($payment['payment_date'])) ?></span>
                                            </td>
                                            <td class="py-4 px-4 font-bold text-green-600">₱<?= number_format((float)$payment['amount'], 2) ?></td>
                                            <td class="py-4 px-4 font-semibold text-slate-800">₱<?= number_format((float)($payment['balance_after_payment'] ?? 0), 2) ?></td>
                                            <td class="py-4 px-4">
                                                <?php
                                                    $status = strtolower($payment['status']);
                                                    $statusClasses = [
                                                        'confirmed'=>'bg-green-100 text-green-800', 
                                                        'ongoing'=>'bg-yellow-100 text-yellow-800', 
                                                        'failed'=>'bg-red-100 text-red-800',
                                                        'late' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $badge = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium <?= $badge ?>">
                                                    <?= htmlspecialchars(ucfirst($payment['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <?php if (!empty($payment['receipt_upload'])): ?>
                                                    <a href="/<?= htmlspecialchars(str_replace('\\', '/', $payment['receipt_upload'])) ?>" target="_blank"
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg">
                                                        View
                                                    </a>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 text-xs font-medium rounded-lg">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 mb-8">
                            <p class="text-slate-500 italic">No payments found.</p>
                             <a href="../makePayment.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                 Make a Payment
                             </a>
                        </div>
                    <?php endif; ?>
                </section>
            </div> <!-- End Left Column -->

            <!-- Right Column (Info) -->
            <div class="lg:col-span-1 space-y-8">
                <!-- Tenant Info -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between h-full">
                    <div>
                        <h2 class="text-2xl font-semibold mb-4">My Information</h2>
                        <?php if ($tenantInfo): ?>
                            <div class="space-y-2 text-sm">
                                <p><strong>Full Name:</strong> <?= htmlspecialchars($tenantInfo['full_name']); ?></p>
                                <p><strong>Birthdate:</strong> <?= htmlspecialchars($tenantInfo['birthdate']); ?></p>
                                <p><strong>Gender:</strong> <?= htmlspecialchars($tenantInfo['gender']); ?></p>
                                <p><strong>Contact:</strong> <?= htmlspecialchars($tenantInfo['phone_no']); ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($tenantInfo['email']); ?></p>
                                <p><strong>Occupation:</strong> <?= htmlspecialchars($tenantInfo['occupation']); ?></p>
                                <p><strong>Employer:</strong> <?= htmlspecialchars($tenantInfo['employer_name']); ?></p>
                                <p><strong>Monthly Income:</strong> ₱<?= htmlspecialchars(number_format((float)($tenantInfo['monthly_income'] ?? 0), 2)); ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-slate-500 italic text-sm">No information found yet.</p>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end mt-6">
                        <a href="../tenantInfo.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                            View / Edit My Information
                        </a>
                    </div>
                </div>
            </div> <!-- End Right Column -->

        </div> <!-- *** END MAIN GRID *** -->
        
    </main>

    <!-- Footer -->
    <?php include '../../assets/footer.php'; ?>

    <!-- ✅ FIXED MAP SCRIPT -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if ($currentProperty): ?>
            const lat = <?= $lat ?>;
            const lng = <?= $lng ?>;
            
            console.log("Initializing map with coordinates:", lat, lng);
            
            try {
                const map = L.map('tenantMap').setView([lat, lng], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(map);
                
                // Add marker
                L.marker([lat, lng]).addTo(map)
                    .bindPopup('<?= htmlspecialchars($propertyName) ?>')
                    .openPopup();
                
                // Disable interaction for static view
                map.dragging.disable();
                map.touchZoom.disable();
                map.doubleClickZoom.disable();
                map.scrollWheelZoom.disable();
                map.boxZoom.disable();
                map.keyboard.disable();
                
                console.log("Map initialized successfully at:", lat, lng);
                
            } catch (error) {
                console.error("Map initialization failed:", error);
                document.getElementById('tenantMap').innerHTML = 
                    '<div class="flex items-center justify-center h-full text-red-500">Map failed to load</div>';
            }
        <?php else: ?>
            console.log("No property data available for map");
        <?php endif; ?>
    });
    </script>

</body>
</html>