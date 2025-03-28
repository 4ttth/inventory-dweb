<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] !== 'Admin') { 
    exit("Access Denied!"); 
}

$message = '';
$error = '';

if (isset($_POST['create_category'])) {
    $categoryName = $_POST['category_name'];

    $stmt = $conn->prepare("INSERT INTO Category (CategoryName) VALUES (?)");
    $stmt->bind_param("s", $categoryName);
    $_SESSION['success_message'] = "Category created successfully!";
    header('location: manage_categories.php');
    if($stmt->execute()) {
        $message = "Category created successfully!";
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
    <title>Create New Category</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">📑 Create New Category</h2>
            
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md"><?= $error ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Category Name</label>
                    <input type="text" name="category_name" 
                           class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <button type="submit" name="create_category" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Create Category
                </button>
            </form>
        </div>
    </div>
</body>
</html>