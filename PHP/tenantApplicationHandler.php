<?php
session_start();
require_once "dbConnect.php";
require_once "leaseManager.php";
require_once "paymentManager.php"; // *** Include Lease & Payment Managers ***

// 1. Check Landlord Auth
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    $_SESSION['landlord_error'] = "Unauthorized action.";
    header("Location: dashboard/landlord_dashboard.php");
    exit;
}

$landlordId = (int)$_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
$leaseManager = new LeaseManager($db); 
$paymentManager = new PaymentManager($db); // *** Instantiate Managers ***

// --- Check for GET request (Reject Action) ---
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['action']) && $_GET['action'] === 'reject') {
    $tenantUserId = (int)($_GET['id'] ?? 0);
    if ($tenantUserId <= 0) {
        $_SESSION['landlord_error'] = "Invalid user ID.";
        header("Location: landlordApplications.php");
        exit;
    }

    try {
        // --- REJECT ACTION ---
        $db->beginTransaction();

        // 1. Get file paths
        $stmt = $db->prepare("SELECT * FROM tenant_info_tbl WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $tenantUserId]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Delete files
        if ($info) {
            foreach ($info as $key => $value) {
                if (is_string($value) && strpos($value, 'uploads/tenant_docs/') === 0) {
                    $fullPath = "../" . $value;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
        }
        
        // 3. Delete directory
        $userDir = "../uploads/tenant_docs/user_{$tenantUserId}";
        if (is_dir($userDir)) {
            @rmdir($userDir);
        }

        // 4. Delete user from database (cascading delete will handle info/role)
        // We also check that the landlord_id is NULL to make sure we're not deleting an assigned tenant
        $stmt = $db->prepare("DELETE FROM user_tbl WHERE user_id = :user_id AND status = 'pending' AND landlord_id IS NULL");
        $stmt->execute([':user_id' => $tenantUserId]);
        
        $db->commit();
        $_SESSION['landlord_success'] = "Application rejected and all data has been permanently deleted.";

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['landlord_error'] = "Error rejecting application: " . $e->getMessage();
    }
    
    header("Location: landlordApplications.php");
    exit;
}

// --- Check for POST request (Approve & Create Lease Action) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'approve') {
    
    // 2. Validate POST Input
    $tenantUserId = (int)($_POST['user_id'] ?? 0);
    $unitId = (int)($_POST['unit_id'] ?? 0); // This now comes from the form
    $startDate = $_POST['lease_start_date'] ?? '';
    // End date is no longer collected from this form
    $monthlyRent = (float)($_POST['monthly_rent'] ?? 0);
    $initialPayment = (float)($_POST['initial_payment'] ?? 0);


    if ($tenantUserId <= 0 || $unitId <= 0 || empty($startDate) || $monthlyRent <= 0) {
        $_SESSION['landlord_error'] = "Invalid data. Please fill out all required lease fields (Start Date, Monthly Rent).";
        header("Location: viewTenantApplication.php?id=" . $tenantUserId);
        exit;
    }

    try {
        // --- APPROVE & CREATE LEASE ACTION ---
        $db->beginTransaction();

        // 1. Approve the user and assign them to this landlord
        $stmt = $db->prepare("
            UPDATE user_tbl 
            SET status = 'approved', landlord_id = :landlord_id 
            WHERE user_id = :user_id 
              AND status = 'pending' 
              AND landlord_id IS NULL
        ");
        $stmt->execute([
            ':landlord_id' => $landlordId,
            ':user_id' => $tenantUserId
        ]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Could not approve user. They may have been processed already.");
        }

        // 2. Calculate initial balance
        // Balance = What they *owe*. So it's Rent - Payment.
        // A payment of 15k on a 5k rent = -10k balance (credit).
        $balance = $monthlyRent - $initialPayment;

        // 3. Create the lease, passing NULL for end date and 'Active' for status
        $newLeaseId = $leaseManager->createLease($tenantUserId, $unitId, $startDate, null, $balance, 'Active');
        
        if ($newLeaseId <= 0) {
            // LeaseManager already set an error message (e.g., "Unit already has active lease")
            throw new Exception($_SESSION['landlord_error'] ?? "Failed to create lease.");
        }

        // 4. If an initial payment was made, log it
        if ($initialPayment > 0) {
            $paymentManager->createPayment(
                $newLeaseId,
                $initialPayment,
                $startDate, // Payment date is same as start date
                'Confirmed',
                null, // No receipt, as it's a direct entry
                'Initial payment (e.g., advance/deposit)',
                $balance // The balance *after* this payment
            );
        }

        // 5. Commit all changes
        $db->commit();
        $_SESSION['landlord_success'] = "Application approved and lease created successfully!";
        header("Location: landlordApplications.php"); // Redirect to the applications list
        exit;

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['landlord_error'] = "Error: " . $e->getMessage();
        header("Location: viewTenantApplication.php?id=" . $tenantUserId);
        exit;
    }
}

// Fallback redirect if neither GET nor POST matches
$_SESSION['landlord_error'] = "Invalid action.";
header("Location: landlordApplications.php");
exit;
?>