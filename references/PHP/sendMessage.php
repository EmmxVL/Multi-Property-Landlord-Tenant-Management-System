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
  <title>Send Message</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded-2xl shadow-md">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">📩 Send Message</h1>
    <!-- ✅ Back to Dashboard Button -->
    <a href="dashboard/landlord_dashboard.php"
       class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded font-medium transition">
       ← Back to Dashboard
    </a>
  </div>

  <?php if ($successMsg): ?>
    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($successMsg) ?></div>
  <?php elseif ($errorMsg): ?>
    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($errorMsg) ?></div>
  <?php endif; ?>

  <form method="POST" class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-600 mb-1">Select Tenant</label>
      <select name="tenant_id" class="w-full p-2 border rounded focus:ring focus:ring-indigo-100" required>
        <option value="">-- Select Tenant --</option>
        <option value="all">📢 All Tenants</option>
        <?php foreach ($tenants as $tenant): ?>
          <option value="<?= $tenant['user_id'] ?>"><?= htmlspecialchars($tenant['tenant_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-600 mb-1">Message</label>
      <textarea name="message" rows="4" class="w-full p-3 border rounded focus:ring focus:ring-indigo-100" placeholder="Type your message..."></textarea>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded font-semibold transition">
        Send Message
      </button>
    </div>
  </form>

  <h2 class="text-xl font-semibold mt-10 mb-4 text-gray-700">Recent Messages</h2>
  <?php if (empty($recentMessages)): ?>
    <p class="text-gray-500">No messages sent yet.</p>
  <?php else: ?>
    <?php foreach ($recentMessages as $msg): ?>
      <div class="p-4 border rounded bg-gray-50 mb-2">
        <p class="text-gray-800 font-medium"><?= htmlspecialchars($msg['message']) ?></p>
        <p class="text-sm text-gray-600">Sent to: <?= htmlspecialchars($msg['tenant_name']) ?> on <?= htmlspecialchars($msg['date_sent']) ?></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>