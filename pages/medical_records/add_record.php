<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $animal_id = $_POST['animal_id'];
    $veterinarian_id = $_POST['veterinarian_id'];
    $visit_date = $_POST['visit_date'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $prescription = $_POST['prescription'];
    $lab_results = $_POST['lab_results'];
    $next_visit_date = $_POST['next_visit_date'];
    $notes = $_POST['notes'];

    $sql = "INSERT INTO medical_records (animal_id, veterinarian_id, visit_date, diagnosis, treatment, prescription, lab_results, next_visit_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssss", $animal_id, $veterinarian_id, $visit_date, $diagnosis, $treatment, $prescription, $lab_results, $next_visit_date, $notes);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Medical record added successfully!</span>
              </div>";
    }
}

// Get animals for dropdown
$animals = $conn->query("SELECT a.id, a.name, c.name as client_name 
                        FROM animals a 
                        JOIN clients c ON a.client_id = c.id 
                        ORDER BY c.name, a.name");

// Get veterinarians for dropdown
$veterinarians = $conn->query("SELECT id, full_name 
                              FROM users 
                              WHERE role = 'veterinarian' 
                              ORDER BY full_name");
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
        
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-plus-medical text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Add Medical Record</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/medical_records/list_records.php'" 
                    class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                           rounded-lg hover:bg-indigo-600 transition-all duration-300 
                           transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to Records
            </button>
        </div>

        <form method="POST" class="space-y-6">
            <!-- Animal Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-pet text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Animal</label>
                </div>
                <select id="animal-select" name="animal_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    <option value="">Select Animal</option>
                    <?php while($animal = $animals->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>">
                            <?php echo htmlspecialchars($animal['name'] . ' (' . $animal['client_name'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Veterinarian Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user-pin text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Veterinarian</label>
                </div>
                <select id="vet-select" name="veterinarian_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    <option value="">Select Veterinarian</option>
                    <?php while($vet = $veterinarians->fetch_assoc()): ?>
                        <option value="<?php echo $vet['id']; ?>"><?php echo htmlspecialchars($vet['full_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Visit Date and Next Visit Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Visit Date</label>
                    </div>
                    <input type="datetime-local" name="visit_date" required 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar-check text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Next Visit Date</label>
                    </div>
                    <input type="date" name="next_visit_date" 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>

            <!-- Diagnosis -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-search-alt text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Diagnosis</label>
                </div>
                <textarea name="diagnosis" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
                          rows="3"></textarea>
            </div>

            <!-- Treatment -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-first-aid text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Treatment</label>
                </div>
                <textarea name="treatment" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
                          rows="3"></textarea>
            </div>

            <!-- Prescription -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-capsule text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Prescription</label>
                </div>
                <textarea name="prescription" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
                          rows="3"></textarea>
            </div>

            <!-- Lab Results -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-test-tube text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Lab Results</label>
                </div>
                <textarea name="lab_results" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
                          rows="3"></textarea>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Additional Notes</label>
                </div>
                <textarea name="notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
                          rows="3"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Save Medical Record
            </button>
        </form>
    </div>
</div>

<script>
    // Initialize Tom Select for animal and vet dropdowns
    new TomSelect('#animal-select', {
        create: false,
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        placeholder: 'Search for the animal...',
        plugins: ['clear_button'],
    });

    new TomSelect('#vet-select', {
        create: false,
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        placeholder: 'Search for a veterinarian...',
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