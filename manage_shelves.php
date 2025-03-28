<?php
session_start();
include 'db_connect.php';

if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) {
    exit("Access Denied!");
}

// Process form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_shelf'])) {
        $stmt = $conn->prepare("UPDATE Shelf SET ShelfLocation=?, ShelfSize=? WHERE ShelfID=?");
        $stmt->bind_param("ssi", $_POST['location'], $_POST['size'], $_POST['shelf_id']);
        if ($stmt->execute()) {
            $message = "Shelf updated successfully!";
        } else {
            $error = "Error updating shelf: " . $stmt->error;
        }
    }
    elseif (isset($_POST['delete_shelf'])) {
        $shelfID = intval($_POST['shelf_id']);
        $conn->query("DELETE FROM shelf WHERE ShelfID = $shelfID");
        $message = "Shelf deleted successfully!";
    }
}

$shelves = $conn->query("SELECT * FROM shelf");
?>

<!DOCTYPE html>
<html>
<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Manage Shelves</title>
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

        function handleEditSave(rowId, shelfId) {
            const row = document.getElementById(rowId);
            const location = row.querySelector('input[data-field="location"]').value;
            const size = row.querySelector('input[data-field="size"]').value;
            
            document.getElementById('update_shelf_id').value = shelfId;
            document.getElementById('update_location').value = location;
            document.getElementById('update_size').value = size;
            document.getElementById('updateForm').submit();
        }

        function handleDelete(shelfId, shelfLocation) {
            showConfirmModal("⚠️ Confirm Deletion", 
                `Are you sure you want to delete shelf "${shelfLocation}"?`,
                () => {
                    document.getElementById('delete_shelf_id').value = shelfId;
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
            <h2 class="text-2xl font-bold mb-4">🗄️ Manage Shelves</h2>
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shelf ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($row = $shelves->fetch_assoc()): ?>
                        <tr id="row-<?= $row['ShelfID'] ?>">
                            <td class="px-6 py-4"><?= $row['ShelfID'] ?></td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="location"><?= $row['ShelfLocation'] ?></span>
                                <input type="text" data-field="location" value="<?= htmlspecialchars($row['ShelfLocation']) ?>" 
                                    class="edit-mode hidden border rounded px-2 py-1 w-full">
                            </td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="size"><?= $row['ShelfSize'] ?></span>
                                <input type="text" data-field="size" value="<?= htmlspecialchars($row['ShelfSize']) ?>" 
                                    class="edit-mode hidden border rounded px-2 py-1 w-full">
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                <span class="view-mode">
                                    <button onclick="toggleEdit('row-<?= $row['ShelfID'] ?>')" 
                                            class="text-blue-500 hover:text-blue-700">✏️</button>
                                    <button onclick="handleDelete(<?= $row['ShelfID'] ?>, '<?= $row['ShelfLocation'] ?>')" 
                                            class="text-red-500 hover:text-red-700">🗑️</button>
                                </span>
                                <span class="edit-mode hidden">
                                    <button onclick="handleEditSave('row-<?= $row['ShelfID'] ?>', <?= $row['ShelfID'] ?>)" 
                                            class="text-green-500 hover:text-green-700">✔️</button>
                                    <button onclick="cancelEdit('row-<?= $row['ShelfID'] ?>')" 
                                            class="text-red-500 hover:text-red-700">❌</button>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="mt-4">
                <a href="create_shelf.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    ➕ Add New Shelf
                </a>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form method="POST" id="updateForm" class="hidden">
        <input type="hidden" name="update_shelf" value="1">
        <input type="hidden" id="update_shelf_id" name="shelf_id">
        <input type="hidden" id="update_location" name="location">
        <input type="hidden" id="update_size" name="size">
    </form>

    <form method="POST" id="deleteForm" class="hidden">
        <input type="hidden" name="delete_shelf" value="1">
        <input type="hidden" id="delete_shelf_id" name="shelf_id">
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