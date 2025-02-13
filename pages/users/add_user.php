<?php
require_once '../../auth/middleware.php';
checkAdmin(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO users (username, password, full_name, role, email, contact) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $password, $full_name, $role, $email, $contact);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>User added successfully!</span>
              </div>";
    }
}
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-plus text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Add New User</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/users/list_users.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to Dashboard
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Username -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Username</label>
                </div>
                <input type="text" name="username" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-lock text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Password</label>
                </div>
                <input type="password" name="password" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Full Name -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user-pin text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Full Name</label>
                </div>
                <input type="text" name="full_name" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Role -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-briefcase text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Role</label>
                </div>
                <select name="role" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                    <option value="admin">Admin</option>
                    <option value="veterinarian">Veterinarian</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <!-- Email -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-envelope text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Email</label>
                </div>
                <input type="email" name="email" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Contact -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-phone text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Contact</label>
                </div>
                <input type="text" name="contact" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Add User
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>