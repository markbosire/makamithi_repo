
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
include '../db/db_connection.php';
include '../includes/header.php'; 

// Login processing
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Prepare SQL to prevent SQL injection
    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../");
                    exit();
                case 'veterinarian':
                    header("Location: ../");
                    exit();
                case 'staff':
                    header("Location: ../");
                    exit();
                default:
                    $error_message = "Invalid user role";
            }
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }
}
?>

<div class="min-h-screen flex items-center justify-center ">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden w-full max-w-4xl flex">
        <!-- Illustration Side -->
        <div class="w-1/2 bg-green-50 p-8 flex items-center justify-center relative">
            <div class="text-center z-10">
                <img src="<?php echo BASE_URL; ?>/assets/images/login.svg" alt="Login Illustration" class="mx-auto mb-6 w-64 h-64 object-contain rounded-full">
                <h2 class="text-2xl font-bold text-gray-800">Welcome Back!</h2>
                <p class="text-gray-600 mt-2">Login to manage your veterinary clinic</p>
            </div>
        </div>

        <!-- Login Form Side -->
        <div class="w-1/2 p-12 flex flex-col justify-center">
            <h1 class="text-3xl font-bold mb-6 text-gray-800">Admin Login</h1>
            
            <?php 
            // Display error message if exists
            if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class='bx bx-user text-xl text-gray-400'></i>
                        </span>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            required 
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Enter your username"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class='bx bx-lock-alt text-xl text-gray-400'></i>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            required 
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Enter your password"
                        >
                    </div>
                    <a href="#" class="text-sm text-red-500 hover:text-red-600 mt-1 block text-right">Forgot Password?</a>
                </div>

                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full bg-red-500 text-white py-2 rounded-md hover:bg-red-600 transition duration-300"
                    >
                        Log In
                    </button>
                </div>
            </form>
        </div>
    </div>


<?php
// Close the database connection
$conn->close();
?>
