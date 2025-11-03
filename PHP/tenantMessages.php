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
<title>Tenant Messages</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="bg-white shadow-md rounded-lg p-5">
    <h2 class="text-xl font-semibold text-gray-800 mb-3">📩 Recent Messages</h2>

    <?php if (!empty($recentMessages)): ?>
        <ul class="divide-y divide-gray-200">
            <?php foreach ($recentMessages as $msg): ?>
                <li class="py-3">
                    <div class="flex justify-between">
                        <p class="text-gray-700"><?= htmlspecialchars($msg['message']) ?></p>
                        <span class="text-sm text-gray-500"><?= date("M d, Y", strtotime($msg['date_sent'])) ?></span>
                    </div>
                    <p class="text-xs 
                        <?= $msg['message_status'] === 'Pending' ? 'text-yellow-500' : 
                            ($msg['message_status'] === 'Completed' ? 'text-green-500' : 'text-red-500') ?>">
                        <?= htmlspecialchars($msg['message_status']) ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (count($allMessages) > 3): ?>
            <div class="text-center pt-3">
                <button id="viewAllBtn" 
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View all <?= count($allMessages) ?> messages →
                </button>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="text-gray-500">No messages yet.</p>
    <?php endif; ?>
</div>

<!-- ✅ MODAL FOR ALL MESSAGES -->
<div id="messageModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-2/3 max-h-[80vh] overflow-y-auto p-6 relative">
        <button id="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
        <h3 class="text-lg font-semibold mb-4">All Messages</h3>
        <ul class="divide-y divide-gray-200">
            <?php foreach ($allMessages as $msg): ?>
                <li class="py-3">
                    <div class="flex justify-between">
                        <p class="text-gray-800"><?= htmlspecialchars($msg['message']) ?></p>
                        <span class="text-sm text-gray-500"><?= date("M d, Y", strtotime($msg['date_sent'])) ?></span>
                    </div>
                    <p class="text-xs 
                        <?= $msg['message_status'] === 'Pending' ? 'text-yellow-500' : 
                            ($msg['message_status'] === 'Completed' ? 'text-green-500' : 'text-red-500') ?>">
                        <?= htmlspecialchars($msg['message_status']) ?>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<script>
// ✅ Modal toggle
const viewAllBtn = document.getElementById('viewAllBtn');
const modal = document.getElementById('messageModal');
const closeModal = document.getElementById('closeModal');

if (viewAllBtn) {
    viewAllBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });
}
closeModal.addEventListener('click', () => {
    modal.classList.add('hidden');
});
window.addEventListener('click', (e) => {
    if (e.target === modal) modal.classList.add('hidden');
});
</script>

</body>
</html>
