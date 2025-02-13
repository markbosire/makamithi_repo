<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';
 
// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$supplier = isset($_GET['supplier']) ? htmlspecialchars($_GET['supplier']) : '';
$expiry_date_from = isset($_GET['expiry_date_from']) ? htmlspecialchars($_GET['expiry_date_from']) : '';
$expiry_date_to = isset($_GET['expiry_date_to']) ? htmlspecialchars($_GET['expiry_date_to']) : '';

// Modify the SQL query to include filters
$sql = "SELECT * FROM inventory WHERE 1=1";

if ($search) {
    $sql .= " AND (product_name LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($category) {
    $sql .= " AND category = '$category'";
}
if ($supplier) {
    $sql .= " AND supplier = '$supplier'";
}
if ($expiry_date_from) {
    $sql .= " AND DATE(expiry_date) >= '$expiry_date_from'";
}
if ($expiry_date_to) {
    $sql .= " AND DATE(expiry_date) <= '$expiry_date_to'";
}

$sql .= " ORDER BY last_updated DESC";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/inventory/add_item.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-add-to-queue'></i>
                Add Inventory Item
            </button>
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo $search; ?>"
                           class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Search inventory..."
                    >
                    <button id="searchBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-1 bg-yellow-400 border-2 border-black rounded-lg hover:bg-yellow-500 transition-colors">
                        <i class='bx bx-search'></i>
                    </button>
                </div>
                <!-- Autocomplete Dropdown (Initially Hidden) -->
                <div id="searchResults" class="hidden absolute z-10 w-fit mt-1 bg-white border-2 border-black rounded-lg shadow-lg">
                    <!-- Results will be populated via JavaScript -->
                </div>
            </div>
            <!-- Filter Buttons -->
            <div class="flex gap-4">
                <button id="filterToggle" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-filter'></i>
                    Filters
                </button>
                <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/inventory/list_inventory.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-reset'></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Expanded Filters (Initially Hidden) -->
        <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block font-medium">Category</label>
                <select id="categoryFilter" name="category" placeholder="Select category..." class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">All Categories</option>
                    <option value="medication">Medication</option>
                    <option value="supplies">Supplies</option>
                    <option value="food">Food</option>
                    <option value="accessories">Accessories</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Supplier</label>
                <select id="supplierFilter" name="supplier" placeholder="Search for a supplier...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Expiry Date From</label>
                <input type="date" name="expiry_date_from" value="<?php echo $expiry_date_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Expiry Date To</label>
                <input type="date" name="expiry_date_to" value="<?php echo $expiry_date_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
        </div>
    </div>
<!-- Inventory Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Product Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Product Name</th>
                    <!-- Category: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Category</th>
                    <!-- Quantity: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Quantity</th>
                    <!-- Price: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Price</th>
                    <!-- Supplier: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Supplier</th>
                    <!-- Expiry Date: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Expiry Date</th>
                    <!-- Actions: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Product Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['product_name']; ?></td>
                    <!-- Category: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo ucfirst($row['category']); ?></td>
                    <!-- Quantity: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['quantity']; ?></td>
                    <!-- Price: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo number_format($row['price'], 2); ?></td>
                    <!-- Supplier: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['supplier']; ?></td>
                    <!-- Expiry Date: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></td>
                    <!-- Actions: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#inventoryDetailsModal" class="viewDetails" rel="modal:open" data-inventory-id="<?php echo $row['id']; ?>">
                                    <i class='bx bx-show text-xl'></i>
                                </a>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Add modal for inventory details -->
<div id="inventoryDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: 100vw !important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full gap-4 pb-2 md:pb-0">
            <h3 class="hidden md:block text-xl font-medium text-slate-800 px-2 md:px-0">Inventory Details</h3>
            <button id="edit-inventory" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-edit'></i>
                Edit Inventory
            </button>
        </div>
        <div id="modalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
    const BASE_URL = "<?php echo BASE_URL ?>";

    // Toggle filter panel
    document.getElementById('filterToggle').addEventListener('click', function() {
        const filterPanel = document.getElementById('filterPanel');
        filterPanel.classList.toggle('hidden');
    });

    // Handle filter changes
    document.querySelectorAll('select, input[type="date"]').forEach(element => {
        element.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set(this.name, this.value);
            window.location = url;
        });
    });

    const supplierFilter = new TomSelect('#supplierFilter', {
        valueField: 'supplier',
        labelField: 'supplier',
        searchField: 'supplier',
        placeholder: 'Search for a supplier...',
        load: function(query, callback) {
            // Fetch supplier names from the database based on the search query
            fetch(`${BASE_URL}/api/get_supplier_names.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    callback(data); // Pass the data to Tom Select
                })
                .catch(() => {
                    callback(); // Handle errors
                });
        },
        render: {
            option: function(item, escape) {
                // Custom rendering for each supplier option
                return `<div>${escape(item.supplier)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected supplier
                return `<div>${escape(item.supplier)}</div>`;
            }
        }
    });

    // Search autocomplete
    let searchTimeout;
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value;

        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`${BASE_URL}/api/search_inventory.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                            div.textContent = `${item.product_name} - ${item.category}`;
                            div.addEventListener('click', () => {
                                searchInput.value = item.product_name;
                                searchResults.classList.add('hidden');
                                const url = new URL(window.location);
                                url.searchParams.set('search', item.product_name);
                                window.location = url;
                            });
                            searchResults.appendChild(div);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.classList.add('hidden');
                    }
                });
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // View inventory details
    document.querySelectorAll('.viewDetails').forEach(button => {
        button.addEventListener('click', function() {
            const inventoryId = this.getAttribute('data-inventory-id');
            setCookie("currentInventoryId", inventoryId, 1);
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

            fetch(`${BASE_URL}/api/view_inventory_details.php?id=${inventoryId}`)
                .then(response => response.json())
                .then(data => {
                  modalContent.innerHTML = `
    <div class="space-y-6 p-2 md:p-4 bg-white rounded-lg">
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
            <!-- Product Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg h-fit w-full">
                <table class="w-full text-left max-h-auto">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-package text-xl text-green-600'></i> Product Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Product Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.product_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Category</td>
                            <td class="px-4 py-3 text-gray-800">${data.category || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Description</td>
                            <td class="px-4 py-3 text-gray-800">${data.description || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Price</td>
                            <td class="px-4 py-3 text-gray-800">${data.price ? '$' + parseFloat(data.price).toFixed(2) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Cost Price</td>
                            <td class="px-4 py-3 text-gray-800">${data.cost_price ? '$' + parseFloat(data.cost_price).toFixed(2) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Quantity</td>
                            <td class="px-4 py-3 text-gray-800">${data.quantity || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Reorder Level</td>
                            <td class="px-4 py-3 text-gray-800">${data.reorder_level || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Supplier</td>
                            <td class="px-4 py-3 text-gray-800">${data.supplier || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Expiry Date</td>
                            <td class="px-4 py-3 text-gray-800">${data.expiry_date ? new Date(data.expiry_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Last Updated</td>
                            <td class="px-4 py-3 text-gray-800">${data.last_updated ? new Date(data.last_updated).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
`;

                });
        });
    });

    // Edit inventory
    document.getElementById('edit-inventory').addEventListener('click', function() {
        const inventoryId = getCookie("currentInventoryId");
        if (inventoryId) {
            window.location.href = `${BASE_URL}/pages/inventory/edit_item.php?id=${inventoryId}`;
        }
    });

    // Cookie functions
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++)
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Set initial filter values based on URL parameters
       const customStyles = `
    .ts-control {
          font-family: "IBM Plex Sans", serif;
          font-optical-sizing: auto;
          font-weight: 500;
          font-style: normal;
          font-variation-settings:
            "wdth" 100;
        font-size: 1rem !important;
        border: 2px solid black !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem 1rem !important;
        box-shadow: none !important;
        background: white !important;
    }

    .ts-control:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgb(250 204 21) !important;
    }

    .ts-dropdown {
        border: 2px solid black !important;
        border-radius: 0.5rem !important;
        padding: 0.25rem !important;
        margin-top: 0.25rem !important;
        box-shadow: 4px 4px 0px 0px rgba(0,0,0,1) !important;
        background: white !important;
    }

    .ts-dropdown .option {
        padding: 0.5rem 1rem !important;
        border-radius: 0.25rem !important;
    }

    .ts-dropdown .active {
        background-color: rgb(250 204 21) !important;
        color: black !important;
    }

    .ts-dropdown .option:hover {
        background-color: rgb(253 224 71) !important;
        color: black !important;
    }

    .ts-control input::placeholder {
        color: #6b7280 !important;
    }

    .ts-control .item {
        background: rgb(250 204 21) !important;
        border: 2px solid black !important;
        border-radius: 0.25rem !important;
        color: black !important;
        padding: 0.25rem 0.5rem !important;
    }

    .ts-control .clear-button {
        color: black !important;
    }

    .ts-wrapper.loading .ts-control::before {
        border-color: black !important;
    }
`;

// Create style element
const styleElement = document.createElement('style');
styleElement.textContent = customStyles;

// Insert styles after Tom Select is initialized
supplierFilter.wrapper.appendChild(styleElement);
document.querySelectorAll('#statusFilter, #paymentStatusFilter').forEach(element => {
    element.addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set(this.name, this.value);
        window.location = url;
    });
});
 
</script>

<?php require_once '../../includes/footer.php'; ?>