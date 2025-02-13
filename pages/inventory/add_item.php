<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $quantity = $_POST['quantity'];
    $reorder_level = $_POST['reorder_level'];
    $supplier = $_POST['supplier'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    // Validate the date format if expiry_date is provided
    if ($expiry_date !== null && !DateTime::createFromFormat('Y-m-d', $expiry_date)) {
        $expiry_date = null; // Set to null if the date format is invalid
    }

    $sql = "INSERT INTO inventory (product_name, category, description, price, cost_price, quantity, reorder_level, supplier, expiry_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssddiiss", $product_name, $category, $description, $price, $cost_price, $quantity, $reorder_level, $supplier, $expiry_date);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Inventory item added successfully!</span>
              </div>";
    } else {
        echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
                <span class='text-red-700 font-medium'>Error: " . $stmt->error . "</span>
              </div>";
    }
}
?>
<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
        
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-package text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Add Inventory Item</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/inventory/list_inventory.php'" 
                    class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                           rounded-lg hover:bg-indigo-600 transition-all duration-300 
                           transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to Inventory
            </button>
        </div>

        <form method="POST" class="space-y-6">
            <!-- Product Name -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-tag text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Product Name</label>
                </div>
                <input type="text" name="product_name" required 
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
            </div>

            <!-- Category -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-category text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Category</label>
                </div>
                <select name="category" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    <option value="">Select Category</option>
                    <option value="medication">Medication</option>
                    <option value="supplies">Supplies</option>
                    <option value="food">Food</option>
                    <option value="accessories">Accessories</option>
                </select>
            </div>

            <!-- Price and Cost Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dollar-circle text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Selling Price</label>
                    </div>
                    <input type="number" name="price" step="0.01" min="0" required 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-purchase-tag text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Cost Price</label>
                    </div>
                    <input type="number" name="cost_price" step="0.01" min="0" required 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                </div>
            </div>

            <!-- Quantity and Reorder Level -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-box text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Quantity</label>
                    </div>
                    <input type="number" name="quantity" min="0" required 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-pulse text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Reorder Level</label>
                    </div>
                    <input type="number" name="reorder_level" min="0" required 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                </div>
            </div>

            <!-- Supplier -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-store text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Supplier</label>
                </div>
                <input type="text" name="supplier" 
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
            </div>

            <!-- Expiry Date -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-calendar-exclamation text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Expiry Date</label>
                </div>
                <input type="date" name="expiry_date" 
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
            </div>

            <!-- Description -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-info-circle text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Description</label>
                </div>
                <textarea name="description" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" 
                          rows="3"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Add to Inventory
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>