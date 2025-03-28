<?php
// borrow_item.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Borrower') {
  exit("Access Denied!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $itemID = intval($_POST['item_id']);
  $quantity = intval($_POST['quantity']);
  $expiry_date = $_POST['expiry_date']; // Set by JavaScript on page load
  $userID = $_SESSION['user_id'];

  // Check if the item exists and has enough quantity
  $result = $conn->query("SELECT Quantity FROM item WHERE ItemID = $itemID");
  if ($result->num_rows == 0) {
    echo "<script>alert('Item not found!'); window.location.href='borrow_item.php';</script>";
    exit();
  }
  $row = $result->fetch_assoc();
  if ($row['Quantity'] < $quantity) {
    echo "<script>alert('Not enough items available!'); window.location.href='borrow_item.php';</script>";
    exit();
  }

  // Deduct the requested quantity from the item
  $conn->query("UPDATE item SET Quantity = Quantity - $quantity WHERE ItemID = $itemID");

  // Insert a new borrow transaction with expiry date
  $stmt = $conn->prepare("INSERT INTO `transaction` (ItemID, TransactionType, Quantity, Date, Time, UserID, expiry_date) VALUES (?, 'Borrow', ?, CURDATE(), CURTIME(), ?, ?)");
  $stmt->bind_param("iiis", $itemID, $quantity, $userID, $expiry_date);
  if ($stmt->execute()) {
    echo "<script>alert('Item borrowed successfully!'); window.location.href='index.php';</script>";
    exit();
  } else {
    echo "<script>alert('Error borrowing item: " . $stmt->error . "'); window.location.href='borrow_item.php';</script>";
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Borrow Item</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
  <script>
    function fetchItemDetailsBorrow() {
      const container = document.getElementById('imageContainer');
      const itemImage = document.getElementById('itemImage');
      const itemId = document.getElementById('item_id').value;
      if (!itemId) {
        document.getElementById('itemImage').src = '';
        document.getElementById('itemNameDisplay').innerHTML = '';
        document.getElementById('availableDisplay').innerHTML = '';
        return;
      }
      fetch('get_item_details.php?item_id=' + encodeURIComponent(itemId))
        .then(response => response.json())
        .then(data => {
          if (data.ItemName) {
            container.classList.remove('hidden');
            itemImage.src = data.photo;
            document.getElementById('itemNameDisplay').innerHTML = 'Item Name: <strong>' + data.ItemName + '</strong>';
            document.getElementById('availableDisplay').innerHTML = 'Available: ' + data.Quantity;
            updateRemainingBorrow();
          } else {
            container.classList.add('hidden');
            itemImage.src = '';
            document.getElementById('itemNameDisplay').innerHTML = 'Item not found';
            document.getElementById('availableDisplay').innerHTML = '';
            document.getElementById('remainingDisplay').innerHTML = '';
          }
        });
    }

    function updateRemainingBorrow() {
      const qty = parseInt(document.getElementById('quantity').value) || 0;
      const availableText = document.getElementById('availableDisplay').innerText;
      const available = parseInt(availableText.replace('Available: ', '')) || 0;
      document.getElementById('remainingDisplay').innerHTML = 'Remaining: ' + (available - qty);
    }
    document.addEventListener("DOMContentLoaded", function() {
      const expiryInput = document.getElementById("expiry_date");
      let now = new Date();
      now.setDate(now.getDate() + 60);
      let yyyy = now.getFullYear();
      let mm = String(now.getMonth() + 1).padStart(2, '0');
      let dd = String(now.getDate()).padStart(2, '0');
      expiryInput.value = `${yyyy}-${mm}-${dd}`;
    });
  </script>
</head>

<body>
  <div class="container mx-auto px-4 py-8">
    <?php include 'nav.php'; ?>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-6">📥 Borrow Item</h2>
      <form method="POST" class="space-y-4">
        <div id="imageContainer" class="hidden">
          <img id="itemImage" src="" alt="Item Image" class="w-full h-72 object-cover mb-2 rounded">
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Item ID</label>
          <input type="number" name="item_id" id="item_id" class="w-full px-3 py-2 border rounded-md" required oninput="fetchItemDetailsBorrow()">
          <div id="itemNameDisplay" class="mt-1 text-sm"></div>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="w-full px-3 py-2 border rounded-md" required min="1" oninput="updateRemainingBorrow()">
          <div id="availableDisplay" class="mt-1 text-sm"></div>
          <div id="remainingDisplay" class="mt-1 text-sm"></div>
        </div>
        <!-- Hidden field for expiry date -->
        <input type="hidden" name="expiry_date" id="expiry_date" value="">
        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">
          Borrow Item
        </button>
      </form>
      <a href="index.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">← Back to Dashboard</a>
    </div>
  </div>
</body>

</html>