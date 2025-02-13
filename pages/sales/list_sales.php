<?php
require_once './auth/middleware.php';
checkAuth(); 
// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : '';
$payment_method = isset($_GET['payment_method']) ? htmlspecialchars($_GET['payment_method']) : '';
$payment_status = isset($_GET['payment_status']) ? htmlspecialchars($_GET['payment_status']) : '';
$date_from = isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '';

$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : '';
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : '';

// Modify the SQL query to include price range
$sql = "SELECT s.*, c.name as client_name, i.product_name, i.category, i.description, i.price as unit_price
        FROM sales s 
        LEFT JOIN clients c ON s.client_id = c.id 
        LEFT JOIN inventory i ON s.product_id = i.id 
        WHERE 1=1";

if ($search) {
    $sql .= " AND (c.name LIKE '%$search%' OR i.product_name LIKE '%$search%')";
}
if ($category) {
    $sql .= " AND i.category = '$category'";
}
if ($payment_method) {
    $sql .= " AND s.payment_method = '$payment_method'";
}
if ($payment_status) {
    $sql .= " AND s.payment_status = '$payment_status'";
}

if ($price_min !== '') {
    $sql .= " AND s.total >= $price_min";
}
if ($price_max !== '') {
    $sql .= " AND s.total <= $price_max";
}
if ($date_from) {
    $sql .= " AND DATE(s.sale_date) >= '$date_from'";
}
if ($date_to) {
    $sql .= " AND DATE(s.sale_date) <= '$date_to'";
}

$sql .= " ORDER BY s.sale_date DESC";
$result = $conn->query($sql);
?>

<!-- Search and Filters Section -->
<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
             <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/sales/add_sale.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-add-to-queue'></i>
                    Add sale
                </button>
            <!-- Search Bar -->
            <div class="flex-1">
                            <div class="relative">
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?php echo $search; ?>"
                       class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                       placeholder="Search sales..."
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
            <!-- Add search button -->

            <!-- Filter Buttons -->
            <div class="flex gap-4">
                <button id="filterToggle" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-filter'></i>
                    Filters
                </button>
                <button onclick="window.location.href='<?php echo BASE_URL;?>'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-reset'></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Expanded Filters (Initially Hidden) -->
        <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block font-medium">Category</label>
                <select name="category" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">All Categories</option>
                    <option value="medication" <?php echo $category === 'medication' ? 'selected' : ''; ?>>Medication</option>
                    <option value="supplies" <?php echo $category === 'supplies' ? 'selected' : ''; ?>>Supplies</option>
                    <option value="food" <?php echo $category === 'food' ? 'selected' : ''; ?>>Food</option>
                    <option value="accessories" <?php echo $category === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">All Methods</option>
                    <option value="cash" <?php echo $payment_method === 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="card" <?php echo $payment_method === 'card' ? 'selected' : ''; ?>>Card</option>
                    <option value="mobile_money" <?php echo $payment_method === 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Date From</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Date To</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
                <!-- Existing filters -->
    <div class="space-y-2">
        <label class="block font-medium">Status</label>
        <select name="payment_status" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            <option value="">All Status</option>
            <option value="active" <?php echo $payment_status === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="completed" <?php echo $payment_status === 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?php echo $payment_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>
    </div>
    <div class="space-y-2">
    <label class="block font-medium">Price Range</label>
    <div class="flex gap-2">
        <input type="number" name="price_min" value="<?php echo $price_min; ?>" placeholder="Min" class="w-full px-3 py-2 border-2 border-black rounded-lg">
        <input type="number" name="price_max" value="<?php echo $price_max; ?>" placeholder="Max" class="w-full px-3 py-2 border-2 border-black rounded-lg">
        <button id="priceRangeSubmit" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
        <i class='bx bxs-badge-dollar'></i>
           Apply
        </button>
    </div>
    </div>
        </div>
    </div>

    <!-- Sales Table -->
   <!-- Sales Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <th class="hidden lg:table-cell px-6 py-3 text-left text-sm font-semibold">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Client</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Product</th>
                    <th class="hidden lg:table-cell px-6 py-3 text-left text-sm font-semibold">Quantity</th>
                    <th class="hidden md:table-cell px-6 py-3 text-left text-sm font-semibold">Total</th>
                    <th class="hidden lg:table-cell px-6 py-3 text-left text-sm font-semibold">Payment</th>
                    <th class="hidden md:table-cell px-6 py-3 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="hidden lg:table-cell px-6 py-4 text-sm">
                        <?php echo date('M d, Y', strtotime($row['sale_date'])); ?>
                    </td>
                    <td class="px-6 py-4 text-sm"><?php echo $row['client_name']; ?></td>
                    <td class="px-6 py-4 text-sm"><?php echo $row['product_name']; ?></td>
                    <td class="hidden lg:table-cell px-6 py-4 text-sm"><?php echo $row['quantity']; ?></td>
                    <td class="hidden md:table-cell px-6 py-4 text-sm">$<?php echo number_format($row['total'], 2); ?></td>
                    <td class="hidden lg:table-cell px-6 py-4 text-sm">
                        <span class="inline-flex items-center gap-1">
                            <i class='bx bx-<?php echo $row['payment_method'] === 'cash' ? 'money' : ($row['payment_method'] === 'card' ? 'credit-card' : 'mobile'); ?>'></i>
                            <?php echo ucfirst($row['payment_method']); ?>
                        </span>
                    </td>
                    <td class="hidden md:table-cell px-6 py-4 text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            <?php echo $row['payment_status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                ($row['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                'bg-red-100 text-red-800'); ?>">
                            <?php echo ucfirst($row['payment_status']); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#saleDetailsModal" class="viewDetails" rel="modal:open" data-sale-id="<?php echo $row['id']; ?>">
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



<!-- Add modal for product details -->
<div id="saleDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: 100vw !important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full pb-2 md:pb-0">
            <h3 class="hidden md:block text-xl font-medium text-slate-800 px-2 md:px-0">Sale Details</h3>
             <button id="edit-sale" class="ml-2 md:ml-0  px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-add-to-queue'></i>
                    Edit sale
                </button>
               
        </div>
        <div id="modalContent" >
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>




<script>
    // Initialize Preline

const BASE_URL = "<?php echo BASE_URL; ?>";

// Toggle filter panel
document.getElementById('filterToggle').addEventListener('click', function() {
    const filterPanel = document.getElementById('filterPanel');
    filterPanel.classList.toggle('hidden');
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
        fetch(`${BASE_URL}/api/search_sales.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {

                searchResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                        div.textContent = `${item.product_name} - ${item.client_name}`;
                        div.addEventListener('click', () => {
                            searchInput.value = item.product_name;
                            searchResults.classList.add('hidden');
                            // Update URL and refresh results
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

// Handle filter changes
document.querySelectorAll('select, input[type="date"]').forEach(element => {
    element.addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set(this.name, this.value);
        window.location = url;
    });
});

document.getElementById('priceRangeSubmit').addEventListener('click', function() {
    // Get the price range values
    var priceMin = document.getElementsByName('price_min')[0].value;
    var priceMax = document.getElementsByName('price_max')[0].value;

    // Update the URL query string with the price range values
    var url = new URL(window.location.href);
    url.searchParams.set('price_min', priceMin);
    url.searchParams.set('price_max', priceMax);

    // Reload the page with the updated query string
    window.location.href = url.href;
});
    document.getElementById('edit-sale').addEventListener('click', function() {
          const saleId = getCookie("currentSaleId")
   window.location.href=`${BASE_URL}/pages/sales/edit_sale.php?id=${saleId}`
});
  

// Add this to your existing script section
document.querySelectorAll('.viewDetails').forEach(button => {

    button.addEventListener('click', function() {
        const saleId = this.getAttribute('data-sale-id');
        setCookie("currentSaleId",saleId,1)
        const modalContent = document.getElementById('modalContent');
        console.log(saleId)
        // Show loading state
        modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';
        
fetch(`${BASE_URL}/api/view_sale_details.php?id=${saleId}`)
  .then(response => {
    if (!response.ok) {
      return response.json().then(err => { throw new Error(err.error || 'Network response was not ok.') });
    }
    return response.json();
  })
  .then(data => {
    if (data.error) {
      throw new Error(data.error);
    }
   
    // Format currency values
    const formatCurrency = (value) => {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(value);
    };

    // Format date
    const formatDate = (dateString) => {
      return dateString ? new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }) : 'N/A';
    };

modalContent.innerHTML = `
  <div class="space-y-6 p-2 md:p-4 bg-white rounded-lg">
  <!-- Client & Product Info Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Client Info Table -->
    <div class="overflow-x-auto border-2 border-black rounded-lg">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-gray-50 border-b-2 border-black">
            <th colspan="2" class="px-4 py-3">
              <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                <i class='bx bx-user text-xl text-blue-600'></i>
                <span>Client Information</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y-2 divide-gray-100">
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-id-card text-gray-500'></i>
              <span>Name</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.client_name || 'N/A'}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-envelope text-gray-500'></i>
              <span>Email</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.email || 'N/A'}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-phone text-gray-500'></i>
              <span>Phone</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.phone || 'N/A'}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Product Info Table -->
    <div class="overflow-x-auto border-2 border-black rounded-lg">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-gray-50 border-b-2 border-black">
            <th colspan="2" class="px-4 py-3">
              <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                <i class='bx bx-package text-xl text-purple-600'></i>
                <span>Product Information</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y-2 divide-gray-100">
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-cube text-gray-500'></i>
              <span>Name</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.product_name || 'N/A'}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-category text-gray-500'></i>
              <span>Category</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.category ? data.category.charAt(0).toUpperCase() + data.category.slice(1) : 'N/A'}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-text text-gray-500'></i>
              <span>Description</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.description || 'N/A'}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-dollar text-gray-500'></i>
              <span>Unit Price</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${formatCurrency(data.unit_price)}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sales & Notes Section -->
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <!-- Sale Info Table -->
    <div class="overflow-x-auto border-2 border-black rounded-lg">
      <table class="w-full text-left">
        <thead>
          <tr class="bg-gray-50 border-b-2 border-black">
            <th colspan="2" class="px-4 py-3">
              <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                <i class='bx bx-receipt text-xl text-green-600'></i>
                <span>Sale Information</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y-2 divide-gray-100">
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-calculator text-gray-500'></i>
              <span>Quantity</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.quantity}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-coin-stack text-gray-500'></i>
              <span>Discount</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${formatCurrency(data.discount)}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-credit-card text-gray-500'></i>
              <span>Payment Method</span>
            </td>
            <td class="px-4 py-3 text-gray-800">${data.payment_method ? data.payment_method.replace('_', ' ').toUpperCase() : 'N/A'}</td>
          </tr>
          <tr>
            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
              <i class='bx bx-badge-check text-gray-500'></i>
              <span>Status</span>
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border-2 border-current
                ${data.payment_status === 'completed' ? 'bg-green-100 text-green-800' : 
                  data.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                  'bg-red-100 text-red-800'}">
                ${data.payment_status ? data.payment_status.toUpperCase() : 'N/A'}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Notes Section -->
    <div class="bg-gray-50 p-4 rounded-lg border-2 border-black max-h-dull overflow-y-auto">
      <h4 class="font-semibold text-gray-700 text-lg mb-2 flex items-center gap-2">
        <i class='bx bx-note text-xl text-orange-600'></i>
        <span>Notes</span>
      </h4>
      <p class="text-gray-800">${data.notes || 'No additional notes'}</p>
    </div>

  </div>
 <div class="w-full inline-flex items-center justify-center"><div class="neobrutalist-button inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 border-2 border-gray-300 rounded-md font-mono text-sm uppercase cursor-pointer transition-colors duration-200 shadow-md hover:bg-gray-200 hover:border-gray-400 active:bg-gray-300 active:border-gray-500 active:shadow-none active:translate-x-1 active:translate-y-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:shadow-sm dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:active:bg-gray-600 dark:active:border-gray-500 dark:active:shadow-none" onclick="generatePDF(${data.id})">
  <i class='bx bx-download text-xl mr-1'></i>  <span>Download Receipt</span>
</div></div>
</div>

`;
  })
  .catch(error => {
    console.error("Error fetching sale details:", error);
    modalContent.innerHTML = `
      <div class="p-4">
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">Error loading sale details: ${error.message}</p>
            </div>
          </div>
        </div>
      </div>
    `;
  });
    });
});
  $(document).ready(function() {
     $("#custom-close").modal({
  closeClass: 'icon-remove',
  closeText: 'X'
});})

</script>