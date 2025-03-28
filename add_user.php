<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] !== 'Admin') { 
    exit("Access Denied!"); 
}

if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $sql = "INSERT INTO user (Username, Password, Role) VALUES ('$username', '$password', '$role')";
    $conn->query($sql);
    $message = "User added successfully!";
}
?>

<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Add User</title>
    <script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-blue-800">👤 Add User</h2>
            <?php if (isset($message)): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" 
                           class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" 
                           class="w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Role</label>
                    <select name="role" class="w-full px-3 py-2 border rounded-md">
                        <option value="Admin">Admin</option>
                        <option value="Officer">Officer</option>
                        <option value="Borrower">Borrower</option>
                    </select>
                </div>
                <button type="submit" name="add_user" 
                        class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                    Add User
                </button>
            </form>
        </div>
    </div>
</body>
</html>