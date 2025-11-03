<?php
ob_start(); // start output buffering early to prevent header errors
session_start();
require_once "PHP/dbConnect.php";
require_once "iprog_sms.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = trim($_POST["phone"]);

    if (empty($phone)) {
        $_SESSION['error'] = "Please enter your phone number.";
        header("Location: forgotPassword.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    // Normalize phone number
    $phone = preg_replace('/[^+0-9]/', '', $phone);
    if (strpos($phone, '+63') === 0) {
        $phone = '0' . substr($phone, 3);
    }

// Check if phone exists
$stmt = $db->prepare("SELECT user_id FROM user_tbl WHERE phone_no = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $result = sendOTP($phone);

    if (
        $result &&
        isset($result["status"]) &&
        strtolower(trim($result["status"])) === "success"
    ) {
        $_SESSION['otp_phone'] = $phone;

        if (ob_get_length()) {
            ob_end_clean();
        }

        header("Location: verify_otp.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to send OTP. Please try again.";
    }
} else {
    $_SESSION['error'] = "No account found with that phone number.";
}

header("Location: forgotPassword.php");
exit;
}

ob_end_flush(); // output any buffered HTML safely
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      --x: 50%;
      --y: 50%;
      background: radial-gradient(
        circle at var(--x) var(--y),
        #ffffff 0%,
        #dbeafe 40%,
        #1e3a8a 100%
      );
      background-attachment: fixed;
      transition: background 0.25s ease-out;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }

    @keyframes rise {
      0% { opacity: 0; transform: translateY(30px) scale(0.97); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .animate-rise {
      animation: rise 0.7s ease-out forwards;
    }
  </style>
</head>

<body class="flex flex-col items-center justify-center min-h-screen text-slate-800">

  <!-- Main Content -->
  <main class="flex-grow flex items-center justify-center py-12 px-4 animate-rise">
    <div class="bg-white/80 backdrop-blur-lg border border-slate-200 w-full max-w-md rounded-3xl shadow-xl p-8 transition-all duration-300 hover:shadow-2xl">

      <!-- Logo -->
      <div class="flex justify-center mb-5">
        <img src="photos/logo.png" alt="Unitly Logo" class="h-20 w-20 rounded-full shadow-md ring-4 ring-blue-100">
      </div>

      <!-- Title -->
      <h2 class="text-3xl font-extrabold text-center text-blue-900 mb-2">Forgot Password?</h2>
      <p class="text-center text-slate-600 text-sm mb-6">Enter your registered phone number, and we’ll send you an OTP to reset your password.</p>

      <!-- SweetAlert PHP logic -->
      <?php if (!empty($_SESSION['error'])): ?>
        <script>
          Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: <?= json_encode($_SESSION['error']); ?>,
            confirmButtonColor: '#2563eb'
          });
        </script>
        <?php unset($_SESSION['error']); ?>
      <?php elseif (!empty($_SESSION['success'])): ?>
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: <?= json_encode($_SESSION['success']); ?>,
            confirmButtonColor: '#2563eb'
          });
        </script>
        <?php unset($_SESSION['success']); ?>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" action="" class="space-y-5 text-left">
        <div>
          <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">📱 Phone Number</label>
          <input type="text" id="phone" name="phone" maxlength="11"
                 class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all text-sm"
                 placeholder="e.g. 09123456789" required
                 oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11);">
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
          🔐 Send OTP
        </button>
      </form>

      <!-- Back to Login -->
      <div class="mt-6 text-sm text-center text-slate-600">
        <p>Remembered your password? 
          <a href="login_page.php" class="text-blue-700 hover:underline font-medium">Log in here</a>
        </p>
      </div>
    </div>
  </main>

  <!-- Cursor-Responsive Gradient Animation -->
  <script>
    let targetX = 50, targetY = 50, currentX = 50, currentY = 50;
    document.addEventListener("mousemove", (e) => {
      targetX = (e.clientX / window.innerWidth) * 100;
      targetY = (e.clientY / window.innerHeight) * 100;
    });
    function animate() {
      currentX += (targetX - currentX) * 0.08;
      currentY += (targetY - currentY) * 0.08;
      document.body.style.setProperty("--x", `${currentX}%`);
      document.body.style.setProperty("--y", `${currentY}%`);
      requestAnimationFrame(animate);
    }
    animate();
  </script>

</body>
</html>
