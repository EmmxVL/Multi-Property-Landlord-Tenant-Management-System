<?php
session_start();
require_once "dbConnect.php";
require_once "messageManager.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Landlord") {
    header("Location: login_page.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$messageManager = new MessageManager($db);
$landlord_id = $_SESSION["user_id"];

// Fetch active tenants
$stmt = $db->prepare("
    SELECT DISTINCT u.user_id, u.full_name AS tenant_name, l.unit_id
    FROM lease_tbl l
    JOIN user_tbl u ON l.user_id = u.user_id
    JOIN unit_tbl un ON l.unit_id = un.unit_id
    JOIN property_tbl p ON un.property_id = p.property_id
    WHERE p.user_id = :landlord_id AND l.lease_status = 'Active'
");
$stmt->execute([':landlord_id' => $landlord_id]);
$tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$successMsg = $errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $message = trim($_POST["message"]);
    $tenantId = $_POST["tenant_id"] ?? '';

    if (empty($message)) {
        $errorMsg = "Please enter a message.";
    } elseif ($tenantId === "all") {
        if ($messageManager->sendBulkMessage($landlord_id, $message)) {
            $successMsg = "Message sent to all tenants.";
        } else {
            $errorMsg = "Failed to send messages.";
        }
    } else {
        $selectedTenant = array_filter($tenants, fn($t) => $t['user_id'] == $tenantId);
        $selectedTenant = reset($selectedTenant);

        if ($selectedTenant && $messageManager->sendMessage($landlord_id, (int)$tenantId, (int)$selectedTenant['unit_id'], $message)) {
            $successMsg = "Message sent successfully.";
        } else {
            $errorMsg = "Failed to send message.";
        }
    }
}

$recentMessages = $messageManager->getRecentMessagesByLandlord($landlord_id, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Message | Unitly</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<?php include '../assets/header.php'; ?>
<body class="bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 min-h-screen font-sans flex flex-col">

  <!-- Main -->
  <main class="flex-grow flex items-center justify-center py-10 px-4">
    <div class="bg-white/80 backdrop-blur-md w-full max-w-3xl rounded-3xl shadow-lg border border-slate-200 p-8 transition-all duration-300 hover:shadow-xl">
      
      <!-- Header with Back Button -->
      <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-extrabold text-blue-900 tracking-tight">Send a Message to Your Tenants</h2>
         <a href="dashboard/landlord_dashboard.php" 
         class="text-sm font-medium text-orange-600 hover:text-orange-700 hover:underline transition-all duration-200">
         ← Back to Dashboard
      </a>
        </a>
      </div>

      <!-- SweetAlert Messages -->
      <?php if (!empty($successMsg)): ?>
        <script>
          Swal.fire({
            icon: 'success',
            title: 'Message Sent!',
            text: <?= json_encode($successMsg) ?>,
            confirmButtonColor: '#2563eb',
            background: '#f0f9ff'
          });
        </script>
      <?php elseif (!empty($errorMsg)): ?>
        <script>
          Swal.fire({
            icon: 'error',
            title: 'Something went wrong',
            text: <?= json_encode($errorMsg) ?>,
            confirmButtonColor: '#2563eb'
          });
        </script>
      <?php endif; ?>

      <!-- Message Form -->
      <form method="POST" class="space-y-6">
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Select Property</label>
          <select name="property_id"
                  class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all"
                  required>
            <option value="">-- Choose Property --</option>
            <?php foreach ($properties as $property): ?>
              <option value="<?= $property['property_id'] ?>"><?= htmlspecialchars($property['property_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-2">Your Message</label>
          <textarea name="message"
                    rows="5"
                    class="w-full border border-slate-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none resize-none"
                    placeholder="Type your message here..." required></textarea>
        </div>

        <div class="flex justify-end">
          <button type="submit"
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
            🚀 Send Message
          </button>
        </div>
      </form>

      <!-- Divider -->
      <div class="relative my-10">
        <hr class="border-slate-200">
        <span class="absolute left-1/2 -translate-x-1/2 -top-3 bg-white px-4 text-slate-400 text-sm">Recent Messages</span>
      </div>

      <!-- Recent Messages -->
      <?php if (empty($recentMessages)): ?>
        <p class="text-center text-slate-500 italic">You haven’t sent any messages yet.</p>
      <?php else: ?>
        <div class="max-h-72 overflow-y-auto space-y-4 pr-1">
          <?php foreach ($recentMessages as $msg): ?>
            <!-- Tenant Message -->
            <div class="flex flex-col items-end">
              <div class="max-w-[80%] bg-blue-600 text-white rounded-2xl rounded-br-sm px-4 py-2 shadow-sm">
                <p class="text-sm"><?= htmlspecialchars($msg['message']) ?></p>
              </div>
              <span class="text-xs text-slate-500 mt-1">Sent on <?= htmlspecialchars($msg['date_sent']) ?></span>
            </div>
            <!-- Landlord Reply -->
            <?php if (!empty($msg['landlord_reply'])): ?>
              <div class="flex flex-col items-start">
                <div class="max-w-[80%] bg-gray-100 text-slate-800 rounded-2xl rounded-bl-sm px-4 py-2 shadow-sm">
                  <p class="text-sm"><?= htmlspecialchars($msg['landlord_reply']) ?></p>
                </div>
                <span class="text-xs text-slate-500 mt-1">Landlord’s reply</span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <!-- Footer -->
  <?php include '../assets/footer.php'; ?>
</body>

</html>
