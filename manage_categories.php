<?php
session_start();
include 'db_connect.php';

if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) {
    exit("Access Denied!");
}

// Process form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_category'])) {
        $stmt = $conn->prepare("UPDATE category SET CategoryName=? WHERE CategoryID=?");
        $stmt->bind_param("si", $_POST['category_name'], $_POST['category_id']);
        if ($stmt->execute()) {
            $message = "Category updated successfully!";
        } else {
            $error = "Error updating category: " . $stmt->error;
        }
    }
    elseif (isset($_POST['delete_category'])) {
        $categoryID = intval($_POST['category_id']);
        $conn->query("DELETE FROM category WHERE CategoryID = $categoryID");
        $message = "Category deleted successfully!";
    }
}

$categories = $conn->query("SELECT * FROM category");
?>

<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Manage Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <script>
        let unsavedChanges = false;
        let confirmCallback = null;

        function showConfirmModal(title, content, callback) {
            document.getElementById('confirmModalTitle').innerText = title;
            document.getElementById('confirmModalContent').innerHTML = content;
            confirmCallback = callback;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function hideConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            confirmCallback = null;
        }

        function confirmAction() {
            if (confirmCallback) confirmCallback();
            hideConfirmModal();
        }

        function toggleEdit(rowId) {
            const row = document.getElementById(rowId);
            row.querySelectorAll('.view-mode').forEach(e => e.classList.add('hidden'));
            row.querySelectorAll('.edit-mode').forEach(e => e.classList.remove('hidden'));
            unsavedChanges = true;
        }

        function cancelEdit(rowId) {
            const row = document.getElementById(rowId);
            row.querySelectorAll('.edit-mode').forEach(e => e.classList.add('hidden'));
            row.querySelectorAll('.view-mode').forEach(e => e.classList.remove('hidden'));
            unsavedChanges = false;
        }
        
        function handleEditSave(rowId, categoryId) {
            const row = document.getElementById(rowId);
            const categoryName = row.querySelector('input[data-field="category_name"]').value;
            
            document.getElementById('update_category_id').value = categoryId;
            document.getElementById('update_category_name').value = categoryName;
            document.getElementById('updateForm').submit();
        }

        function handleDelete(categoryId, categoryName) {
            showConfirmModal("⚠️ Confirm Deletion", 
                `Are you sure you want to delete category "${categoryName}"?`,
                () => {
                    document.getElementById('delete_category_id').value = categoryId;
                    document.getElementById('deleteForm').submit();
                }
            );
        }
    </script>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">📑 Manage Categories</h2>
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <tr id="row-<?= $row['CategoryID'] ?>">
                            <td class="px-6 py-4"><?= $row['CategoryID'] ?></td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="category_name"><?= $row['CategoryName'] ?></span>
                                <input type="text" data-field="category_name" value="<?= htmlspecialchars($row['CategoryName']) ?>" 
                                    class="edit-mode hidden border rounded px-2 py-1 w-full">
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <span class="view-mode">
                                    <button onclick="toggleEdit('row-<?= $row['CategoryID'] ?>')" 
                                            class="text-blue-500 hover:text-blue-700">✏️</button>
                                    <button onclick="handleDelete(<?= $row['CategoryID'] ?>, '<?= $row['CategoryName'] ?>')" 
                                            class="text-red-500 hover:text-red-700">🗑️</button>
                                </span>
                                <span class="edit-mode hidden">
                                    <button onclick="handleEditSave('row-<?= $row['CategoryID'] ?>', <?= $row['CategoryID'] ?>)" 
                                            class="text-green-500 hover:text-green-700">✔️</button>
                                    <button onclick="cancelEdit('row-<?= $row['CategoryID'] ?>')" 
                                            class="text-red-500 hover:text-red-700">❌</button>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="mt-4">
                <a href="create_category.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    ➕ Add New Category
                </a>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form method="POST" id="updateForm" class="hidden">
        <input type="hidden" name="update_category" value="1">
        <input type="hidden" id="update_category_id" name="category_id">
        <input type="hidden" id="update_category_name" name="category_name">
    </form>

    <form method="POST" id="deleteForm" class="hidden">
        <input type="hidden" name="delete_category" value="1">
        <input type="hidden" id="delete_category_id" name="category_id">
    </form>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96">
            <h3 id="confirmModalTitle" class="text-xl font-bold mb-4"></h3>
            <div id="confirmModalContent" class="mb-4"></div>
            <div class="flex justify-end gap-2">
                <button onclick="hideConfirmModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                <button onclick="confirmAction()" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Confirm</button>
            </div>
        </div>
    </div>
</body>
</html>