<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../../login_page.php");
    exit;
}

// Redirect if not logged in as Admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {
    header("Location: ../../login_page.php"); // Fixed missing slash
    exit;
}

// Get session messages
$adminSuccess = $_SESSION['admin_success'] ?? null;
$adminError   = $_SESSION['admin_error'] ?? null;

// Clear them so they don't show again on refresh
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

require_once "../../PHP/dbConnect.php";
$database = new Database();
$db = $database->getConnection();


try {
    // 1️⃣ Total Properties
    $stmt = $db->query("SELECT COUNT(*) AS total_properties FROM property_tbl");
    $totalProperties = $stmt->fetch(PDO::FETCH_ASSOC)['total_properties'] ?? 0;

    // 2️⃣ Active Landlords (Role ID = 1)
    $stmt = $db->query("
        SELECT COUNT(*) AS active_landlords
        FROM user_role_tbl ur
        JOIN user_tbl u ON ur.user_id = u.user_id
        WHERE ur.role_id = 1
    ");
    $activeLandlords = $stmt->fetch(PDO::FETCH_ASSOC)['active_landlords'] ?? 0;

    // 3️⃣ Active Tenants (Role ID = 2 + Active Leases)
    $stmt = $db->query("
        SELECT COUNT(DISTINCT l.user_id) AS active_tenants
        FROM lease_tbl l
        JOIN user_role_tbl ur ON l.user_id = ur.user_id
        WHERE ur.role_id = 2 AND l.lease_status = 'Active'
    ");
    $activeTenants = $stmt->fetch(PDO::FETCH_ASSOC)['active_tenants'] ?? 0;

    // 4️⃣ Pending Payments
    $stmt = $db->query("
        SELECT COUNT(*) AS pending_payments
        FROM payment_tbl
        WHERE payment_status = 'Ongoing'
    ");
    $pendingPayments = $stmt->fetch(PDO::FETCH_ASSOC)['pending_payments'] ?? 0;

} catch (PDOException $e) {
    // Default fallback values in case of error
    $totalProperties = $activeLandlords = $activeTenants = $pendingPayments = 0;
    // Optional: log $e->getMessage();
}

try {
    $stmt = $db->prepare("
        SELECT u.user_id, u.full_name, u.phone_no
        FROM user_tbl u
        JOIN user_role_tbl ur ON u.user_id = ur.user_id
        WHERE ur.role_id = 1
        ORDER BY u.full_name ASC
    ");
    $stmt->execute();
    $landlords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $landlords = [];
    // Optionally log error or display message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Unitly Admin - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="../../assets/styles.css">
   <script src="../../assets/script.js" defer></script> 
    <script src="../../assets/admin.js" defer></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen font-sans flex flex-col">
    <div id="notification-container"></div>
    <?php include '../../assets/header.php'; ?>
    
    <main class="flex-grow max-w-7xl mx-auto px-6 py-8 w-full">
        <h2 class="text-2xl font-semibold text-slate-800">Admin Dashboard</h2>
<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 fade-in">

    <!-- TOTAL PROPERTIES -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Total Properties</p>
                <p class="text-3xl font-bold text-slate-800 mt-1"><?= $totalProperties ?></p>
                <p class="text-xs text-slate-600 mt-1">All registered properties</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ACTIVE LANDLORDS -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Active Landlords</p>
                <p class="text-3xl font-bold text-slate-800 mt-1"><?= $activeLandlords ?></p>
                <p class="text-xs text-green-600 mt-1">Managing at least one property</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5V4H2v16h5m10 0v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- ACTIVE TENANTS -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Active Tenants</p>
                <p class="text-3xl font-bold text-slate-800 mt-1"><?= $activeTenants ?></p>
                <p class="text-xs text-orange-600 mt-1">Currently renting</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 14a4 4 0 10-8 0v6h8v-6zM12 7a4 4 0 110-8 4 4 0 010 8z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- PENDING PAYMENTS -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200 property-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-600 text-sm font-medium">Pending Payments</p>
                <p class="text-3xl font-bold text-slate-800 mt-1"><?= $pendingPayments ?></p>
                <p class="text-xs text-purple-600 mt-1">Awaiting verification</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 fade-in">
</div>

    <div class="grid grid-cols-1 gap-8">
  <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
      <div class="flex items-center space-x-3 mb-4 sm:mb-0">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <div>
          <h3 class="text-xl font-semibold text-slate-800">Landlord Management</h3>
          <p class="text-slate-600 text-sm">Manage landlord accounts and permissions</p>
        </div>
      </div>

        <!-- Add New Landlord Button -->
        <button id="open-add-landlord-modal-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Add New Landlord</span>
            </button>
        </div>

        <?php if (!empty($landlords)): ?>
        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
        <!-- Search -->
        <div class="flex items-center space-x-3">
            <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" id="searchInput" placeholder="Search landlords..."
                class="pl-10 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
            </div>
        </div>

        <!-- Sort + Export -->
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-slate-600">Sort by:</label>
            <select id="sortBy"
                class="text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="name-asc">Name (A-Z)</option>
                <option value="name-desc">Name (Z-A)</option>
                <option value="id-asc">ID (Low-High)</option>
                <option value="id-desc">ID (High-Low)</option>
                <option value="phone-asc">Phone (A-Z)</option>
                <option value="phone-desc">Phone (Z-A)</option>
            </select>
            </div>

            <!-- Export Button -->
            <button id="exportBtn"
            class="px-3 py-2 text-sm text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Export</span>
            </button>
        </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto">
        <table class="w-full text-sm" id="landlordsTable">
            <thead>
            <tr class="border-b-2 border-slate-200 bg-slate-50">
                <th class="text-left py-4 px-4 font-semibold text-slate-700">ID</th>
                <th class="text-left py-4 px-4 font-semibold text-slate-700">Full Name</th>
                <th class="text-left py-4 px-4 font-semibold text-slate-700">Phone Number</th>
                <th class="text-left py-4 px-4 font-semibold text-slate-700">Status</th>
                <th class="text-center py-4 px-4 font-semibold text-slate-700">Actions</th>
            </tr>
            </thead>
            <tbody id="landlordsTableBody">
            <?php foreach ($landlords as $landlord): ?>
            <tr
                class="border-b border-slate-100 hover:bg-slate-50 transition-colors duration-150"
                data-id="<?= htmlspecialchars($landlord['user_id']); ?>"
                data-name="<?= htmlspecialchars($landlord['full_name']); ?>"
                data-phone="<?= htmlspecialchars($landlord['phone_no']); ?>">
                <td class="py-4 px-4 font-medium text-slate-700">#<?= htmlspecialchars($landlord['user_id']); ?></td>
                <td class="py-4 px-4">
                <div class="flex items-center space-x-3">
                    <div
                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                    <?= strtoupper(substr($landlord['full_name'], 0, 1)); ?>
                    </div>
                    <div>
                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($landlord['full_name']); ?></p>
                    <p class="text-xs text-slate-500">Landlord ID: <?= htmlspecialchars($landlord['user_id']); ?></p>
                    </div>
                </div>
                </td>
                <td class="py-4 px-4 text-slate-700"><?= htmlspecialchars($landlord['phone_no']); ?></td>
                <td class="py-4 px-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Active
                </span>
                </td>
                <td class="py-4 px-4 text-center">
                <div class="flex justify-center space-x-2">
                    <a href="../manageLandlord.php?user_id=<?= urlencode($landlord['user_id']); ?>"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-lg transition">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                    </a>
                    <button
                    onclick="confirmDelete(<?= htmlspecialchars($landlord['user_id']); ?>, '<?= htmlspecialchars($landlord['full_name']); ?>')"
                    class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-lg transition">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                    </button>
                    <form id="deleteForm<?= htmlspecialchars($landlord['user_id']); ?>" method="POST"
                    action="../landlordManager.php" class="hidden">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($landlord['user_id']); ?>">
                    <input type="hidden" name="action" value="delete">
                    </form>
                </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- Table Footer -->
        <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-slate-600">
            Showing <span class="font-medium" id="showingCount"><?= count($landlords); ?></span> of
            <span class="font-medium"><?= count($landlords); ?></span> landlords
        </p>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-slate-600">Total Active:</span>
            <span
            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <?= count($landlords); ?>
            </span>
        </div>
        </div>

        <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-12">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
        <h4 class="text-lg font-semibold text-slate-800 mb-2">No landlords found</h4>
        <p class="text-slate-500 mb-4">Get started by adding your first landlord to the system.</p>
        <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Add First Landlord
        </button>
        </div>
        <?php endif; ?>
    </section>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <div class="flex items-center space-x-3 mb-4">
        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-800">Confirm Deletion</h3>
        </div>
        <p class="text-slate-600 mb-6">
        Are you sure you want to delete <span id="deleteLandlordName" class="font-semibold"></span>?
        This action cannot be undone.
        </p>
        <div class="flex items-center justify-end space-x-3">
        <button onclick="closeDeleteModal()"
            class="px-4 py-2 text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors">
            Cancel
        </button>
        <button onclick="submitDelete()"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
            Delete Landlord
        </button>
        </div>
    </div>
    </div>

    </main>
   <!-- Add Landlord Modal -->
        <div id="add-landlord-modal"
            class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg transform transition-all duration-300 scale-95 opacity-0 modal-content">
            <form method="POST" action="../admin.php">

            <!-- Header -->
            <div class="relative bg-gradient-to-br from-blue-600 to-indigo-700 rounded-t-3xl p-8 text-center">
            <!-- Close Button -->
            <button type="button" id="close-modal-btn"
                    class="absolute top-4 right-4 w-10 h-10 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all duration-200"
                    aria-label="Close Modal">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Icon -->
            <div class="w-24 h-24 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold text-white mb-2">Add New Landlord</h2>
            <p class="text-blue-100">Create a secure account for property management</p>
            </div>

            <!-- Form -->
            <form id="landlord-form" class="p-8" method="POST" action="../admin.php">
            <div class="space-y-6">

                <!-- Full Name -->
                <div>
                <label for="full-name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Full Name
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    </div>
                    <input type="text" id="full-name" name="full_name" required
                        placeholder="Enter landlord's full name"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl
                                focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                transition-all duration-200" />
                </div>
                </div>

                <!-- Phone Number -->
<div>
  <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
    Phone Number
  </label>
  <div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13
                  a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498
                  a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
      </svg>
    </div>
<script>
function formatPhoneNumber(input) {
  // Remove all non-digit characters
  let value = input.value.replace(/\D/g, "");

  // Limit to 11 digits
  value = value.substring(0, 11);

  // Format as 1111 111 1111
  if (value.length > 4 && value.length <= 7) {
    value = value.replace(/^(\d{4})(\d+)/, "$1 $2");
  } else if (value.length > 7) {
    value = value.replace(/^(\d{4})(\d{3})(\d+)/, "$1 $2 $3");
  }

  input.value = value;
}
</script>
    <input id="phone" name="phone" maxlength="13" required
      placeholder="09121231234"
      class="w-full pl-10 pr-4 py-4 border border-gray-300 rounded-xl
             focus:ring-2 focus:ring-blue-500 focus:border-transparent
             transition-all duration-200"
      oninput="formatPhoneNumber(this)"
    />
  </div>
</div>



                <!-- Password -->
                <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    Temporary Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0
                                00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    </div>
                    <input type="password" id="password" name="password" required
                        placeholder="Enter secure password"
                        class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl
                                focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                transition-all duration-200" />
                    <button type="button" id="toggle-password"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            aria-label="Toggle Password Visibility">
                    <svg class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors"
                        id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5
                                c4.478 0 8.268 2.943 9.542 7
                                -1.274 4.057-5.064 7-9.542 7
                                -4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Landlord will change this on first login
                </p>
                </div>
            </div>

            <!-- Info Notice -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0
                            11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                    <strong>Security Notice:</strong>
                    The landlord will receive login credentials and must change their password on first login.
                    </p>
                </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 mt-8">
                <button type="button" id="cancel-btn"
                        class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-xl
                            hover:bg-gray-50 transition-all duration-200 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
                </button>

                <button type="submit" id="submit-btn"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600
                            hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl
                            transition-all duration-200 flex items-center justify-center gap-2
                            shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Account
                </button>
            </div>
            </form>
             </form>
        </div>
        </div>


    <?php include '../../assets/footer.php'; ?>
 <script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('add-landlord-modal');
  const openBtn = document.getElementById('open-add-landlord-modal-btn');
  const closeBtn = document.getElementById('close-modal-btn');
  const cancelBtn = document.getElementById('cancel-btn');
  const modalContent = modal.querySelector('.modal-content');

  function showModal() {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
      modalContent.classList.remove('scale-95', 'opacity-0');
      modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
  }

  function hideModal() {
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }, 250);
  }

  // ✅ Button triggers
  if (openBtn) openBtn.addEventListener('click', showModal);
  if (closeBtn) closeBtn.addEventListener('click', hideModal);
  if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

  // ✅ Close when clicking on the background (not inside the modal)
  modal.addEventListener('click', (e) => {
    if (e.target === modal) hideModal();
  });
});
</script>


    <script>
        <?php if ($adminSuccess): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: <?php echo json_encode($adminSuccess); ?>,
                timer: 3000, // Auto close after 3 seconds
                showConfirmButton: false
            });
        <?php elseif ($adminError): ?>
            Swal.fire({
                icon: 'error',
                title: 'Operation Failed',
                text: <?php echo json_encode($adminError); ?>,
                confirmButtonText: 'Okay'
            });
        <?php endif; ?>
    </script>
    <script>
let currentDeleteId = null;

function confirmDelete(userId, fullName) {
  currentDeleteId = userId;
  document.getElementById('deleteLandlordName').textContent = fullName;
  const modal = document.getElementById('deleteModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeDeleteModal() {
  const modal = document.getElementById('deleteModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function submitDelete() {
  if (!currentDeleteId) return;
  const form = document.getElementById('deleteForm' + currentDeleteId);
  if (form) form.submit();
  closeDeleteModal();
}
</script>
    </body>
</html>