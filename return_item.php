<?php
// return_item.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Borrower') {
  exit("Access Denied!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $itemID = intval($_POST['item_id']);
  $quantity = intval($_POST['quantity']);
  $userID = $_SESSION['user_id'];

  // Validate inputs
  if ($quantity <= 0) {
    die("Invalid quantity.");
  }

  // Check item exists
  $itemCheck = $conn->query("SELECT Quantity FROM item WHERE ItemID = $itemID");
  if ($itemCheck->num_rows == 0) {
    die("Item not found.");
  }

  // Calculate net borrowed by user for this item
  $borrowedQuery = $conn->query("SELECT t.*, i.ItemName, i.photo, t.expiry_date 
  FROM `transaction` t 
  LEFT JOIN item i ON t.ItemID = i.ItemID 
  WHERE t.TransactionType = 'Borrow' 
    AND t.UserID = '$userID'
    AND NOT EXISTS (
      SELECT 1 FROM `transaction` r 
      WHERE r.TransactionType = 'Return' 
        AND r.ItemID = t.ItemID 
        AND r.UserID = t.UserID 
        AND r.verified = 'Verified'
    )");
  $borrowedData = $borrowedQuery->fetch_assoc();
  $netBorrowed = $borrowedData['net_borrowed'] ?? 0;

  if ($netBorrowed < $quantity) {
    die("You cannot return more than you've borrowed.");
  }
  
  // Handle photo upload
  $uploadDir = 'uploads/returns';
  $photoName = basename($_FILES['return_photo']['name']);
  $photoPath = $uploadDir . uniqid() . '_' . $photoName;
  if (!move_uploaded_file($_FILES['return_photo']['tmp_name'], $photoPath)) {
    die("Error uploading photo.");
  }

  // Update item quantity
  $conn->query("UPDATE item SET Quantity = Quantity + $quantity WHERE ItemID = $itemID");

  // Insert return transaction
  $stmt = $conn->prepare("INSERT INTO `transaction` 
    (ItemID, TransactionType, Quantity, Date, Time, UserID, return_photo, verified) 
    VALUES (?, 'Return', ?, CURDATE(), CURTIME(), ?, ?, 'Not Verified')");
$stmt->bind_param("iiis", $itemID, $quantity, $userID, $photoPath);
  if ($stmt->execute()) {
    echo "<script>alert('Item returned successfully!'); window.location.href='index.php';</script>";
  } else {
    echo "Error: " . $stmt->error;
  }
  exit();
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
  <title>Return Item</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
  <script>
    // When the item_id field changes, fetch details via AJAX.
    function fetchItemDetails() {
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
            updateRemaining();
          } else {
            container.classList.add('hidden');
            itemImage.src = '';
            document.getElementById('itemNameDisplay').innerHTML = 'Item not found';
            document.getElementById('availableDisplay').innerHTML = '';
            document.getElementById('remainingDisplay').innerHTML = '';
          }
        });
    }
    // When quantity changes, update remaining available.
    function updateRemaining() {
      const qty = parseInt(document.getElementById('quantity').value) || 0;
      const availableText = document.getElementById('availableDisplay').innerText;
      const available = parseInt(availableText.replace('Available: ', '')) || 0;
      document.getElementById('remainingDisplay').innerHTML = 'Remaining: ' + (available - qty);
    }
  </script>
</head>

<body>
  <div class="container mx-auto px-4 py-8">
    <?php include 'nav.php'; ?>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-6 text-green-800">📤 Return Item</h2>
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <div id="imageContainer" class="hidden">
          <img id="itemImage" src="" alt="Item Image" class="w-full h-72 object-cover mb-2 rounded">
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Item ID</label>
          <input type="number" name="item_id" id="item_id" class="w-full px-3 py-2 border rounded-md" required oninput="fetchItemDetails()">
          <div id="itemNameDisplay" class="mt-1 text-sm"></div>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="w-full px-3 py-2 border rounded-md" required min="1" oninput="updateRemaining()">
          <div id="availableDisplay" class="mt-1 text-sm"></div>
          <div id="remainingDisplay" class="mt-1 text-sm"></div>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Upload Return Photo</label>
          <input type="file" name="return_photo" accept="image/*" class="w-full" required>
        </div>
        <button type="submit" class="w-full bg-green-500 text-white py-2 rounded-md hover:bg-green-600">
          Return Item
        </button>
      </form>
      <a href="index.php" class="mt-4 inline-block text-gray-600 hover:text-gray-800">← Back to Dashboard</a>
    </div>
  </div>
</body>

</html>