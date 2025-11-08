<?php
$baseURL = dirname($_SERVER['SCRIPT_NAME']) === 'footer/'
    ? '../footer/' 
    : (strpos($_SERVER['SCRIPT_NAME'], '/dashboard/') !== false ? '../../footer/' : '../footer/');
?>
<footer class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white mt-16">
  <div class="max-w-7xl mx-auto px-6 py-14 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-10">
    
    <!-- Logo / Vision -->
    <div>
      <h3 class="text-3xl font-bold mb-4 text-white">Unitly</h3>
      <p class="text-blue-100 text-sm leading-relaxed">
        Revolutionizing property management by connecting landlords and tenants seamlessly through smart solutions.
      </p>
    </div>

    <!-- Contact Info -->
    <div>
      <h4 class="text-xl font-semibold mb-4 text-blue-200">Contact Us</h4>
      <p class="text-blue-100 text-sm">004, Pilahan East, Sabang, Lipa City</p>
      <p class="text-blue-100 text-sm">+63 (906) 581-6503</p>
      <p class="text-blue-100 text-sm">unitlyph@gmail.com</p>
      <p class="text-blue-100 text-sm">www.unitly.com</p>
    </div>

    <!-- Quick Links -->
    <div>
      <h4 class="text-xl font-semibold mb-4 text-blue-200">Quick Links</h4>
      <ul class="space-y-3">
        <li><a href="<?= $baseURL ?>aboutus.php" class="hover:text-blue-300 transition">About Us</a></li>
        <li><a href="<?= $baseURL ?>ourservices.php" class="hover:text-blue-300 transition">Our Services</a></li>
        <li><a href="<?= $baseURL ?>developers.php" class="hover:text-blue-300 transition">Developers</a></li>
      </ul>
    </div>

    <!-- External Links -->
    <div>
      <h4 class="text-xl font-semibold mb-4 text-blue-200">External Links</h4>
      <div class="flex flex-col space-y-3">
        <a href="https://github.com/EmmxVL/Multi-Property-Landlord-Tenant-Management-System" target="_blank" 
           class="flex items-center gap-2 bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded-lg transition">
          <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 0a12 12 0 00-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.4-4-1.4-.5-1.1-1.2-1.4-1.2-1.4-1-.7.1-.7.1-.7 1.1.1 1.7 1.1 1.7 1.1 1 .1 1.8.7 2 .9v-3.1c-2.7.6-3.4-1.3-3.4-1.3-.8-2.1.3-3.4.3-3.4.9-1.2 2.4-1.3 3.4-1.3.5 0 .9.1 1.3.2a4.6 4.6 0 018.1 0c.4-.1.9-.2 1.3-.2 1 0 2.5.1 3.4 1.3 0 0 1.1 1.3.3 3.4 0 0-.7 1.9-3.4 1.3v3.1c.2.2 1 .8 2 .9 0 0 .1 0 .1-.1 0 0 1.1 0 .1.7 0 0-.6.3-1.2 1.4 0 0-.7 2.1-4 1.4v2c0 .3.2.7.8.6A12 12 0 0012 0z"/>
          </svg>
          GitHub
        </a>

        <a href="https://batstateu.edu.ph" target="_blank" 
           class="flex items-center gap-2 bg-red-700 hover:bg-red-600 px-4 py-2 rounded-lg transition">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 20l9-5-9-5-9 5 9 5zm0-10l9-5-9-5-9 5 9 5z"/>
          </svg>
          BatStateU Portal
        </a>
      </div>
    </div>
  </div>

  <div class="border-t border-blue-800 mt-10">
    <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col md:flex-row justify-between items-center text-center text-sm text-blue-200">
      <p>© <?= date("Y") ?> Unitly. All rights reserved.</p>
      <div class="flex space-x-5 mt-3 md:mt-0">
        <a href="<?= $baseURL ?>privacypolicy.php" class="hover:text-white">Privacy Policy</a>
        <a href="<?= $baseURL ?>termsofservice.php" class="hover:text-white">Terms of Service</a>
        <a href="<?= $baseURL ?>cookiepolicy.php" class="hover:text-white">Cookie Policy</a>
      </div>
    </div>
  </div>
</footer>