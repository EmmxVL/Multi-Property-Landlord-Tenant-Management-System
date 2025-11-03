<?php
session_start();
require_once "dbConnect.php";

// ✅ Restrict access to tenants only
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Tenant") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$tenantId = $_SESSION['user_id'];

// ✅ Fetch tenant’s recent messages (3 latest)
$stmt = $db->prepare("
    SELECT m.message_id, m.message, m.date_sent, m.message_status
    FROM message_tbl m
    INNER JOIN lease_tbl l ON m.unit_id = l.unit_id
    WHERE l.user_id = :tenant_id
    ORDER BY m.date_sent DESC
    LIMIT 3
");
$stmt->execute(['tenant_id' => $tenantId]);
$recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Fetch ALL messages for modal
$allStmt = $db->prepare("
    SELECT m.message_id, m.message, m.date_sent, m.message_status
    FROM message_tbl m
    INNER JOIN lease_tbl l ON m.unit_id = l.unit_id
    WHERE l.user_id = :tenant_id
    ORDER BY m.date_sent DESC
");
$allStmt->execute(['tenant_id' => $tenantId]);
$allMessages = $allStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenant Messages | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/styles.css">
  <script src="../assets/script.js" defer></script>
  <script src="../assets/tenant.js" defer></script>
</head>

<?php include '../assets/header.php'; ?>

<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- Main Section -->
  <main class="flex-grow flex items-center justify-center py-12 px-6">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-4xl rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-2xl">

      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-3xl font-extrabold text-blue-900 flex items-center gap-2">
            📩 <span>Recent Messages</span>
          </h2>
          <p class="text-slate-600 text-sm mt-1">Stay updated with your landlord communications.</p>
        </div>
        <a href="dashboard/tenant_dashboard.php"
           class="text-sm font-medium text-blue-700 hover:text-indigo-700 hover:underline transition">
          ← Back to Dashboard
        </a>
      </div>

      <!-- Messages Section -->
      <div class="bg-white/70 rounded-2xl border border-slate-200 shadow-sm p-6">
        <?php if (!empty($recentMessages)): ?>
          <ul class="divide-y divide-slate-200">
            <?php foreach ($recentMessages as $msg): ?>
              <li class="py-4">
                <div class="flex justify-between items-start">
                  <p class="text-slate-800"><?= htmlspecialchars($msg['message']) ?></p>
                  <span class="text-sm text-slate-500">
                    <?= date("M d, Y", strtotime($msg['date_sent'])) ?>
                  </span>
                </div>
                <p class="mt-1 text-xs font-semibold
                  <?= $msg['message_status'] === 'Pending' ? 'text-yellow-600' : 
                      ($msg['message_status'] === 'Completed' ? 'text-green-600' : 'text-red-600') ?>">
                  <?= htmlspecialchars($msg['message_status']) ?>
                </p>
              </li>
            <?php endforeach; ?>
          </ul>

          <?php if (count($allMessages) > 3): ?>
            <div class="text-center pt-5">
              <button id="viewAllBtn"
                      class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
                View all <?= count($allMessages) ?> messages →
              </button>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <p class="text-center text-slate-500 italic">No messages yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- MODAL -->
  <div id="messageModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-xl w-11/12 md:w-3/4 lg:w-1/2 max-h-[85vh] overflow-y-auto p-8 relative border border-slate-200">
      <button id="closeModal"
              class="absolute top-3 right-4 text-slate-500 hover:text-slate-700 text-2xl font-bold">&times;</button>
      <h3 class="text-2xl font-bold text-blue-900 mb-4 flex items-center gap-2">
        💬 <span>All Messages</span>
      </h3>
      <ul class="divide-y divide-slate-200">
        <?php foreach ($allMessages as $msg): ?>
          <li class="py-4">
            <div class="flex justify-between items-start">
              <p class="text-slate-800"><?= htmlspecialchars($msg['message']) ?></p>
              <span class="text-sm text-slate-500">
                <?= date("M d, Y", strtotime($msg['date_sent'])) ?>
              </span>
            </div>
            <p class="mt-1 text-xs font-semibold
              <?= $msg['message_status'] === 'Pending' ? 'text-yellow-600' :
                  ($msg['message_status'] === 'Completed' ? 'text-green-600' : 'text-red-600') ?>">
              <?= htmlspecialchars($msg['message_status']) ?>
            </p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Script -->
  <script>
    const viewAllBtn = document.getElementById('viewAllBtn');
    const modal = document.getElementById('messageModal');
    const closeModal = document.getElementById('closeModal');

    if (viewAllBtn) {
      viewAllBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    }
    closeModal.addEventListener('click', () => modal.classList.add('hidden'));
    window.addEventListener('click', (e) => {
      if (e.target === modal) modal.classList.add('hidden');
    });
  </script>

  <?php include '../assets/footer.php'; ?>
</body>
</html>
