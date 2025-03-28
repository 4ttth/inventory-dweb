<?php
session_start();
include 'db_connect.php';

// If this is an AJAX request, perform the search and return the results snippet.
if (isset($_GET['ajax'])) {
    $searchQuery = $conn->real_escape_string($_GET['search_query']);
    $result = $conn->query("SELECT item*, shelf.ShelfLocation, category.CategoryName 
                       FROM item 
                       LEFT JOIN shelf ON item.ShelfID = shelf.ShelfID
                       LEFT JOIN category ON item.CategoryID = category.CategoryID
                       WHERE item.ItemName LIKE '%$searchQuery%'");
?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Photo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shelf Location</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img src="<?= $row['photo'] ?>"
                                alt="<?= htmlspecialchars($row['ItemName']) ?>"
                                class="w-16 h-12 object-cover cursor-pointer hover:scale-110 transition-transform duration-200 transform-gpu"
                                onclick="showItemModal(<?= $row['ItemID'] ?>)">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= $row['ItemID'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['ItemName']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= $row['Quantity'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['ShelfLocation']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php
    exit;
}
?>

<!DOCTYPE html>
<html>

<head><link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
    <title>Search Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="styles.css">
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("search_query");
            const resultsContainer = document.getElementById("resultsContainer");

            searchInput.addEventListener("keyup", function() {
                const query = searchInput.value;
                if (query.trim() === "") {
                    resultsContainer.innerHTML = "";
                    return;
                }
                fetch("search_item.php?ajax=1&search_query=" + encodeURIComponent(query))
                    .then(response => response.text())
                    .then(data => {
                        resultsContainer.innerHTML = data;
                    });
            });
        });
    </script>
</head>

<body>
    <div class="container mx-auto px-4 py-8">
        <?php include 'nav.php'; ?>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6">🔍 Search Items</h2>
            <form method="post" class="mb-6" onsubmit="return false;">
                <div class="flex gap-2">
                    <input type="text" id="search_query" name="search_query"
                        class="flex-1 px-3 py-2 border rounded-md"
                        placeholder="Enter item name..." required>
                </div>
            </form>
            <!-- This div will be populated with search results as the user types -->
            <div id="resultsContainer"></div>
        </div>
    </div>
    <div id="itemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50" onclick="closeModal()">
        <div class="bg-white p-6 rounded-lg max-w-2xl mx-auto mt-20">
            <div class="flex justify-between mb-4">
                <h3 class="text-xl font-bold" id="modalTitle"></h3>
                <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800">✖️</button>
            </div>
            <img id="modalImage" src="" class="w-full h-64 object-cover mb-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <p>ID: <span id="modalId"></span></p>
                <p>Category: <span id="modalCategory"></span></p>
                <p>Quantity: <span id="modalQuantity"></span></p>
                <p>Shelf: <span id="modalShelf"></span></p>
            </div>
            <button onclick="copyItemId()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Copy ID to Clipboard
            </button>
        </div>
    </div>

    <script>
        let currentItemId = null;

        function showItemModal(itemId) {
            currentItemId = itemId;
            fetch('get_item_details.php?item_id=' + itemId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = data.ItemName;
                    document.getElementById('modalImage').src = data.photo;
                    document.getElementById('modalId').textContent = data.ItemID;
                    document.getElementById('modalCategory').textContent = data.CategoryName;
                    document.getElementById('modalQuantity').textContent = data.Quantity;
                    document.getElementById('modalShelf').textContent = data.ShelfLocation;
                    document.getElementById('itemModal').classList.remove('hidden');
                });
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        function copyItemId() {
            navigator.clipboard.writeText(currentItemId)
                .then(() => alert('Item ID copied to clipboard!'))
                .catch(err => console.error('Failed to copy:', err));
        }
    </script>
</body>

</html>