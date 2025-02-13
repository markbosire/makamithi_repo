/**
 * Add New Animal
 * 
 * This script handles the addition of new animal records to the database.
 * It includes form processing for animal details and client association.
 * 
 * @package VetClinicSystem
 * @subpackage Animals
 * @author Makamithi Devs
 * @version 1.0
 * @license [License Information]
 * 
 * Features:
 * - Secure authentication check via middleware
 * - Form submission handling for animal data
 * - Client selection from database
 * - Responsive form layout with field validation
 * - Success notification for successful submissions
 * - Enhanced UI with TomSelect for client dropdown
 * 
 * Database Operations:
 * - Inserts new animal records with prepared statements
 * - Retrieves client list for dropdown selection
 * 
 * Form Fields:
 * - Client selection (required)
 * - Animal name (required)
 * - Species (required)
 * - Breed (required)
 * - Date of birth (required)
 * - Gender (required)
 * - Weight (required)
 * - Microchip number (optional)
 * - Medical history (optional)
 * - Allergies (optional)
 * - Special notes (optional)
 * 
 * Dependencies:
 * - auth/middleware.php - For authentication
 * - includes/header.php - For page header
 * - includes/nav.php - For navigation
 * - includes/footer.php - For page footer
 * - db/db_connection.php - For database connection
 * - TomSelect JS library - For enhanced dropdown functionality
 * 
 * @uses checkAuth() For verifying user authentication
 * @uses db_connection.php For database operations
 */
<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $weight = $_POST['weight'];
    $microchip_number = $_POST['microchip_number'];
    $medical_history = $_POST['medical_history'];
    $allergies = $_POST['allergies'];
    $special_notes = $_POST['special_notes'];

    $sql = "INSERT INTO animals (client_id, name, species, breed, date_of_birth, gender, weight, microchip_number, medical_history, allergies, special_notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssdssss", $client_id, $name, $species, $breed, $date_of_birth, $gender, $weight, $microchip_number, $medical_history, $allergies, $special_notes);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Animal added successfully!</span>
              </div>";
    }
}

// Get clients for dropdown
$clients = $conn->query("SELECT id, name FROM clients ORDER BY name");
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-plus text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Add New Animal</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/animals/list_animals.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to Dashboard
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
                        <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Animal Name -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-pencil text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Animal Name</label>
                </div>
                <input type="text" name="name" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
            </div>

            <!-- Species and Breed -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dna text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Species</label>
                    </div>
                    <input type="text" name="species" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dna text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Breed</label>
                    </div>
                    <input type="text" name="breed" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>

            <!-- Date of Birth and Gender -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Date of Birth</label>
                    </div>
                    <input type="date" name="date_of_birth" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-male-female text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Gender</label>
                    </div>
                    <select name="gender" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="unknown">Unknown</option>
                    </select>
                </div>
            </div>

            <!-- Weight and Microchip Number -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-weight text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Weight (kg)</label>
                    </div>
                    <input type="number" name="weight" step="0.01" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-chip text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Microchip Number</label>
                    </div>
                    <input type="text" name="microchip_number" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>

            <!-- Medical History and Allergies -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-clipboard text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Medical History</label>
                    </div>
                    <textarea name="medical_history" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-clipboard text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Allergies</label>
                    </div>
                    <textarea name="allergies" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" rows="3"></textarea>
                </div>
            </div>

            <!-- Special Notes -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Special Notes</label>
                </div>
                <textarea name="special_notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" rows="3"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Add Animal
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

    // Add custom styles to match your design
    document.addEventListener('DOMContentLoaded', function() {
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
</script>

<?php require_once '../../includes/footer.php'; ?>