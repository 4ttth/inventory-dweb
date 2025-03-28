<?php
$current_role = $_SESSION['role'] ?? '';
?>
<nav class="bg-blue-600 text-white p-4 mb-6 rounded-lg relative z-10">
    <div class="container mx-auto flex items-center justify-between">
        <div class="flex space-x-4">
            <a href="index.php" class="hover:bg-blue-700 px-3 py-2 rounded">🏠 Home</a>
            <a href="search_item.php" class="hover:bg-blue-700 px-3 py-2 rounded">🔍 Search</a>
            
            <?php if (in_array($current_role, ['Admin', 'Officer'])): ?>
                <div class="relative group">
                    <button class="hover:bg-blue-700 px-3 py-2 rounded">📦 Manage Items 🔽</button>
                    <div class="hidden group-hover:block absolute bg-white shadow-md rounded mt-1">
                        <a href="manage_items.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">⚙️ Edit Items</a>
                        <a href="all_items_report.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">📊 View Reports</a>
                        <a href="verify_returns.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">✅ Verify Returns</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($current_role === 'Admin'): ?>
                <div class="relative group">
                    <button class="hover:bg-blue-700 px-3 py-2 rounded">👥  Manage Users 🔽</button>
                    <div class="hidden group-hover:block absolute bg-white shadow-md rounded mt-1">
                        <a href="add_user.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">➕ Add User</a>
                        <a href="delete_user.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">❌ Delete User</a>
                    </div>
                </div>

                <div class="relative group">
                    <button class="hover:bg-blue-700 px-3 py-2 rounded">📦 Inventory 🔽</button>
                    <div class="hidden group-hover:block absolute bg-white shadow-md rounded mt-1">
                        <a href="manage_shelves.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">🗄️Manage Shelves</a>
                        <a href="manage_categories.php" class="block text-gray-800 hover:bg-blue-100 px-4 py-2">📑 Manage Categories</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($current_role === 'Borrower'): ?>
                <a href="borrow_item.php" class="hover:bg-blue-700 px-3 py-2 rounded">📤 Borrow Item</a>
                <a href="return_item.php" class="hover:bg-blue-700 px-3 py-2 rounded">📥 Return Item</a>
                <a href="my_inventory.php" class="hover:bg-blue-700 px-3 py-2 rounded">📋 Current Items</a>
            <?php endif; ?>
        </div>
        <?php if (in_array($current_role, ['Admin', 'Officer', 'Borrower'])): ?>
        <div>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">🚪 Logout</a>
        </div>
        <?php endif; ?>
    </div>
</nav>