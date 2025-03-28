<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] !== 'Admin') { 
    exit("Access Denied!"); 
}

$message = '';
$error = '';

if (isset($_POST['create_shelf'])) {
    $location = $_POST['location'];
    $size = $_POST['size'];

    $stmt = $conn->prepare("INSERT INTO shelf (ShelfLocation, ShelfSize) VALUES (?, ?)");
    $stmt->bind_param("ss", $location, $size);
    
    $_SESSION['success_message'] = "Shelf created successfully!";
    header('location: manage_shelves.php');
    if($stmt->execute()) {
        $message = "Shelf created successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Create New Shelf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">🗄️ Create New Shelf</h2>
            
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md"><?= $error ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Location</label>
                    <input type="text" name="location" class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Size</label>
                    <select name="size" class="w-full px-3 py-2 border rounded-md" required>
                        <option value="Small">Small</option>
                        <option value="Medium">Medium</option>
                        <option value="Large">Large</option>
                    </select>
                </div>
                <button type="submit" name="create_shelf" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Create Shelf
                </button>
            </form>
        </div>
    </div>
</body>
</html>