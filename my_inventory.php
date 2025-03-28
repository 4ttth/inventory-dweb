<?php
// my_inventory.php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Borrower') {
  exit("Access Denied!");
}

$userID = $_SESSION['user_id'];

// Active borrow transactions – assume a verified return removes a borrow record.
// (This simplistic query assumes one transaction per borrow; in practice you might need to join and subtract returned quantities.)
$borrowed = $conn->query("SELECT t.*, i.ItemName, i.photo, t.expiry_date 
    FROM `transaction` t 
    LEFT JOIN item i ON t.ItemID = i.ItemID 
    WHERE t.TransactionType = 'Borrow' AND t.UserID = '$userID'
      AND NOT EXISTS (
        SELECT 1 FROM `transaction` r 
        WHERE r.TransactionType = 'Return' AND 
              r.UserID = '$userID' AND 
              r.ItemID = t.ItemID AND 
              r.verified = 'Verified'
      )");

// Return requests that are still not verified.
$notVerified = $conn->query("SELECT t.*, i.ItemName, i.photo 
    FROM `transaction` t 
    LEFT JOIN item i ON t.ItemID = i.ItemID 
    WHERE t.TransactionType = 'Return' AND 
          t.UserID = '$userID' AND 
          (t.verified = 'Not Verified' OR t.verified IS NULL)");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Inventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
  <style>
    .card-img {
      width: 100%;
      aspect-ratio: 5 / 4;
      object-fit: cover;
    }
  </style>
</head>

<body>

  <div class="container mx-auto px-4 py-8">
    <?php include 'nav.php'; ?>
    <h2 class="text-2xl font-bold mb-4">My Inventory</h2>
    <h3 class="text-xl font-semibold mb-2 text-green-800">Borrowed Items</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6">
      <?php while ($row = $borrowed->fetch_assoc()): ?>
        <div class="bg-green-50 p-3 rounded shadow transition-transform duration-200 hover:scale-105 hover:shadow-lg">
          <img src="<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['ItemName']) ?>" class="card-img rounded mb-2" loading="lazy">
          <h4 class="font-semibold text-sm"><?= htmlspecialchars($row['ItemName']) ?></h4>
          <p class="text-xs">Item ID: <?= $row['ItemID'] ?></p>
          <p class="text-xs">Quantity: <?= $row['Quantity'] ?></p>
          <p class="text-xs">Expiry: <?= $row['expiry_date'] ?></p>
        </div>
      <?php endwhile; ?>
    </div>
    <h3 class="text-xl font-semibold mb-2 text-yellow-800">Pending Returns (Waiting Verification)</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <?php while ($row = $notVerified->fetch_assoc()): ?>
        <div class="bg-yellow-50 p-3 rounded shadow transition-transform duration-200 hover:scale-105 hover:shadow-lg">
          <img src="<?= htmlspecialchars($row['photo']) ?>" alt="<?= htmlspecialchars($row['ItemName']) ?>" class="card-img rounded mb-2" loading="lazy">
          <h4 class="font-semibold text-sm"><?= htmlspecialchars($row['ItemName']) ?></h4>
          <p class="text-xs">Item ID: <?= $row['ItemID'] ?></p>
          <p class="text-xs">Quantity: <?= $row['Quantity'] ?></p>
          <p class="text-xs text-yellow-600">Status: Pending Verification</p>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>

</html>