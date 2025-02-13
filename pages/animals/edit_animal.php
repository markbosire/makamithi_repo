<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Retrieve the animal ID from the URL parameter
$animal_id = $_GET['id'] ?? null;

if (!$animal_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Animal ID is missing!</span>
          </div>";
    exit;
}

// Fetch the animal data from the database
$sql = "SELECT * FROM animals WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $animal_id);
$stmt->execute();
$result = $stmt->get_result();
$animal = $result->fetch_assoc();

if (!$animal) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Animal not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the animal
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $weight = $_POST['weight'];
    $microchip_number = $_POST['microchip_number'] ?? '';
    $medical_history = $_POST['medical_history'] ?? '';
    $allergies = $_POST['allergies'] ?? '';
    $special_notes = $_POST['special_notes'] ?? '';
    
    $sql = "UPDATE animals 
            SET name = ?, species = ?, breed = ?, date_of_birth = ?, gender = ?, 
                weight = ?, microchip_number = ?, medical_history = ?, allergies = ?, special_notes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $name, $species, $breed, $date_of_birth, $gender, 
                                $weight, $microchip_number, $medical_history, $allergies, $special_notes, $animal_id);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Animal updated successfully!</span>
              </div>";
    } else {
        echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
                <span class='text-red-700 font-medium'>Error updating animal: " . $stmt->error . "</span>
              </div>";
    }
}
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
      
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-paw text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Animal</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/animals/list_animals.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to animals
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-paw text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Name</label>
                    </div>
                    <input type="text" name="name" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($animal['name']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dna text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Species</label>
                    </div>
                    <input type="text" name="species" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($animal['species']); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dna text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Breed</label>
                    </div>
                    <input type="text" name="breed"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($animal['breed']); ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Date of Birth</label>
                    </div>
                    <input type="date" name="date_of_birth"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($animal['date_of_birth']); ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-male-female text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Gender</label>
                    </div>
                    <select name="gender" required
                            class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                        <option value="male" <?php echo $animal['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo $animal['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="unknown" <?php echo $animal['gender'] == 'unknown' ? 'selected' : ''; ?>>Unknown</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-weight text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Weight (kg)</label>
                    </div>
                    <input type="number" step="0.01" name="weight"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                           value="<?php echo htmlspecialchars($animal['weight']); ?>">
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-chip text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Microchip Number</label>
                </div>
                <input type="text" name="microchip_number"
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none"
                       value="<?php echo htmlspecialchars($animal['microchip_number']); ?>">
            </div>

            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-file text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Medical History</label>
                </div>
                <textarea name="medical_history" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" rows="3"><?php echo htmlspecialchars($animal['medical_history']); ?></textarea>
            </div>

            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-shield text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Allergies</label>
                </div>
                <textarea name="allergies" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" rows="3"><?php echo htmlspecialchars($animal['allergies']); ?></textarea>
            </div>

            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Special Notes</label>
                </div>
                <textarea name="special_notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:outline-none" rows="3"><?php echo htmlspecialchars($animal['special_notes']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Update Animal
            </button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>