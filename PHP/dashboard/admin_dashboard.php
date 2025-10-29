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
    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly Admin</h1>
                    <p class="text-xs text-slate-500">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>!</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="text-slate-700 text-sm hidden sm:inline"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin User'); ?></span>
                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-orange-500 rounded-full flex items-center justify-center text-white font-semibold">
                       <?php // Initials
                           $fullName = $_SESSION['full_name'] ?? 'AU'; $names = explode(' ', $fullName);
                           $initials = ($names[0][0] ?? '') . ($names[1][0] ?? ''); echo htmlspecialchars(strtoupper($initials) ?: 'A');
                       ?>
                    </div>
                     <a href="../logout.php" title="Logout" class="p-2 text-slate-600 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                     </a>
                </div>
            </div>
        </div>
    </header>
    
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

        <div class="grid grid-cols-1 gap-8">
            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">Landlord Management</h3>
                <p class="text-slate-600 mb-4">Manage landlord accounts from here.</p>
                <?php if (!empty($landlords)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse border border-slate-300">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th class="border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700">ID</th>
                                    <th class="border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700">Full Name</th>
                                    <th class="border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700">Phone Number</th>
                                    <th class="border border-slate-300 px-4 py-2 text-left text-sm font-medium text-slate-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($landlords as $landlord): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="border border-slate-300 px-4 py-2 text-sm text-slate-800"><?php echo htmlspecialchars($landlord['user_id']); ?></td>
                                        <td class="border border-slate-300 px-4 py-2 text-sm text-slate-800"><?php echo htmlspecialchars($landlord['full_name']); ?></td>
                                        <td class="border border-slate-300 px-4 py-2 text-sm text-slate-800"><?php echo htmlspecialchars($landlord['phone_no']); ?></td>
                                        <td class="border border-slate-300 px-4 py-2 text-sm text-slate-800 flex gap-2">
                                            <!-- Edit / Manage button -->
                                            <a href="../manageLandlord.php?user_id=<?php echo urlencode($landlord['user_id']); ?>" 
                                            class="text-blue-600 hover:text-blue-800">Edit</a>
                                            
                                            <!-- Delete button -->
                                            <form method="POST" action="../landlordManager.php" onsubmit="return confirm('Are you sure you want to delete this landlord?');" class="inline">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($landlord['user_id']); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                            </form>
                                        </td>


                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500">No landlords found.</p>
                <?php endif; ?>
            </section>

             <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-4">System Overview</h3>
                <p class="text-slate-600">Display key system statistics here.</p>
            </section>
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
                    <div class="absolute inset-y-0 left-10 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">+63</span>
                    </div>
                    <input type="tel" id="phone" name="phone" maxlength="13"
                        placeholder="9XX XXX XXXX"
                        class="w-full pl-20 pr-4 py-3 border border-gray-300 rounded-xl
                                focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                transition-all duration-200" />
                </div>
                <p class="text-xs text-gray-500 mt-1">Philippine mobile number format</p>
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
                        <li><a href="#" class="footer-link hover:text-white">About Us</a></li>
                        <li><a href="#" class="footer-link hover:text-white">Our Services</a></li>
                        <li><a href="#" class="footer-link hover:text-white">Developers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-semibold mb-6 text-blue-200">Stay Connected</h4>
                    <div class="flex space-x-4 mb-6">
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white hover:text-blue-300" viewBox="0 0 24 24" fill="currentColor"><path d="M...Z"/></svg></a>
                        <a href="#" class="social-icon"><svg class="w-4 h-4 text-white hover:text-blue-300" viewBox="0 0 24 24" fill="currentColor"><path d="M...Z"/></svg></a>
                    </div>
                    <h5 class="text-lg font-medium mb-3 text-blue-200">Newsletter</h5>
                    <form id="newsletter-form" class="space-y-3">
                        <input type="email" id="newsletter-email" placeholder="Enter your email" class="newsletter-input w-full p-2 rounded bg-blue-800 border border-blue-700 focus:outline-none focus:border-blue-500 text-sm" required>
                        <button type="submit" class="newsletter-btn w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-sm transition-colors">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="border-t border-blue-700">
            <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col md:flex-row justify-between items-center text-center md:text-left">
                <div class="text-blue-200 text-sm mb-4 md:mb-0">© <?php echo date("Y"); ?> Unitly. All rights reserved.</div>
                <div class="flex space-x-6 text-sm">
                    <a href="#" class="footer-bottom-link hover:text-white">Privacy Policy</a>
                    <a href="#" class="footer-bottom-link hover:text-white">Terms of Service</a>
                    <a href="#" class="footer-bottom-link hover:text-white">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
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
    </body>
</html>