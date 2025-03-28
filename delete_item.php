<?php
session_start();
include 'db_connect.php';

if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) { 
    exit("Access Denied!"); 
}

if (isset($_GET['delete_item'])) {
    $itemID = $_GET['delete_item'];
    $conn->query("DELETE FROM item WHERE ItemID = $itemID");
    $message = "Item deleted successfully!";
}

$result = $conn->query("SELECT * FROM item");
?>

<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Delete Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">❌ Delete Item</h2>
            <?php if (isset($message)): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['ItemID'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['ItemName'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['Quantity'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="delete_item.php?delete_item=<?= $row['ItemID'] ?>" 
                                   class="text-red-600 hover:text-red-900">❌ Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>