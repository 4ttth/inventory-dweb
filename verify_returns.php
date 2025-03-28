<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    exit("Access Denied!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_transaction_id'])) {
  try {
      $transactionID = $_POST['verify_transaction_id'];
      $verifierID = $_SESSION['user_id'];
      
      $conn->begin_transaction();
      
      // Update transaction
      $conn->query("UPDATE `transaction` 
                   SET verified = 'Verified', 
                       verified_by = '$verifierID' 
                   WHERE TransactionID = '$transactionID'");
      
      // Update inventory
      $conn->query("UPDATE item i
                   JOIN `transaction` t ON i.ItemID = t.ItemID
                   SET i.Quantity = i.Quantity + t.Quantity
                   WHERE t.TransactionID = '$transactionID'");
      
      $conn->commit();
      header("Location: verify_returns.php");
      exit();
  } catch (Exception $e) {
      $conn->rollback();
      die("Error processing verification: " . $e->getMessage());
  }
}

$returns = $conn->query("SELECT t.*, i.ItemName FROM `transaction` t 
    LEFT JOIN item i ON t.ItemID = i.ItemID 
    WHERE t.TransactionType = 'Return'");
    
$stmt = $conn->prepare("INSERT INTO `transaction` (ItemID, TransactionType, Quantity, Date, Time, UserID, return_photo, verified) VALUES (?, 'Return', ?, CURDATE(), CURTIME(), ?, ?, 'Not Verified')");
$stmt->bind_param("iiiss", $itemID, $quantity, $userID, $photoPath);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <title>Verify Returned Items</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Modal styles: hidden by default */
    #imgModal {
      display: none;
    }
    #imgModal.active {
      display: flex;
    }
    .modal-img {
      max-width: 90%;
      max-height: 80vh;
      animation: fadeIn 0.3s ease-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
  </style>
  <script>
    function openModal(elem) {
      const src = elem.getAttribute('src');
      const transId = elem.getAttribute('data-transaction-id');
      document.getElementById('modalImg').src = src;
      document.getElementById('modalTransactionID').value = transId;
      document.getElementById('imgModal').classList.add('active');
    }
    function closeModal() {
      document.getElementById('imgModal').classList.remove('active');
    }
  </script>
</head>
<body>
  
  <div class="container mx-auto px-4 py-8">
    <?php include 'nav.php'; ?>
    <h2 class="text-2xl font-bold mb-4">Verify Returned Items</h2>
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-4">ID</th>
          <th class="px-6 py-4">Item Name</th>
          <th class="px-6 py-4">Quantity</th>
          <th class="px-6 py-4">Photo</th>
          <th class="px-6 py-4">Status</th>
          <th class="px-6 py-4">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php while($row = $returns->fetch_assoc()): ?>
        <tr>
          <td class="px-6 py-4"><?= $row['TransactionID'] ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($row['ItemName']) ?></td>
          <td class="px-6 py-4"><?= $row['Quantity'] ?></td>
          <td class="px-6 py-4">
            <?php if($row['return_photo']): ?>
              <img src="<?= htmlspecialchars($row['return_photo']) ?>" alt="Return Photo" 
                   class="w-16 h-12 object-cover cursor-pointer hover:scale-110 transition-transform duration-200 transform-gpu"
                   data-transaction-id="<?= $row['TransactionID'] ?>"
                   onclick="openModal(this)">
            <?php else: ?>
              N/A
            <?php endif; ?>
          </td>
          <td class="px-4 py-2"><?= $row['verified'] ?? 'N/A' ?></td>
          <td class="px-4 py-2">
            <?= ($row['verified'] === 'Not Verified' || $row['verified'] === null) ? 'Pending Verification' : 'Verified' ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <!-- Modal for enlarged image and verification -->
  <div id="imgModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center" onclick="closeModal()">
    <div class="bg-white p-4 rounded shadow-md relative" onclick="event.stopPropagation()">
      <img id="modalImg" class="modal-img rounded mb-4" src="" alt="Enlarged Photo">
      <form method="POST" class="text-center">
        <input type="hidden" name="verify_transaction_id" id="modalTransactionID" value="">
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Verify</button>
      </form>
      <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800">✖️</button>
    </div>
  </div>
</body>
</html>
