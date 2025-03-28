<?php
// get_item_details.php
session_start();
include 'db_connect.php';
if (isset($_GET['item_id'])) {
  $itemID = intval($_GET['item_id']);
  $result = $conn->query("SELECT i.*, c.CategoryName, s.ShelfLocation 
                       FROM item i
                       LEFT JOIN category c ON i.CategoryID = c.CategoryID
                       LEFT JOIN shelf s ON i.ShelfID = s.ShelfID
                       WHERE i.ItemID = $itemID");

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  echo json_encode([
    'ItemID' => $row['ItemID'],
    'ItemName' => $row['ItemName'],
    'Quantity' => $row['Quantity'],
    'photo' => $row['photo'],
    'CategoryName' => $row['CategoryName'],
    'ShelfLocation' => $row['ShelfLocation']
  ]);
}
}
?>
