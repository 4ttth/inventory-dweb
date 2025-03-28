<?php
session_start();
include 'db_connect.php';

// Authorization check
if (!in_array($_SESSION['role'], ['Admin', 'Officer'])) {
    exit("Access Denied!");
}

// Process deletion if a GET parameter "delete" is set.
if (isset($_GET['delete'])) {
    $deleteID = intval($_GET['delete']);
    $conn->query("DELETE FROM item WHERE ItemID = $deleteID");
    header("Location: manage_items.php");
    exit();
}

// Helper functions to display names instead of IDs
function getShelfName($shelvesArray, $shelfID)
{
    foreach ($shelvesArray as $shelf) {
        if ($shelf['ShelfID'] == $shelfID) return $shelf['ShelfLocation'];
    }
    return $shelfID;
}
function getCategoryName($categoriesArray, $catID)
{
    foreach ($categoriesArray as $cat) {
        if ($cat['CategoryID'] == $catID) return $cat['CategoryName'];
    }
    return $catID;
}

// Process update and bulk deletion if the hidden forms were submitted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update item (via hidden update form)
    if (isset($_POST['update_item'])) {
        $stmt = $conn->prepare("UPDATE item SET ItemName=?, Quantity=?, ShelfID=?, CategoryID=? WHERE ItemID=?");
        $stmt->bind_param("siiii", $_POST['item_name'], $_POST['quantity'], $_POST['shelf_id'], $_POST['category_id'], $_POST['item_id']);
        $stmt->execute();
        $message = "Item updated successfully!";
    }
    // Bulk delete (via hidden bulk form)
    elseif (isset($_POST['bulk_delete'])) {
        if (isset($_POST['bulk_item_ids']) && is_array($_POST['bulk_item_ids']) && count($_POST['bulk_item_ids'])) {
            $ids = implode(",", $_POST['bulk_item_ids']);
            $conn->query("DELETE FROM item WHERE ItemID IN ($ids)");
            $message = "Items deleted successfully!";
        } else {
            $message = "No items selected for deletion.";
        }
    }
}

// Fetch items, shelves, and categories
$itemsResult = $conn->query("SELECT * FROM item");

$shelvesResult = $conn->query("SELECT * FROM shelf");
$shelvesArray = [];
while ($shelf = $shelvesResult->fetch_assoc()) {
    $shelvesArray[] = $shelf;
}

$categoriesResult = $conn->query("SELECT * FROM category");
$categoriesArray = [];
while ($cat = $categoriesResult->fetch_assoc()) {
    $categoriesArray[] = $cat;
}
?>
<!DOCTYPE html>
<html>

<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Manage Items</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <script>
        let unsavedChanges = false;
        let bulkModeEnabled = false;
        let confirmCallback = null;

        // Show our confirmation modal.
        function showConfirmModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.add('modal-enter-active');
                modal.querySelector('.modal-content').classList.add('modal-content-enter-active');
            }, 10);
        }

        function hideConfirmModal() {
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('modal-enter-active');
            modal.querySelector('.modal-content').classList.remove('modal-content-enter-active');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        // Called when user clicks confirm in modal
        function confirmAction() {
            if (confirmCallback) {
                confirmCallback();
            }
            hideConfirmModal();
        }

        // --- Inline editing functions ---
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

        function handleEditSave(rowId, itemId) {
            const row = document.getElementById(rowId);
            const oldName = row.querySelector('.view-mode[data-field="item_name"]').innerText;
            const oldQuantity = row.querySelector('.view-mode[data-field="quantity"]').innerText;
            const oldShelf = row.querySelector('.view-mode[data-field="shelf"]').innerText;
            const oldCategory = row.querySelector('.view-mode[data-field="category"]').innerText;
            const newName = row.querySelector('input[data-field="item_name"]').value;
            const newQuantity = row.querySelector('input[data-field="quantity"]').value;
            const newShelfSelect = row.querySelector('select[data-field="shelf_id"]');
            const newShelf = newShelfSelect.options[newShelfSelect.selectedIndex].text;
            const newCategorySelect = row.querySelector('select[data-field="category_id"]');
            const newCategory = newCategorySelect.options[newCategorySelect.selectedIndex].text;
            let changes = "<ul>";
            if (oldName !== newName) {
                changes += "<li>Item Name: <strong>" + oldName + "</strong> → <strong>" + newName + "</strong></li>";
            }
            if (oldQuantity !== newQuantity) {
                changes += "<li>Quantity: <strong>" + oldQuantity + "</strong> → <strong>" + newQuantity + "</strong></li>";
            }
            if (oldShelf !== newShelf) {
                changes += "<li>Shelf: <strong>" + oldShelf + "</strong> → <strong>" + newShelf + "</strong></li>";
            }
            if (oldCategory !== newCategory) {
                changes += "<li>Category: <strong>" + oldCategory + "</strong> → <strong>" + newCategory + "</strong></li>";
            }
            changes += "</ul>";
            if (changes === "<ul></ul>") {
                alert("No changes detected.");
                return;
            }
            showConfirmModal("⚠️ Confirm Update", changes, function() {
                unsavedChanges = false;
                document.getElementById('update_item_id').value = itemId;
                document.getElementById('update_item_name').value = newName;
                document.getElementById('update_quantity').value = newQuantity;
                document.getElementById('update_shelf_id').value = newShelfSelect.value;
                document.getElementById('update_category_id').value = newCategorySelect.value;
                document.getElementById('updateForm').submit();
            });
        }

        // --- Deletion functions ---
        function handleDelete(itemId, rowId) {
            const row = document.getElementById(rowId);
            const itemName = row.querySelector('.view-mode[data-field="item_name"]').innerText;
            const quantity = row.querySelector('.view-mode[data-field="quantity"]').innerText;
            const content = "<p>Are you sure you want to delete <strong>" + itemName + "</strong> (" + quantity + " pieces)?</p>";
            showConfirmModal("⚠️ Confirm Deletion", content, function() {
                window.location.href = "manage_items.php?delete=" + itemId;
            });
        }

        function handleBulkDelete() {
            let checked = Array.from(document.querySelectorAll('.bulk-checkbox'))
                .filter(cb => cb.checked);
            if (!checked.length) {
                alert("No items selected.");
                return;
            }
            let content = "<ul>";
            checked.forEach(cb => {
                let row = document.getElementById("row-" + cb.value);
                let itemName = row.querySelector('.view-mode[data-field="item_name"]').innerText;
                let quantity = row.querySelector('.view-mode[data-field="quantity"]').innerText;
                content += "<li style='margin-left: 50px; list-style-type: square;'>" + itemName + " (" + quantity + " pieces)</li>";
            });
            content += "</ul>";
            showConfirmModal("⚠️ Confirm Bulk Deletion on the following items:", content, function() {
                submitBulkDelete();
            });
        }

        function toggleBulkMode() {
            bulkModeEnabled = !bulkModeEnabled;
            document.querySelectorAll('.bulk-checkbox').forEach(cb => {
                if (bulkModeEnabled) {
                    cb.classList.remove('hidden');
                } else {
                    cb.classList.add('hidden');
                    cb.checked = false;
                }
            });
            const bulkBtn = document.getElementById('bulkDeleteBtn');
            if (bulkModeEnabled) {
                bulkBtn.classList.remove('hidden');
            } else {
                bulkBtn.classList.add('hidden');
            }
        }

        function toggleAll(source) {
            document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = source.checked);
        }

        function submitBulkDelete() {
            let checked = Array.from(document.querySelectorAll('.bulk-checkbox'))
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            if (!checked.length) {
                alert("No items selected.");
                return;
            }
            const bulkIdsContainer = document.getElementById('bulkIdsContainer');
            bulkIdsContainer.innerHTML = "";
            checked.forEach(id => {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = "bulk_item_ids[]";
                input.value = id;
                bulkIdsContainer.appendChild(input);
            });
            document.getElementById('bulkDeleteForm').submit();
        }
        window.addEventListener('beforeunload', (e) => {
            if (unsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</head>

<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">📦 Manage Items</h2>
                <a href="add_item.php" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                    ➕ Add Item
                </a>
            </div>
            <?php if ($message): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md"><?= $message ?></div>
            <?php endif; ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            <input type="checkbox" class="hidden bulk-checkbox" onclick="toggleAll(this)">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shelf</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($item = $itemsResult->fetch_assoc()): ?>
                        <tr id="row-<?= $item['ItemID'] ?>">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="<?= $item['ItemID'] ?>" class="bulk-checkbox hidden">
                            </td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="item_name"><?= htmlspecialchars($item['ItemName']) ?></span>
                                <input type="text" data-field="item_name" value="<?= htmlspecialchars($item['ItemName']) ?>" class="edit-mode hidden border rounded px-2 py-1">
                            </td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="quantity"><?= $item['Quantity'] ?></span>
                                <input type="number" data-field="quantity" value="<?= $item['Quantity'] ?>" class="edit-mode hidden border rounded px-2 py-1" min="0">
                            </td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="shelf"><?= htmlspecialchars(getShelfName($shelvesArray, $item['ShelfID'])) ?></span>
                                <select data-field="shelf_id" class="edit-mode hidden border rounded px-2 py-1">
                                    <?php foreach ($shelvesArray as $shelf): ?>
                                        <option value="<?= $shelf['ShelfID'] ?>" <?= ($shelf['ShelfID'] == $item['ShelfID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($shelf['ShelfLocation']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <span class="view-mode" data-field="category"><?= htmlspecialchars(getCategoryName($categoriesArray, $item['CategoryID'])) ?></span>
                                <select data-field="category_id" class="edit-mode hidden border rounded px-2 py-1">
                                    <?php foreach ($categoriesArray as $cat): ?>
                                        <option value="<?= $cat['CategoryID'] ?>" <?= ($cat['CategoryID'] == $item['CategoryID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['CategoryName']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <span class="view-mode">
                                    <button type="button" onclick="toggleEdit('row-<?= $item['ItemID'] ?>')" class="text-blue-500 hover:text-blue-700">✏️</button>
                                    <button type="button" onclick="handleDelete(<?= $item['ItemID'] ?>, 'row-<?= $item['ItemID'] ?>')" class="text-red-500 hover:text-red-700">🗑️</button>
                                </span>
                                <span class="edit-mode hidden">
                                    <button type="button" onclick="handleEditSave('row-<?= $item['ItemID'] ?>', <?= $item['ItemID'] ?>)" class="text-green-500 hover:text-green-700">✔️</button>
                                    <button type="button" onclick="cancelEdit('row-<?= $item['ItemID'] ?>')" class="text-red-500 hover:text-red-700">❌</button>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div class="mt-4 flex gap-2">
                <button id="bulkDeleteBtn" class="hidden bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600" onclick="handleBulkDelete()">🗑️ Delete Selected</button>
                <button onclick="toggleBulkMode()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">🗂️ Toggle Bulk Actions</button>
            </div>
        </div>
    </div>
    <!-- Hidden Update Form -->
    <form method="POST" action="manage_items.php" id="updateForm" class="hidden">
        <input type="hidden" name="update_item" value="1">
        <input type="hidden" id="update_item_id" name="item_id">
        <input type="hidden" id="update_item_name" name="item_name">
        <input type="hidden" id="update_quantity" name="quantity">
        <input type="hidden" id="update_shelf_id" name="shelf_id">
        <input type="hidden" id="update_category_id" name="category_id">
    </form>
    <!-- Hidden Bulk Delete Form -->
    <form method="POST" action="manage_items.php" id="bulkDeleteForm" class="hidden">
        <input type="hidden" name="bulk_delete" value="1">
        <div id="bulkIdsContainer"></div>
    </form>
    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center transition-opacity" onclick="hideConfirmModal()">
        <div class="bg-white p-6 rounded-lg w-96 modal-content transform transition-all">
            <div class="bg-white p-6 rounded-lg w-96">
                <h3 id="confirmModalTitle" class="text-xl font-bold mb-4"></h3>
                <div id="confirmModalContent" class="mb-4"></div>
                <div class="flex justify-end gap-2">
                    <button onclick="hideConfirmModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">Cancel</button>
                    <button onclick="confirmAction()" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>