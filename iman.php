// db_connect.php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "InventoryDB";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

// login.php
<?php
session_start();
include 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM user WHERE Username='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            $_SESSION['role'] = $row['Role'];
            $_SESSION['user_id'] = $row['UserID'];
            header("Location: dashboard.php");
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }
}
?>

// logout.php
<?php
session_start();
session_destroy();
header("Location: login.php");
exit();
?>

// add_user.php
<?php
session_start();
include 'db_connect.php';
if ($_SESSION['role'] !== 'Admin') { exit("Access Denied!"); }
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $sql = "INSERT INTO user (Username, Password, Role) VALUES ('$username', '$password', '$role')";
    $conn->query($sql);
}
?>

// delete_user.php
<?php
session_start();
include 'db_connect.php';
if ($_SESSION['role'] !== 'Admin') { exit("Access Denied!"); }
if (isset($_GET['delete_user'])) {
    $userID = $_GET['delete_user'];
    $conn->query("DELETE FROM user WHERE UserID = $userID");
}
?>

// add_item.php
<?php
session_start();
include 'db_connect.php';
if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) { exit("Access Denied!"); }
if (isset($_POST['add_item'])) {
    $itemName = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $shelfID = $_POST['shelf_id'];
    $categoryID = $_POST['category_id'];
    
    $sql = "INSERT INTO Item (ItemName, Quantity, ShelfID, CategoryID) VALUES ('$itemName', '$quantity', '$shelfID', '$categoryID')";
    $conn->query($sql);
}
?>

// delete_item.php
<?php
session_start();
include 'db_connect.php';
if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) { exit("Access Denied!"); }
if (isset($_GET['delete_item'])) {
    $itemID = $_GET['delete_item'];
    $conn->query("DELETE FROM item WHERE ItemID = $itemID");
}
?>

// search_item.php
<?php
session_start();
include 'db_connect.php';
if (isset($_POST['search_item'])) {
    $searchQuery = $_POST['search_query'];
    $result = $conn->query("SELECT * FROM item WHERE ItemName LIKE '%$searchQuery%'");
    while ($row = $result->fetch_assoc()) {
        echo $row['ItemName'] . " - Quantity: " . $row['Quantity'] . "<br>";
    }
}
?>

// borrowed_report.php
<?php
session_start();
include 'db_connect.php';
$result = $conn->query("SELECT * FROM transaction WHERE TransactionType = 'Borrow'");
while ($row = $result->fetch_assoc()) {
    echo "Item ID: " . $row['ItemID'] . " - Borrowed Quantity: " . $row['Quantity'] . " - Date: " . $row['Date'] . "<br>";
}
?>

// all_items_report.php
<?php
session_start();
include 'db_connect.php';
$result = $conn->query("SELECT itemItemID, itemItemName, itemQuantity, shelf.ShelfLocation, category.CategoryName FROM item JOIN shelf ON itemShelfID = shelf.ShelfID JOIN category ON itemCategoryID = category.CategoryID");
while ($row = $result->fetch_assoc()) {
    echo "Item ID: " . $row['ItemID'] . " - Name: " . $row['ItemName'] . " - Quantity: " . $row['Quantity'] . " - Shelf: " . $row['ShelfLocation'] . " - Category: " . $row['CategoryName'] . "<br>";
}
?>

