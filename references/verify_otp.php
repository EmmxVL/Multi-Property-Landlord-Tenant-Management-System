<?php
session_start();
require_once "iprog_sms.php";

if (!isset($_SESSION['otp_phone'])) {
    header("Location: forgotPassword.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $otp = trim($_POST["otp"]);
    $phone = $_SESSION['otp_phone'];

    $result = verifyOTP($phone, $otp);

    if ($result && $result["status"] === "success") {
        $_SESSION['verified_phone'] = $phone;
        unset($_SESSION['otp_phone']);
        header("Location: resetPassword.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid or expired OTP.";
        header("Location: verify_otp.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Unitly | Verify OTP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="icon" type="image/png" href="photos/logo.png">
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
      transition: background 0.2s ease-out;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }

    @keyframes riseUp {
      0% { opacity: 0; transform: translateY(30px) scale(0.97); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .animate-rise {
      animation: riseUp 0.5s ease-out forwards;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen text-slate-800">
  <main class="w-full max-w-md px-6 animate-rise">
    <div class="bg-white/80 backdrop-blur-lg border border-slate-200 shadow-xl rounded-3xl p-8 hover:shadow-2xl transition-all duration-300">
      
      <!-- Logo -->
      <div class="flex justify-center mb-5">
        <img src="photos/logo.png" alt="Unitly Logo" class="h-16 w-16 rounded-full shadow-md ring-4 ring-blue-100">
      </div>

      <!-- Title -->
      <h2 class="text-2xl font-extrabold text-center text-blue-900 mb-2">Verify Your OTP</h2>
      <p class="text-center text-slate-500 text-sm mb-6">Enter the 6-digit code sent to your registered phone number.</p>

      <!-- Alert -->
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="bg-red-100 text-red-700 border border-red-300 p-3 rounded-lg mb-4 text-sm text-center">
          <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" action="" class="space-y-4">
        <div>
          <label for="otp" class="block text-sm font-medium text-slate-700 mb-1">OTP Code</label>
          <input type="text" id="otp" name="otp" maxlength="6"
            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-center text-lg tracking-widest font-semibold focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all"
            placeholder="123456" required>
        </div>

        <button type="submit"
          class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
          ✅ Verify OTP
        </button>
      </form>

      <!-- Back Button -->
      <div class="mt-5 text-center">
        <button type="button" onclick="window.location.href='login_page.php'"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium transition">
          ← Back to Login
        </button>
      </div>
    </div>
  </main>

  <!-- Interactive Background Script -->
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
