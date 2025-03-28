<?php
session_start();
include 'db_connect.php';

$secret_key = "lausdeosemper"; // Change this to your actual secret key

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['secret'] !== $secret_key) {
        die("Invalid secret key!");
    }

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO user (Username, Password, Role) VALUES ('$username', '$password', 'Admin')";
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = "Registration successful as admin!";
        header("Location: login.php");
    } else {
        echo "<script>alert('Registration failed!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="styles.css">
    <style>
        i{
            color: red;
            font-size: small;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">🔐 Register as Admin</h2>
        <form method="post">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" class="w-full px-3 py-2 border rounded-md" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-md" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Secret Key <br><i>*Get key from system administrator</i></label>
                <input type="password" name="secret" class="w-full px-3 py-2 border rounded-md" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                Register
            </button>
        </form>
        <p class="mt-4 text-center">Already have an account? <a href="login.php" class="text-blue-500">Login</a></p>
    </div>
</body>
</html>