<?php
session_start();


// Function to generate a random background color
function generateRandomColor() {
    $colors = [
        'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 
        'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
    ];
    return $colors[array_rand($colors)];
}


if (isset($_SESSION['user_id'])) {

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
  
}
?>

<nav class="bg-white py-4 ibm-plex-mono-medium relative">
  <div class="container mx-auto flex justify-between items-center px-6">
    <!-- Logo (left-aligned) -->
    <div class="flex items-center">
      <img src="<?php echo BASE_URL; ?>/assets/images/logo.svg" alt="Logo" class="h-10">
    </div>
    
    <!-- Hamburger Menu (Mobile) -->
    <div class="md:hidden">
      <button id="mobile-menu-toggle" class="text-gray-800 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
    
    <?php if(isset($_SESSION['user_id'])): ?>
    <!-- Navigation Links (Desktop) -->
        <ul class="hidden md:flex space-x-8 items-center">
      <!-- Home -->
      <li>
        <a href="<?php echo BASE_URL; ?>/index.php" class="text-gray-800 hover:text-gray-600 font-Quicksand md:text-base lg:text-xl transition-all duration-300">
          Home
        </a>
      </li>
      
      <!-- Patients Group -->
      <li class="relative group">
        <button class="text-gray-800 hover:text-gray-600 font-Quicksand md:text-base lg:text-xl transition-all duration-300 flex items-center">
          Patients
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 invisible group-hover:visible transition-all duration-300 opacity-0 group-hover:opacity-100 z-50">
          <div class="py-1">
            <a href="<?php echo BASE_URL; ?>/pages/clients/list_clients.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Clients</a>
            <a href="<?php echo BASE_URL; ?>/pages/animals/list_animals.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Animals</a>
          </div>
        </div>
      </li>
      
      <!-- Medical Group -->
      <li class="relative group">
        <button class="text-gray-800 hover:text-gray-600 font-Quicksand md:text-base lg:text-xl transition-all duration-300 flex items-center">
          Medical
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 invisible group-hover:visible transition-all duration-300 opacity-0 group-hover:opacity-100 z-50">
          <div class="py-1">
            <a href="<?php echo BASE_URL; ?>/pages/medical_records/list_records.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Medical Records</a>
            <a href="<?php echo BASE_URL; ?>/pages/vaccinations/list_vaccinations.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Vaccinations</a>
          </div>
        </div>
      </li>
      
      <!-- Management Group -->
     <!-- Management Group -->
      <li class="relative group">
        <button class="text-gray-800 hover:text-gray-600 font-Quicksand md:text-base lg:text-xl transition-all duration-300 flex items-center">
          Management
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 invisible group-hover:visible transition-all duration-300 opacity-0 group-hover:opacity-100 z-50">
          <div class="py-1">
            <a href="<?php echo BASE_URL; ?>/pages/appointments/list_appointments.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Appointments</a>
            <a href="<?php echo BASE_URL; ?>/pages/inventory/list_inventory.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Inventory</a>
            <a href="<?php echo BASE_URL; ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Sales</a>
            
            <!-- Staff Option (Visible only to Admin) -->
            <?php if ($user["role"] === 'admin'): ?>
              <a href="<?php echo BASE_URL; ?>/pages/users/list_users.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Staff</a>
            <?php endif; ?>
          </div>
        </div>
      </li>
    </ul>
    <?php endif; ?>
    
    <!-- Mobile Menu Dropdown -->
    <?php if(isset($_SESSION['user_id'])): ?>
   <div id="mobile-menu" class="hidden md:hidden fixed inset-0 z-50">
  <!-- Backdrop -->
  <div id="mobile-menu-backdrop" class="fixed inset-0 bg-black bg-opacity-50"></div>
  
  <!-- Menu Content -->
  <div class="fixed inset-y-0 right-0 w-64 bg-white shadow-xl">
    <div class="flex flex-col h-full">
      <!-- Back Button -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <button id="mobile-menu-back" class="flex items-center text-gray-800">
          <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back
        </button>
      </div>

      <!-- User Info Section -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-full <?php echo generateRandomColor(); ?> flex items-center justify-center text-white font-bold">
            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
          </div>
          <span class="font-Quicksand text-lg">
            <?php echo htmlspecialchars($_SESSION['username']); ?>
          </span>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-2 overflow-y-auto">
        <!-- Home -->
        <a href="<?php echo BASE_URL; ?>/index.php" class="block py-2 text-gray-800 hover:text-gray-600 font-Quicksand text-lg">
          Home
        </a>

        <!-- Patients Section -->
        <div class="py-2">
          <button class="flex items-center justify-between w-full text-gray-800 hover:text-gray-600 font-Quicksand text-lg mobile-dropdown-toggle">
            Patients
            <svg class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="hidden pl-4 space-y-2 mt-2">
            <a href="<?php echo BASE_URL; ?>/pages/clients/list_clients.php" class="block py-1 text-gray-700 hover:text-gray-900">
              Clients
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/animals/list_animals.php" class="block py-1 text-gray-700 hover:text-gray-900">
              Animals
            </a>
          </div>
        </div>

        <!-- Medical Section -->
        <div class="py-2">
          <button class="flex items-center justify-between w-full text-gray-800 hover:text-gray-600 font-Quicksand text-lg mobile-dropdown-toggle">
            Medical
            <svg class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="hidden pl-4 space-y-2 mt-2">
            <a href="<?php echo BASE_URL; ?>/pages/medical_records/list_records.php" class="block py-1 text-gray-700 hover:text-gray-900">
              Medical Records
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/vaccinations/list_vaccinations.php" class="block py-1 text-gray-700 hover:text-gray-900">
              Vaccinations
            </a>
          </div>
        </div>

        <!-- Management Section -->
        <div class="py-2">
          <button class="flex items-center justify-between w-full text-gray-800 hover:text-gray-600 font-Quicksand text-lg mobile-dropdown-toggle">
            Management
            <svg class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="hidden pl-4 space-y-2 mt-2">
            <a href="<?php echo BASE_URL; ?>/pages/appointments/list_appointments.php" class="block py-1 text-gray-700 hover:text-gray-900">
              Appointments
            </a>
            <a href="<?php echo BASE_URL; ?>/pages/inventory/list_inventory.php" class="block py-1 text-gray-700 hover:text-gray-900">
              Inventory
            </a>
            <a href="<?php echo BASE_URL; ?>" class="block py-1 text-gray-700 hover:text-gray-900">
              Sales
            </a>
              <?php if ($user["role"] === 'admin'): ?>
              <a href="<?php echo BASE_URL; ?>/pages/users/list_users.php" class="block py-1 text-gray-700 hover:bg-gray-100">Staff</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>

      <!-- Footer Actions -->
      <div class="border-t border-gray-200 p-4 space-y-2">
        <a href="<?php echo BASE_URL; ?>/pages/users/edit_profile.php" class="block py-2 text-gray-700 hover:text-gray-900 font-Quicksand">
          Profile
        </a>
        <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="block py-2 text-gray-700 hover:text-gray-900 font-Quicksand">
          Logout
        </a>
      </div>
    </div>
  </div>
</div>
    <!-- Previous mobile menu code remains the same -->
    <?php endif; ?>
    
    <!-- User/Login Section (Mobile/Desktop) -->
    <?php if(isset($_SESSION['user_id'])): ?>
      <div class="hidden md:block relative group">
      <div class="flex items-center bg-white pl-1 pr-2 py-1 cursor-pointer">
  <div class="mr-2 w-10 h-10 rounded-full <?php echo generateRandomColor(); ?> flex items-center justify-center text-white font-bold">
    <?php 
    // Get user details from database using session ID
   
    
    // Get first letter of full name for the avatar
    $initial = strtoupper(substr($user['full_name'], 0, 1)); 
    echo $initial; 
    ?>
  </div>
  <div class="flex flex-col">
    <span class="text-gray-800 font-Quicksand md:text-base lg:text-xl md:hidden lg:block">
      <?php echo htmlspecialchars($user['full_name']); ?>
    </span>
    <span class="text-gray-600 text-sm capitalize hidden lg:block">
      <?php echo htmlspecialchars($user['role']); ?>
    </span>
  </div>
</div>
        
        <!-- User Dropdown Menu -->
        <div class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
          <div class="py-1">
            <a href="<?php echo BASE_URL; ?>/pages/users/edit_profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              Profile
            </a>
            <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              Logout
            </a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <!-- Login Button (Mobile/Desktop) -->
      <a href="<?php echo BASE_URL; ?>/auth/login.php" class="h-12 border-black border-2 p-2.5 bg-[#A6FAFF] hover:bg-[#79F7FF] hover:shadow-[2px_2px_0px_rgba(0,0,0,1)] active:bg-[#00E1EF] rounded-md">
        Login
      </a>
    <?php endif; ?>
  </div>
</nav>

<style>
    @media (max-width: 767px) {
  .modal{
    min-width: 100vw !important;
    width: 100%;
    padding: 5px;
      padding-top: 20px;
  }
  .blocker{
    padding: 0;
  }
.modal a.close-modal {
  top: 1.4rem !important;
  right: 1rem !important;
  padding: 9px 25px; /* Slightly larger padding for a more impactful button */

  color: #000; /* Keep the text color black for contrast */
  font-family: "IBM Plex Sans", sans-serif; /* Use sans-serif for a more modern, utilitarian feel */
  font-optical-sizing: auto;
  font-weight: 400; 
  font-style: normal;
  font-variation-settings: "wdth" 100;
  line-height: 1.25;
  text-align: center;
  text-decoration: none;
  text-indent: 0;

  background: #ff4444; /* Subtle red background */
  border: 2px solid #000; /* Bold black border */
  width: auto;
  height: auto;
  border-radius: 4px; /* Slight rounding for a softer neo-brutalist look */
  box-shadow: 2px 2px 0px #000; /* Add a shadow for depth and a neo-brutalist edge */
  transition: all 0.2s ease; /* Smooth transition for hover effects */

  /* Hover state for interactivity */
  &:hover {
    background: #cc0000; /* Darker red on hover */
    box-shadow: 1px 1px 0px #000; /* Reduce shadow on hover for a "pressed" effect */
    transform: translate(2px, 2px); /* Move the button slightly to simulate pressing */
  }

  /* Active state for when the button is clicked */
  &:active {
    background: #aa0000; /* Even darker red for active state */
    box-shadow: 0px 0px 0px #000; /* Remove shadow to simulate being fully pressed */
    transform: translate(4px, 4px); /* Move the button further */
  }
}
}
</style>