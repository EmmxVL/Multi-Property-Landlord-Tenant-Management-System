<?php
session_start();
$loginError = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unitly | Login</title>
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
    0% {
      opacity: 0;
      transform: translateY(40px) scale(0.98);
    }
    100% {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  .animate-rise {
    animation: riseUp 0.6s ease-out 0.2s forwards;
  }
</style>
</head>
<script>
   document.addEventListener("DOMContentLoaded", () => {
    const card = document.querySelector(".animate-rise");
    if (card) {
      card.style.animation = "none";
      void card.offsetWidth; // Trigger reflow
      card.style.animation = null;
    }
  });
  document.addEventListener("mousemove", (e) => {
    const x = (e.clientX / window.innerWidth) * 100;
    const y = (e.clientY / window.innerHeight) * 100;
    document.body.style.setProperty("--x", `${x}%`);
    document.body.style.setProperty("--y", `${y}%`);
  });
</script>
<body class="font-sans text-slate-800 min-h-screen flex flex-col">
  <!-- Login Section -->
  <main class="flex flex-grow items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
      <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-xl animate-rise">
        <!-- Logo -->
        <div class="flex justify-center mb-5">
          <img src="photos/logo.png" alt="Unitly Logo" class="h-20 w-20 rounded-full shadow-md ring-4 ring-blue-100">
        </div>

        <!-- Title -->
        <h2 class="text-2xl font-bold text-center text-blue-900 mb-2">Welcome to <span class="text-blue-700">Unitly</span></h2>
        <p class="text-center text-slate-500 text-sm mb-6">Sign in to manage your properties efficiently.</p>

        <!-- Login Form -->
        <form method="POST" action="PHP/login.php" class="space-y-4">
          <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">📱 Phone Number</label>
            <input type="tel" id="phone" name="phone" maxlength="11"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder:text-slate-400 transition-all duration-200"
              placeholder="Enter your phone number" required>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">🔒 Password</label>
            <input type="password" id="password" name="password"
              class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none placeholder:text-slate-400 transition-all duration-200"
              placeholder="Enter your password" required>
          </div>

          <div class="flex justify-between items-center mt-2">
            <label class="text-sm text-slate-600 flex items-center gap-2">
              <input type="checkbox" class="rounded border-slate-300 focus:ring-blue-500"> Remember me
            </label>
            <a href="forgotPassword.php" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Forgot Password?</a>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl mt-5 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
            Log In
          </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-6">
          
        </div>
      </div>
    </div>
  </main>
  <!-- SweetAlert for Login Error -->
  <script>
  <?php if (!empty($loginError)): ?>
    Swal.fire({
      icon: 'error',
      title: 'Login Failed',
      text: <?= json_encode($loginError); ?>,
      confirmButtonColor: '#2563eb',
      confirmButtonText: 'Try Again'
    });
  <?php endif; ?>
  </script>

</body>
</html>
