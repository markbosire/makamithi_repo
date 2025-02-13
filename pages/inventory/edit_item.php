<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Retrieve the inventory ID from the URL parameter
$inventory_id = $_GET['id'] ?? null;

if (!$inventory_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Inventory ID is missing!</span>
          </div>";
    exit;
}

// Fetch the inventory data from the database
$sql = "SELECT * FROM inventory WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inventory_id);
$stmt->execute();
$result = $stmt->get_result();
$inventory = $result->fetch_assoc();

if (!$inventory) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Inventory item not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the inventory
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $quantity = $_POST['quantity'];
    $reorder_level = $_POST['reorder_level'];
    $supplier = $_POST['supplier'];
    $expiry_date = $_POST['expiry_date'];
    
    $sql = "UPDATE inventory 
            SET product_name = ?, category = ?, description = ?, price = ?, cost_price = ?, 
                quantity = ?, reorder_level = ?, supplier = ?, expiry_date = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $product_name, $category, $description, $price, $cost_price, 
                                $quantity, $reorder_level, $supplier, $expiry_date, $inventory_id);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Inventory updated successfully!</span>
              </div>";
    } else {
        echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
                <span class='text-red-700 font-medium'>Error updating inventory: " . $stmt->error . "</span>
              </div>";
    }
}
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-package text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Inventory</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/inventory/list_inventory.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to inventory
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-package text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Product Name</label>
                    </div>
                    <input type="text" name="product_name" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['product_name']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-category text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Category</label>
                    </div>
                    <select name="category" required
                            class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                        <option value="medication" <?php echo $inventory['category'] == 'medication' ? 'selected' : ''; ?>>Medication</option>
                        <option value="supplies" <?php echo $inventory['category'] == 'supplies' ? 'selected' : ''; ?>>Supplies</option>
                        <option value="food" <?php echo $inventory['category'] == 'food' ? 'selected' : ''; ?>>Food</option>
                        <option value="accessories" <?php echo $inventory['category'] == 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dollar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Price</label>
                    </div>
                    <input type="number" step="0.01" name="price" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['price']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dollar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Cost Price</label>
                    </div>
                    <input type="number" step="0.01" name="cost_price" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['cost_price']); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-box text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Quantity</label>
                    </div>
                    <input type="number" name="quantity" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['quantity']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-flag text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Reorder Level</label>
                    </div>
                    <input type="number" name="reorder_level" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['reorder_level']); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-building text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Supplier</label>
                    </div>
                    <input type="text" name="supplier" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['supplier']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Expiry Date</label>
                    </div>
                    <input type="date" name="expiry_date"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($inventory['expiry_date']); ?>">
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-file text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Description</label>
                </div>
                <textarea name="description" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" rows="3"><?php echo htmlspecialchars($inventory['description']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Update Inventory
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>