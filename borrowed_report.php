<?php
session_start();
include 'db_connect.php';

$result = $conn->query("SELECT transaction.ItemID, itemItemName, transaction.Quantity, transaction.Date 
                        FROM transaction JOIN item ON transaction.ItemID = itemItemID 
                        WHERE TransactionType = 'Borrow'");
?>

<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Borrowed Items Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-blue-800">📜 Borrowed Items Report</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['ItemID'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['ItemName'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['Quantity'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['Date'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>