<?php
session_start();
include 'db_connect.php';

// Inventory Overview
$items = $conn->query("SELECT COUNT(*) as total FROM item")->fetch_assoc();
$borrowed = $conn->query("SELECT SUM(Quantity) as total FROM transaction WHERE TransactionType='Borrow'")->fetch_assoc();

// Category Distribution
$categories = $conn->query("SELECT CategoryName, COUNT(*) as count FROM category GROUP BY CategoryID");

// Shelf Distribution: count of items per shelf (by number of items)
$shelfDistribution = $conn->query("SELECT shelf.ShelfLocation, COUNT(itemItemID) as count 
    FROM item 
    INNER JOIN shelf ON itemShelfID = shelf.ShelfID 
    GROUP BY shelf.ShelfLocation");

// Shelf Occupation: total quantity per shelf (how full each shelf is)
$shelfOccupation = $conn->query("SELECT shelf.ShelfLocation, SUM(itemQuantity) as total_quantity 
    FROM item 
    INNER JOIN shelf ON itemShelfID = shelf.ShelfID 
    GROUP BY shelf.ShelfLocation");

// Item Distribution: top 5 items with highest quantity
$itemDistribution = $conn->query("SELECT ItemName, Quantity 
    FROM item 
    ORDER BY Quantity DESC 
    LIMIT 5");

// Most Borrowed Items: top 5 items by borrow count
$mostBorrowed = $conn->query("SELECT itemItemName, SUM(transaction.Quantity) as total_borrows 
    FROM transaction 
    INNER JOIN item ON transaction.ItemID = itemItemID 
    WHERE transaction.TransactionType = 'Borrow' 
    GROUP BY transaction.ItemID 
    ORDER BY total_borrows DESC 
    LIMIT 5");
?>

<!DOCTYPE html>
<html>

<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Reports Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>

        <!-- Top Row: Inventory Overview & Shelf Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Inventory Overview -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold mb-4">📊 Inventory Overview</h3>
                <canvas id="inventoryChart"></canvas>
            </div>

            <!-- Shelf Data: Two Pie Charts -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Shelf Distribution -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-4">🗂️ Shelf Distribution</h3>
                    <canvas id="shelfDistributionChart"></canvas>
                </div>
                <!-- Shelf Occupation -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-4">📦 Inventory Occupation</h3>
                    <canvas id="shelfOccupationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Item & Category Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Item Distribution -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-bold mb-4">📦 Category Distribution</h3>
                <canvas id="categoryChart"></canvas>
            </div>
            <!-- Category Distribution and Most Borrowed -->
            <div class="grid grid-rows-2 gap-4">
                <!-- Category Distribution -->

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-4">📈 Item Distribution</h3>
                    <canvas id="itemDistributionChart"></canvas>
                </div>
                <!-- Most Borrowed Items -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-4">🔥 Most Borrowed Items</h3>
                    <canvas id="mostBorrowedChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inventory Chart (Bar)
        new Chart(document.getElementById('inventoryChart'), {
            type: 'bar',
            data: {
                labels: ['Total Items', 'Borrowed Items'],
                datasets: [{
                    label: 'Inventory',
                    data: [<?= $items['total'] ?>, <?= $borrowed['total'] ?? 0 ?>],
                    backgroundColor: ['#3B82F6', '#10B981']
                }]
            }
        });

        // Shelf Distribution Chart (Pie)
        new Chart(document.getElementById('shelfDistributionChart'), {
            type: 'pie',
            data: {
                labels: [<?php while ($row = $shelfDistribution->fetch_assoc()) echo "'" . $row['ShelfLocation'] . "',"; ?>],
                datasets: [{
                    data: [<?php
                            $shelfDistribution->data_seek(0);
                            while ($row = $shelfDistribution->fetch_assoc()) echo $row['count'] . ',';
                            ?>],
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']
                }]
            }
        });

        // Shelf Occupation Chart (Pie)
        new Chart(document.getElementById('shelfOccupationChart'), {
            type: 'pie',
            data: {
                labels: [<?php while ($row = $shelfOccupation->fetch_assoc()) echo "'" . $row['ShelfLocation'] . "',"; ?>],
                datasets: [{
                    data: [<?php
                            $shelfOccupation->data_seek(0);
                            while ($row = $shelfOccupation->fetch_assoc()) echo $row['total_quantity'] . ',';
                            ?>],
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']
                }]
            }
        });

        // Item Distribution Chart (Horizontal Bar)
        new Chart(document.getElementById('itemDistributionChart'), {
            type: 'bar',
            data: {
                labels: [<?php while ($row = $itemDistribution->fetch_assoc()) echo "'" . $row['ItemName'] . "',"; ?>],
                datasets: [{
                    label: 'Quantity',
                    data: [<?php
                            $itemDistribution->data_seek(0);
                            while ($row = $itemDistribution->fetch_assoc()) echo $row['Quantity'] . ',';
                            ?>],
                    backgroundColor: '#3B82F6'
                }]
            },
            options: {
                indexAxis: 'y'
            }
        });

        // Category Distribution Chart (Pie)
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: [<?php while ($row = $categories->fetch_assoc()) echo "'" . $row['CategoryName'] . "',"; ?>],
                datasets: [{
                    data: [<?php
                            $categories->data_seek(0);
                            while ($row = $categories->fetch_assoc()) echo $row['count'] . ',';
                            ?>],
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6']
                }]
            }
        });

        // Most Borrowed Items Chart (Horizontal Bar)
        new Chart(document.getElementById('mostBorrowedChart'), {
            type: 'bar',
            data: {
                labels: [<?php while ($row = $mostBorrowed->fetch_assoc()) echo "'" . $row['ItemName'] . "',"; ?>],
                datasets: [{
                    label: 'Borrows',
                    data: [<?php
                            $mostBorrowed->data_seek(0);
                            while ($row = $mostBorrowed->fetch_assoc()) echo $row['total_borrows'] . ',';
                            ?>],
                    backgroundColor: '#EF4444'
                }]
            },
            options: {
                indexAxis: 'y'
            }
        });
    </script>
</body>

</html>