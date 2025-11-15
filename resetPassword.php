<?php
session_start();
require_once "PHP/dbConnect.php";

if (!isset($_SESSION['verified_phone'])) {
    header("Location: forgotPassword.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPass = trim($_POST["password"]);
    $confirmPass = trim($_POST["confirm_password"]);

    if ($newPass !== $confirmPass) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: resetPassword.php");
        exit;
    }

    $database = new Database();
    $db = $database->getConnection();

    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $phone = $_SESSION['verified_phone'];

    $update = $db->prepare("UPDATE user_tbl SET password = ? WHERE phone_no = ?");
    $update->execute([$hash, $phone]);

    unset($_SESSION['verified_phone']);
    $_SESSION['success'] = "Password successfully reset! Please log in.";

    header("Location: login_page_user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password | Unitly</title>
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
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
    }

    @keyframes riseUp {
      0% { opacity: 0; transform: translateY(30px) scale(0.97); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .animate-rise {
      animation: riseUp 0.6s ease-out forwards;
    }
  </style>
</head>

<?php include 'assets/header.php'; ?>

<body class="flex items-center justify-center min-h-screen text-slate-800">
  <main class="w-full max-w-md px-6 animate-rise">
    <div class="bg-white/80 backdrop-blur-lg border border-slate-200 shadow-xl rounded-3xl p-8 hover:shadow-2xl transition-all duration-300">
      
      <!-- Icon / Title -->
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-md">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.104.896-2 2-2s2 .896 2 2v1h1a3 3 0 010 6h-6a3 3 0 010-6h1v-1zm0 0V7m0 0a5 5 0 015 5v1h1a3 3 0 110 6h-6a3 3 0 010-6h1v-1a5 5 0 015-5z"/>
          </svg>
        </div>
        <h2 class="text-3xl font-extrabold text-blue-900 tracking-tight">Reset Your Password</h2>
        <p class="text-slate-600 text-sm mt-2">Create a new password to secure your account.</p>
      </div>

      <!-- Error Handling -->
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
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" action="" class="space-y-5">
        <div>
          <label class="block mb-1 text-sm font-medium text-slate-700">New Password</label>
          <input type="password" name="password" placeholder="Enter new password"
                 class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm" required>
        </div>

        <div>
          <label class="block mb-1 text-sm font-medium text-slate-700">Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Confirm new password"
                 class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm" required>
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
          🔐 Reset Password
        </button>
      </form>

      <!-- Back to Login -->
      <div class="mt-6 text-center">
        <a href="login_page.php" class="text-sm text-blue-700 hover:underline transition">← Back to Login</a>
      </div>
    </div>
  </main>

  <?php include 'assets/footer.php'; ?>

  <!-- Dynamic Background -->
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
