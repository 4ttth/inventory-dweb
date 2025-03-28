<?php
// sign_up.php
session_start();
include 'db_connect.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $firstName = $conn->real_escape_string($_POST['first_name']);
  $lastName  = $conn->real_escape_string($_POST['last_name']);
  $email     = $conn->real_escape_string($_POST['email']);
  $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO user (FirstName, LastName, Username, Password, Role) VALUES (?, ?, ?, ?, 'Borrower')");
  $stmt->bind_param("ssss", $firstName, $lastName, $email, $password);
  if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Success! You can now login!';
    header('Location: login.php');
  } else {
    $message = "Error: " . $stmt->error;
  }
  $stmt->close();
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
      <h2 class="text-2xl font-bold mb-6 text-center">🗃️ Inventory System Sign Up</h2>
      <?php if ($message): ?>
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded"><?= $message ?></div>
      <?php endif; ?>
      <form method="POST" class="space-y-4">
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">First Name</label>
          <input type="text" name="first_name" required class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Last Name</label>
          <input type="text" name="last_name" required class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2 border rounded-md">
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Password</label>
          <input type="password" name="password" required class="w-full px-3 py-2 border rounded-md">
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-md hover:bg-blue-600">Sign Up</button>
      </form>
      <p class="mt-4 text-center">Already have an account? <a href="login.php" class="text-blue-600 underline">Log in</a></p>
    </div>
  </div>
</body>

</html>