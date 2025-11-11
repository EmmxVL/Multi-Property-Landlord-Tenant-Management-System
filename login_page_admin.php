<?php

session_start();

// *** THE FIX ***
// Check if user is logged in AND the role is set
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Only redirect if they are already an Admin
    if ($_SESSION['role'] == 'Admin') {
        header("Location: PHP/dashboard/admin_dashboard.php");
        exit();
    }
}

$loginError = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']); 

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unitly | Admin Login</title>
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
        #dbeafe 6%,
        #1e3a8a 100%
      );
      background-attachment: fixed;
      transition: background 0.2s ease-out;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }
    @keyframes riseUp {
      0% { opacity: 0; transform: translateY(40px) scale(0.98); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-rise { animation: riseUp 0.6s ease-out 0.2s forwards; opacity: 0; }
  </style>
</head>
<body class="flex flex-col min-h-screen text-slate-800" onmousemove="document.body.style.setProperty('--x', (event.clientX / window.innerWidth * 100) + '%'); document.body.style.setProperty('--y', (event.clientY / window.innerHeight * 100) + '%');">
  <main class="flex flex-grow items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
      <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-lg border border-slate-200 p-8 animate-rise">
        <div class="flex justify-center mb-5">
          <img src="photos/logo.png" alt="Unitly Logo" class="h-20 w-20 rounded-full shadow-md ring-4 ring-blue-100">
        </div>
        <h2 class="text-2xl font-bold text-center text-blue-900 mb-2">
          Admin <span class="text-blue-700">Login</span>
        </h2>
        <p class="text-center text-slate-500 text-sm mb-6">Sign in to manage system-wide settings.</p>

        <form method="POST" action="PHP/login_admin.php" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">📱 Phone Number</label>
            <input type="tel" name="phone" maxlength="11" placeholder="09xxxxxxxxx" required
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">🔒 Password</label>
            <input type="password" name="password" required placeholder="••••••••"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
          </div>
          
          <div class="text-right text-sm">
            <a href="forgotPassword.php?role=Admin" class="text-blue-600 hover:underline">Forgot Password?</a>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl mt-5 shadow-md transition-all duration-300 transform hover:scale-105">
            Log In
          </button>
        </form>
        
      </div>
    </div>
  </main>

  <?php if (!empty($loginError)): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Login Failed',
      text: <?= json_encode($loginError); ?>,
      confirmButtonColor: '#2563eb'
    });
  </script>
  <?php endif; ?>
</body>
</html>