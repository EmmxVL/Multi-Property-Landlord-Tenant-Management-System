<header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">U</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Unitly <?php echo htmlspecialchars($_SESSION['role_name'] ?? ''); ?></h1>
                    <p class="text-xs text-slate-500">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>!</p>
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