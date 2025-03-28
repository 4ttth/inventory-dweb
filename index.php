<?php
session_start();
include 'db_connect.php';

// Use IFNULL() to default original_quantity to current_quantity if not set.
$items = $conn->query("
  SELECT 
    i.*,
    i.Quantity AS current_quantity,
    COALESCE(t.net_borrowed, 0) AS borrowed_quantity
  FROM item i
  LEFT JOIN (
    SELECT 
      ItemID,
      SUM(CASE 
            WHEN TransactionType = 'Borrow' THEN Quantity 
            WHEN TransactionType = 'Return' THEN -Quantity 
            ELSE 0 
          END) AS net_borrowed
    FROM `transaction`
    GROUP BY ItemID
  ) t ON i.ItemID = t.ItemID
");
?>
<!DOCTYPE html>
<html lang="en">
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inventory Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Compact card style */
    .card {
      padding: 0.5rem;
    }
    .card-img {
      width: 100%;
      aspect-ratio: 5 / 4;
      object-fit: cover;
    }
  </style>
</head>
<body>
  <!-- Ensure nav.php includes your responsive hamburger nav -->
  <div class="container mx-auto px-4 py-8">
  <?php include 'nav.php'; ?>
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Available Items</h2>
    <?php if(!isset($_SESSION['user_id'])): ?>
      <div class="mb-4">
        <a href="sign_up.php" class="text-blue-600 underline">Sign up now</a> to borrow items!
      </div>
    <?php endif; ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
      <?php while($item = $items->fetch_assoc()): ?>
      <div class="bg-white card rounded shadow group relative transition-transform duration-200 hover:scale-105 hover:shadow-lg">
        <img src="<?= htmlspecialchars($item['photo']) ?>" 
             alt="<?= htmlspecialchars($item['ItemName']) ?>" 
             class="card-img rounded mb-2" loading="lazy">
        <h3 class="text-sm font-semibold"><?= htmlspecialchars($item['ItemName']) ?></h3>
        <p class="text-xs text-gray-600">Item ID: <?= $item['ItemID'] ?></p>
        <p class="text-xs text-gray-600">Current: <?= $item['current_quantity'] ?></p>
        <p class="text-xs text-gray-600">Borrowed: <?= $item['borrowed_quantity'] ?></p>
        <p class="text-xs text-gray-600">Original: <?= $item['original_quantity'] ?></p>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>
