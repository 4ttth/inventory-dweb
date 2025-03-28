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
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['role'] = $row['Role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
$message = '';
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  
  <div class="container mx-auto px-4 py-8">
  <?php include 'nav.php'; ?>
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center">🗃️ Inventory System Login</h2>
        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message;
                                                                            unset($_SESSION['success_message']) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md"><?= $error ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" class="w-full px-3 py-2 border rounded-md" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded-md" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                Login
            </button>
        </form>
        <p class="mt-4 text-center">Need an account? <a href="sign_up.php" class="text-blue-500">Sign up</a></p>
    </div>
</body>

</html>