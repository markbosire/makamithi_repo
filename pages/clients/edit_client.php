<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Retrieve the client ID from the URL parameter
$client_id = $_GET['id'] ?? null;

if (!$client_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Client ID is missing!</span>
          </div>";
    exit;
}

// Fetch the client data from the database
$sql = "SELECT * FROM clients WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();

if (!$client) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Client not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the client
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact_primary = $_POST['contact_primary'];
    $contact_emergency = $_POST['contact_emergency'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    $sql = "UPDATE clients 
            SET name = ?, email = ?, contact_primary = ?, contact_emergency = ?, 
                address = ?, city = ?, postal_code = ?, notes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $name, $email, $contact_primary, $contact_emergency, 
                                $address, $city, $postal_code, $notes, $client_id);
    
    if ($stmt->execute()) {
      
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Client updated successfully!</span>
              </div>";

    } else {
        echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
                <span class='text-red-700 font-medium'>Error updating client: " . $stmt->error . "</span>
              </div>";
    }
}
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-user-circle text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Client</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/clients/list_clients.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to clients
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-user text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Full Name</label>
                    </div>
                    <input type="text" name="name" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($client['name']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-envelope text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Email</label>
                    </div>
                    <input type="email" name="email"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($client['email']); ?>">
                </div>
            </div>

            <!-- Contact Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-phone text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Primary Contact</label>
                    </div>
                    <input type="text" name="contact_primary" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($client['contact_primary']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-phone-call text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Emergency Contact</label>
                    </div>
                    <input type="text" name="contact_emergency"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($client['contact_emergency']); ?>">
                </div>
            </div>

            <!-- Address Information -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-home text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Address</label>
                </div>
                <textarea name="address" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" rows="2"><?php echo htmlspecialchars($client['address']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-buildings text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">City</label>
                    </div>
                    <input type="text" name="city"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($client['city']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-mailbox text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Postal Code</label>
                    </div>
                    <input type="text" name="postal_code"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($client['postal_code']); ?>">
                </div>
            </div>

            <!-- Client Information -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Notes</label>
                </div>
                <textarea name="notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" rows="3"><?php echo htmlspecialchars($client['notes']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Update Client
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>