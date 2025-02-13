<?php
require_once '../../auth/middleware.php';
checkAdmin(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';


// Retrieve the user ID from the URL parameter
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>User ID is missing!</span>
          </div>";
    exit;
}

// Fetch the user data from the database
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>User not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    
    $sql = "UPDATE users 
            SET username = ?, full_name = ?, role = ?, email = ?, contact = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $username, $full_name, $role, $email, $contact, $user_id);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>User updated successfully!</span>
              </div>";
    } else {
        echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
                <span class='text-red-700 font-medium'>Error updating user: " . $stmt->error . "</span>
              </div>";
    }
}
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-user text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit User</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/users/list_users.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to users
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-user text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Username</label>
                    </div>
                    <input type="text" name="username" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($user['username']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-user-pin text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Full Name</label>
                    </div>
                    <input type="text" name="full_name" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-envelope text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Email</label>
                    </div>
                    <input type="email" name="email" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-phone text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Contact</label>
                    </div>
                    <input type="text" name="contact" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($user['contact']); ?>">
                </div>
            </div>

            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user-circle text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Role</label>
                </div>
                <select name="role" required
                        class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="veterinarian" <?php echo $user['role'] == 'veterinarian' ? 'selected' : ''; ?>>Veterinarian</option>
                    <option value="staff" <?php echo $user['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Update User
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>