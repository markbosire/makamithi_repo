<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Retrieve the vaccination ID from the URL parameter
$vaccination_id = $_GET['id'] ?? null;

if (!$vaccination_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Vaccination ID is missing!</span>
          </div>";
    exit;
}

// Fetch the vaccination data from the database
$sql = "SELECT * FROM vaccinations WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vaccination_id);
$stmt->execute();
$result = $stmt->get_result();
$vaccination = $result->fetch_assoc();

if (!$vaccination) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Vaccination record not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the vaccination record
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $animal_id = $_POST['animal_id'];
    $vaccine_name = $_POST['vaccine_name'];
    $date_given = $_POST['date_given'];
    $next_due_date = $_POST['next_due_date'];
    $administered_by = $_POST['administered_by'];
    $batch_number = $_POST['batch_number'];
    $notes = $_POST['notes'];
    
    $sql = "UPDATE vaccinations 
            SET animal_id = ?, vaccine_name = ?, date_given = ?, 
                next_due_date = ?, administered_by = ?, batch_number = ?, notes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssissi", $animal_id, $vaccine_name, $date_given, 
                      $next_due_date, $administered_by, $batch_number, $notes, $vaccination_id);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Vaccination record updated successfully!</span>
              </div>";
    }
}

// Get animals for dropdown
$animals = $conn->query("SELECT a.id, a.name, c.name as owner_name 
                        FROM animals a 
                        JOIN clients c ON a.client_id = c.id 
                        ORDER BY a.name");

// Get staff for administered_by dropdown
$staff = $conn->query("SELECT id, full_name FROM users WHERE role IN ('veterinarian', 'staff') ORDER BY full_name");
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
        
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-injection text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Vaccination Record</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/vaccinations/list_vaccinations.php'" class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                                 rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                 transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to vaccinations
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Animal Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-dog text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Patient (Animal)</label>
                </div>
                <select id="animal-select" name="animal_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200 focus:outline-none">
                    <option value="">Select Patient</option>
                    <?php while($animal = $animals->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>" <?php echo $animal['id'] == $vaccination['animal_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['name']) . ' (Owner: ' . htmlspecialchars($animal['owner_name']) . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Vaccine Name -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-capsule text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Vaccine Name</label>
                </div>
                <input type="text" name="vaccine_name" required
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                       value="<?php echo htmlspecialchars($vaccination['vaccine_name']); ?>">
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Date Given</label>
                    </div>
                    <input type="date" name="date_given" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           value="<?php echo $vaccination['date_given']; ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar-check text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Next Due Date</label>
                    </div>
                    <input type="date" name="next_due_date" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           value="<?php echo $vaccination['next_due_date']; ?>">
                </div>
            </div>

            <!-- Administrator Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user-pin text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Administered By</label>
                </div>
                <select id="staff-select" name="administered_by" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200">
                    <option value="">Select Staff Member</option>
                    <?php while($member = $staff->fetch_assoc()): ?>
                        <option value="<?php echo $member['id']; ?>" <?php echo $member['id'] == $vaccination['administered_by'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Batch Number -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-barcode text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Batch Number</label>
                </div>
                <input type="text" name="batch_number"
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                       value="<?php echo htmlspecialchars($vaccination['batch_number']); ?>">
            </div>

            <!-- Notes -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Additional Notes</label>
                </div>
                <textarea name="notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" rows="3"><?php echo htmlspecialchars($vaccination['notes']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full bg-indigo-500 text-white py-3 px-6 text-base font-semibold 
                                     rounded-lg hover:bg-indigo-600 transition-all duration-300 
                                     transform hover:scale-[1.02] flex items-center justify-center">
                <i class='bx bx-save text-lg mr-2'></i>
                Update Vaccination Record
            </button>
        </form>
    </div>
</div>

<script>
// Initialize Tom Select for animal dropdown
new TomSelect('#animal-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for a patient...',
    plugins: ['clear_button']
});

// Initialize Tom Select for staff dropdown
new TomSelect('#staff-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for staff member...',
    plugins: ['clear_button']
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