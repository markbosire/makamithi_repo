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
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
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
<nav class="bg-gray-50 p-2 ibm-plex-sans-regular">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-3">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.svg" alt="Logo" class="h-10">
            <span class="text-xl font-bold">Makamithi Vetcare</span>
        </a>
        <a href="<?php echo BASE_URL; ?>" class="text-black hover:bg-gray-100 px-4 py-2 border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)]">
            Home
        </a>
    </div>
</nav>
<div class="h-screen flex items-center justify-center bg-gray-50 ibm-plex-sans-regular">
    <div class="w-full max-w-4xl flex flex-col md:flex-row bg-white md:border-2 md:border-black md:shadow-[4px_4px_0px_0px_rgba(0,0,0,0.8)]">
        <!-- Illustration Side - Hidden on mobile -->
        <div class="hidden md:block w-full md:w-1/2 bg-green-50 p-8 md:border-r-2 border-black">
            <div class="text-center">
                <img src="<?php echo BASE_URL; ?>/assets/images/login.svg" alt="Login Illustration" class="mx-auto mb-6 w-64 h-64 object-contain border-2 border-black bg-white">
                <h2 class="text-2xl font-bold text-black">Welcome Back</h2>
                <p class="text-gray-600 mt-2">Login to manage your veterinary clinic</p>
            </div>
        </div>

        <!-- Login Form Side -->
        <div class="w-full md:w-1/2 p-6 md:p-12 bg-white">
            <h1 class="text-3xl font-bold mb-8 text-black">Login</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 border-2 border-red-500 p-4 mb-6" role="alert">
                    <span class="block text-red-500"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                <div>
                    <label class="block text-lg font-medium text-black mb-2">Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        required 
                        class="w-full p-3 border-2 border-black text-base focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
                        placeholder="Enter your username"
                    >
                </div>

                <div>
                    <label class="block text-lg font-medium text-black mb-2">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        class="w-full p-3 border-2 border-black text-base focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
                        placeholder="Enter your password"
                    >
                    <a href="#" class="text-base text-blue-600 hover:underline mt-2 inline-block">Forgot Password?</a>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-black text-white text-lg font-medium py-3 border-2 border-black hover:bg-gray-800 transition-colors duration-300 shadow-[2px_2px_0px_0px_rgba(0,0,0,0.8)]"
                >
                    Log In
                </button>
            </form>
        </div>
    </div>
</div>

<?php $conn->close(); ?>