<?php
session_start();

// ✅ Save role before destroying the session
$role = $_SESSION['role'] ?? null;

// ✅ Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// ✅ Redirect based on role
if ($role === 'Admin') {
    header("Location: ../login_page_admin.php");
} else {
    header("Location: ../login_page_user.php");
}
exit;
?>