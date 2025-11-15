<?php
session_start();
$signupError = $_SESSION['signup_error'] ?? null;
$signupSuccess = $_SESSION['signup_success'] ?? null;
unset($_SESSION['signup_error'], $_SESSION['signup_success']);

// To keep form data if a validation error happens
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

// *** NEW: Fetch available units ***
require_once "dbConnect.php"; 
$availableUnits = [];
try {
    $database = new Database();
    $db = $database->getConnection();
    // Select all units that are NOT part of an active lease
    $stmt = $db->query("
        SELECT u.unit_id, u.unit_name, p.property_name
        FROM unit_tbl u
        JOIN property_tbl p ON u.property_id = p.property_id
        WHERE u.unit_id NOT IN (
            SELECT la.unit_id FROM lease_tbl la WHERE la.lease_status = 'Active'
        )
        ORDER BY p.property_name, u.unit_name
    ");
    $availableUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // If this fails, the dropdown will just be empty
    $signupError = "Could not load available units: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Unitly | Apply</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Corrected path to photos/logo.png from within PHP/ folder -->
  <link rel="icon" type="image/png" href="../photos/logo.png"> 
  <style>
    body {
      --x: 50%; --y: 50%;
      background: radial-gradient(circle at var(--x) var(--y), #ffffff 0%, #f0f9ff 6%, #3b82f6 100%);
      background-attachment: fixed;
      font-family: 'Inter', sans-serif;
    }
    .form-section { display: none; }
    .file-input {
        background-color: white;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        padding: 0.6rem 0.75rem;
    }
    .file-input::file-selector-button {
        background-color: #e2e8f0;
        border: 0;
        border-radius: 0.5rem;
        color: #334155;
        font-weight: 500;
        margin-right: 0.75rem;
        padding: 0.25rem 0.75rem;
    }
    .file-input::file-selector-button:hover {
        background-color: #cbd5e1;
    }
  </style>
</head>
<body class="text-slate-800" onmousemove="document.body.style.setProperty('--x', (event.clientX / window.innerWidth * 100) + '%'); document.body.style.setProperty('--y', (event.clientY / window.innerHeight * 100) + '%');">
  
  <main class="flex items-center justify-center min-h-screen py-12 px-4">
    <div class="w-full max-w-2xl">
      <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-lg border border-slate-200 p-8">
        
        <div class="flex justify-center mb-5">
          <!-- Corrected path to photos/logo.png from within PHP/ folder -->
          <img src="../photos/logo.png" alt="Unitly Logo" class="h-20 w-20 rounded-full shadow-md ring-4 ring-blue-100">
        </div>
        <h2 class="text-2xl font-bold text-center text-blue-900 mb-2">
          Create Your <span class="text-blue-700">Account</span>
        </h2>
        <p class="text-center text-slate-500 text-sm mb-6">Apply as a Landlord or Tenant.</p>

        <!-- action="signupHandler.php" is correct because both files are in the PHP/ folder -->
        <form id="signupForm" method="POST" action="signupHandler.php" class="space-y-4" enctype="multipart/form-data">
          
          <!-- Basic Information -->
          <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">1. Basic Information</legend>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
              <input type="text" name="full_name" placeholder="Juan Dela Cruz" required value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
              <input type="tel" name="phone" maxlength="11" placeholder="09xxxxxxxxx" required value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
              <input type="password" name="password" required placeholder="••••••••"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
              <input type="password" name="confirm_password" required placeholder="••••••••"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
          </fieldset>

          <!-- Role Selection -->
          <fieldset class="pt-4">
            <legend class="text-lg font-semibold text-slate-700 mb-2">2. I am applying as a...</legend>
            <div class="flex gap-4">
              <label class="flex-1 flex items-center p-4 border border-slate-300 rounded-xl cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                <input type="radio" id="roleLandlord" name="role" value="landlord" class="w-5 h-5 text-blue-600 focus:ring-blue-500" <?php if (isset($old['role']) && $old['role'] == 'landlord') echo 'checked'; ?>>
                <span class="ml-3 font-medium text-slate-800">Landlord</span>
              </label>
              <label class="flex-1 flex items-center p-4 border border-slate-300 rounded-xl cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500">
                <input type="radio" id="roleTenant" name="role" value="tenant" class="w-5 h-5 text-blue-600 focus:ring-blue-500" <?php if (isset($old['role']) && $old['role'] == 'tenant') echo 'checked'; ?>>
                <span class="ml-3 font-medium text-slate-800">Tenant</span>
              </label>
            </div>
          </fieldset>

          <!-- Landlord Fields (Dynamic) -->
          <fieldset id="landlordFields" class="form-section grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
            <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">3. Landlord Information</legend>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
              <input type="number" name="landlord_age" placeholder="25" min="18"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Occupation</label>
              <input type="text" name="landlord_occupation" placeholder="e.g., Business Owner"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="col-span-full">
              <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
              <input type="text" name="landlord_address" placeholder="123 Main St, Barangay, City"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <p class="col-span-full text-sm font-medium text-slate-700 mb-1 -mt-2">Please upload the following documents (PDF or Image):</p>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Land Title</label>
              <input type="file" name="land_title" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Building Permit</label>
              <input type="file" name="building_permit" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Business Permit</label>
              <input type="file" name="business_permit" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Mayor's Permit</label>
              <input type="file" name="mayors_permit" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Fire Safety Permit</label>
              <input type="file" name="fire_safety_permit" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Barangay Certificate</label>
              <input type="file" name="barangay_cert" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Occupancy Permit</label>
              <input type="file" name="occupancy_permit" class="w-full file-input">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Sanitary Permit</label>
              <input type="file" name="sanitary_permit" class="w-full file-input">
            </div>
            <div class="col-span-full">
              <label class="block text-sm font-medium text-slate-700 mb-1">DTI Permit</label>
              <input type="file" name="dti_permit" class="w-full file-input">
            </div>
          </fieldset>
          
          <!-- Tenant Fields (Dynamic) -->
          <fieldset id="tenantFields" class="form-section grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
            <legend class="text-lg font-semibold text-slate-700 mb-2 col-span-full">3. Tenant Information</legend>
            
            <!-- *** NEW: Unit Selection *** -->
            <div class="col-span-full">
                <label class="block text-sm font-medium text-slate-700 mb-1">Which unit are you applying for?</label>
                <select name="requested_unit_id" id="requested_unit_id" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Select a unit...</option>
                    <?php if (empty($availableUnits)): ?>
                        <option value="" disabled>No units are currently available for application.</option>
                    <?php else: ?>
                        <?php foreach ($availableUnits as $unit): ?>
                            <option value="<?= $unit['unit_id'] ?>" <?php if (isset($old['requested_unit_id']) && $old['requested_unit_id'] == $unit['unit_id']) echo 'selected'; ?>>
                                <?= htmlspecialchars($unit['unit_name'] . ' (' . $unit['property_name'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Personal Info -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Birthdate</label>
              <input type="date" name="tenant_birthdate"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
              <select name="tenant_gender" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">Select gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
              <input type="email" name="tenant_email" placeholder="juan@example.com"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Your Photo (for profile)</label>
                <input type="file" name="tenant_photo" class="w-full file-input">
            </div>
            <!-- ID Info -->
            <legend class="text-md font-semibold text-slate-700 mt-2 col-span-full">Identification</legend>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">ID Type</label>
              <input type="text" name="tenant_id_type" placeholder="e.g., Driver's License, SSS"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">ID Number</label>
              <input type="text" name="tenant_id_number" placeholder="123-456-789"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="col-span-full">
              <label class="block text-sm font-medium text-slate-700 mb-1">Photo of ID</label>
              <input type="file" name="tenant_id_photo" class="w-full file-input">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Birth Certificate (Optional)</label>
                <input type="file" name="tenant_birth_certificate" class="w-full file-input">
            </div>
             <!-- Employment Info -->
            <legend class="text-md font-semibold text-slate-700 mt-2 col-span-full">Employment & Rent</legend>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Occupation</label>
              <input type="text" name="tenant_occupation" placeholder="e.g., Software Engineer"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Employer Name</label>
              <input type="text" name="tenant_employer_name" placeholder="ABC Company"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Income (PHP)</label>
              <input type="number" name="tenant_monthly_income" placeholder="30000" step="1000"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Proof of Income (e.g., Payslip)</label>
                <input type="file" name="tenant_proof_of_income" class="w-full file-input">
            </div>
            
            <div class="col-span-full">
                <label class="block text-sm font-medium text-slate-700 mb-1">Expected Monthly Rent (PHP)</label>
                <input type="number" name="tenant_monthly_rent" placeholder="10000" step="500"
                    class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            
            <!-- Emergency Contact -->
            <legend class="text-md font-semibold text-slate-700 mt-2 col-span-full">Emergency Contact</legend>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
              <input type="text" name="tenant_emergency_name"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Contact Number</label>
              <input type="tel" name="tenant_emergency_contact"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="col-span-full">
              <label class="block text-sm font-medium text-slate-700 mb-1">Relationship</label>
              <input type="text" name="tenant_relationship" placeholder="e.g., Mother, Spouse"
                class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
          </fieldset>

          <!-- Submission -->
          <div class="pt-6">
            <button type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl shadow-md transition-all duration-300 transform hover:scale-105">
              Submit Application
            </button>
          </div>
        </form>

        <div class="text-center mt-6">
          <!-- This link now points UP one level to the root folder -->
          <a href="../login_page_user.php" class="text-sm text-slate-600 hover:text-blue-700 hover:underline">
            Already have an account? Log In
          </a>
        </div>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const roleRadios = document.querySelectorAll('input[name="role"]');
      const landlordFields = document.getElementById('landlordFields');
      const tenantFields = document.getElementById('tenantFields');
      const form = document.getElementById('signupForm');
      // *** NEW: Get the unit dropdown ***
      const unitSelect = document.getElementById('requested_unit_id');

      function toggleSections() {
        const selectedRole = document.querySelector('input[name="role"]:checked');
        if (!selectedRole) {
          landlordFields.style.display = 'none';
          tenantFields.style.display = 'none';
          return;
        }

        if (selectedRole.value === 'landlord') {
          landlordFields.style.display = 'grid';
          tenantFields.style.display = 'none';
          
          // Set required for landlord fields (except files)
          landlordFields.querySelectorAll('input[type="file"]').forEach(el => el.required = false);
          landlordFields.querySelectorAll('input[type="text"], input[type="number"]').forEach(el => {
            const label = el.closest('div').querySelector('label');
            const isOptional = label && label.innerText.includes('(Optional)');
            el.required = !isOptional;
          });

          // *** NEW: Un-require the unit select ***
          if (unitSelect) unitSelect.required = false;
          // Un-require tenant fields
          tenantFields.querySelectorAll('input, select').forEach(el => el.required = false);


        } else if (selectedRole.value === 'tenant') {
          landlordFields.style.display = 'none';
          tenantFields.style.display = 'grid';
          // *** NEW: Require the unit select ***
          if (unitSelect) unitSelect.required = true;
          
          // Set required for tenant fields (except optionals)
          tenantFields.querySelectorAll('input, select').forEach(el => {
            // Don't override the unit select we just set
            if (el.id === 'requested_unit_id') return;
            
            const label = el.closest('div').querySelector('label');
            const isOptional = label && label.innerText.includes('(Optional)');
            el.required = !isOptional;
          });
          // Ensure file inputs are not required unless specified
          tenantFields.querySelectorAll('input[type="file"]').forEach(el => {
             const label = el.closest('div').querySelector('label');
             const isOptional = label && label.innerText.includes('(Optional)');
             el.required = !isOptional;
          });
          // Unset required for landlord fields
          landlordFields.querySelectorAll('input, select').forEach(el => el.required = false);
        }
      }

      roleRadios.forEach(radio => {
        radio.addEventListener('change', toggleSections);
      });

      // Initial check in case of form resubmission
      toggleSections();
    });
  </script>

  <?php if (!empty($signupError)): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Application Failed',
      text: <?= json_encode($signupError); ?>,
      confirmButtonColor: '#2563eb'
    });
  </script>
  <?php endif; ?>

  <?php if (!empty($signupSuccess)): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Application Submitted!',
      text: <?= json_encode($signupSuccess); ?>,
      confirmButtonColor: '#2563eb'
    });
  </script>
  <?php endif; ?>

</body>
</html>