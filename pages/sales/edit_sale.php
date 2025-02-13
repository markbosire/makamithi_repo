<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Retrieve the sale ID from the URL parameter
$sale_id = $_GET['id'] ?? null;

if (!$sale_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Sale ID is missing!</span>
          </div>";
    exit;
}

// Fetch the sale data from the database
$sql = "SELECT * FROM sales WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sale_id);
$stmt->execute();
$result = $stmt->get_result();
$sale = $result->fetch_assoc();

if (!$sale) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Sale not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the sale
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $discount = $_POST['discount'] ?? 0;
    $payment_method = $_POST['payment_method'];
    $payment_status = $_POST['payment_status'];
    $notes = $_POST['notes'];
    
    // Calculate total
    $total = ($unit_price * $quantity) - $discount;
    
    $sql = "UPDATE sales 
            SET client_id = ?, product_id = ?, quantity = ?, unit_price = ?, discount = ?, total = ?, payment_method = ?, payment_status = ?, notes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiidddsssi", $client_id, $product_id, $quantity, $unit_price, $discount, $total, $payment_method, $payment_status, $notes, $sale_id);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Sale updated successfully!</span>
              </div>";
        
        // Update inventory if the product or quantity changed
        if ($sale['product_id'] != $product_id || $sale['quantity'] != $quantity) {
            // Revert the old product's inventory
            $revert_inventory = "UPDATE inventory SET quantity = quantity + ? WHERE id = ?";
            $stmt = $conn->prepare($revert_inventory);
            $stmt->bind_param("ii", $sale['quantity'], $sale['product_id']);
            $stmt->execute();
            
            // Update the new product's inventory
            $update_inventory = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
            $stmt = $conn->prepare($update_inventory);
            $stmt->bind_param("ii", $quantity, $product_id);
            $stmt->execute();
        }
    }
}

// Get clients for dropdown
$clients = $conn->query("SELECT id, name FROM clients ORDER BY name");

// Get products for dropdown
$products = $conn->query("SELECT id, product_name, price, quantity FROM inventory WHERE quantity > 0 ORDER BY product_name");
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-receipt text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Sale Entry</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to sales
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Client Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Client</label>
                </div>
                <select id="client-select" name="client_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    <option value="">Select Client</option>
                    <?php while($client = $clients->fetch_assoc()): ?>
                        <option value="<?php echo $client['id']; ?>" <?php echo $client['id'] == $sale['client_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Product Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-package text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Product</label>
                </div>
                <select id="product-select" name="product_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200">
                    <option value="">Select Product</option>
                    <?php while($product = $products->fetch_assoc()): ?>
                        <option value="<?php echo $product['id']; ?>" 
                                data-price="<?php echo $product['price']; ?>"
                                data-stock="<?php echo $product['quantity']; ?>"
                                <?php echo $product['id'] == $sale['product_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['product_name']); ?> 
                            (Stock: <?php echo $product['quantity']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Quantity and Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-list-ol text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Quantity</label>
                    </div>
                    <input type="number" name="quantity" required min="1" 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           value="<?php echo $sale['quantity']; ?>"
                           onchange="calculateTotal()">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dollar-circle text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Unit Price</label>
                    </div>
                    <input type="number" name="unit_price" required step="0.01" 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           value="<?php echo $sale['unit_price']; ?>"
                           onchange="calculateTotal()">
                </div>
            </div>

            <!-- Payment Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-tag-alt text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Discount</label>
                    </div>
                    <input type="number" name="discount" value="<?php echo $sale['discount']; ?>" step="0.01" 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           onchange="calculateTotal()">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-credit-card text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Payment Method</label>
                    </div>
                    <select name="payment_method" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="cash" <?php echo $sale['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="card" <?php echo $sale['payment_method'] == 'card' ? 'selected' : ''; ?>>Card</option>
                        <option value="mobile_money" <?php echo $sale['payment_method'] == 'mobile_money' ? 'selected' : ''; ?>>Mobile Money</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-check-circle text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Payment Status</label>
                    </div>
                    <select name="payment_status" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="completed" <?php echo $sale['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo $sale['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="cancelled" <?php echo $sale['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Total Display -->
            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                <div class="flex items-center mb-2">
                    <i class='bx bx-calculator text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Total Amount</label>
                </div>
                <div id="total-amount" class="text-xl md:text-2xl font-bold text-indigo-600">$<?php echo number_format($sale['total'], 2); ?></div>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Notes</label>
                </div>
                <textarea name="notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" rows="3"><?php echo htmlspecialchars($sale['notes']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Update Sale
            </button>
        </form>
    </div>
</div>

<script>
    // Initialize Tom Select for client dropdown
new TomSelect('#client-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for a client...',
    plugins: ['clear_button'],
});

// Initialize Tom Select for product dropdown
new TomSelect('#product-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for a product...',
    plugins: ['clear_button'],
    onChange: function(value) {
        updatePrice(value);
    }
});

// Add custom styles to match your design
document.addEventListener('DOMContentLoaded', function() {
    // Add these styles to match your existing design
    const style = document.createElement('style');
    style.textContent = `
        .ts-control {
            background-color: rgb(249 250 251) !important;
            border-color: rgb(229 231 235) !important;
            border-radius: 0.5rem !important;
            padding: 0.75rem !important;
        }
        .ts-control:focus {
            box-shadow: 0 0 0 2px rgb(199 210 254) !important;
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
        }
        .ts-dropdown .active {
            background-color: rgb(99 102 241) !important;
            color: white !important;
        }
    `;
    document.head.appendChild(style);
});

function updatePrice(productId) {
    const select = document.querySelector('select[name="product_id"]');
    const option = select.options[select.selectedIndex];
    const priceInput = document.querySelector('input[name="unit_price"]');
    
    if (option.dataset.price) {
        priceInput.value = option.dataset.price;
        calculateTotal();
    }
}

function calculateTotal() {
    const quantity = document.querySelector('input[name="quantity"]').value || 0;
    const unitPrice = document.querySelector('input[name="unit_price"]').value || 0;
    const discount = document.querySelector('input[name="discount"]').value || 0;
    
    const total = (quantity * unitPrice) - discount;
    document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
}
</script>

<?php require_once '../../includes/footer.php'; ?>