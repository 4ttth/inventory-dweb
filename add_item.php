<?php
session_start();
include 'db_connect.php';

if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) {
    exit("Access Denied!");
}

$message = '';
$error = '';

if (isset($_POST['add_item'])) {
    $itemName = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $shelfID = $_POST['shelf_id'];
    $categoryID = $_POST['category_id'];

    // Process item photo upload
    $photoPath = 'imgs/default.jpg'; // default photo
    if(isset($_FILES['item_photo']) && $_FILES['item_photo']['error'] == 0) {
        $uploadDir = "uploads/items/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = basename($_FILES['item_photo']['name']);
        $targetFile = $uploadDir . time() . "_" . $fileName;
        if(move_uploaded_file($_FILES['item_photo']['tmp_name'], $targetFile)) {
            $photoPath = $targetFile;
        }
    }

    if (empty($shelfID) || empty($categoryID)) {
        $error = "Please select both Shelf and Category";
    } else {
        $stmt = $conn->prepare("INSERT INTO item (ItemName, Quantity, ShelfID, CategoryID, photo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $itemName, $quantity, $shelfID, $categoryID, $photoPath);
        if ($stmt->execute()) {
            $message = "Item added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch shelves and categories for the dropdowns
$shelves = $conn->query("SELECT * FROM shelf");
$categories = $conn->query("SELECT * FROM category");
?>
<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <title>Add Item</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container mx-auto px-4 py-8">
    <?php include 'nav.php'; ?>
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
      <?php if ($message): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md"><?= $error ?></div>
      <?php endif; ?>
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block text-gray-700 mb-2">Item Name</label>
          <input type="text" name="item_name" class="w-full px-3 py-2 border rounded-md" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Quantity</label>
          <input type="number" name="quantity" class="w-full px-3 py-2 border rounded-md" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Shelf</label>
          <select name="shelf_id" class="w-full px-3 py-2 border rounded-md" required>
            <option value="">Select Shelf</option>
            <?php while ($row = $shelves->fetch_assoc()): ?>
              <option value="<?= $row['ShelfID'] ?>"><?= $row['ShelfLocation'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Category</label>
          <select name="category_id" class="w-full px-3 py-2 border rounded-md" required>
            <option value="">Select Category</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
              <option value="<?= $row['CategoryID'] ?>"><?= $row['CategoryName'] ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="block text-gray-700 mb-2">Item Photo</label>
          <input type="file" name="item_photo" accept="image/*" class="w-full">
        </div>
        <button type="submit" name="add_item" class="w-full bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600">
          Add Item
        </button>
      </form>
    </div>
  </div>
</body>
</html>
