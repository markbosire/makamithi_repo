<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Retrieve the appointment ID from the URL parameter
$appointment_id = $_GET['id'] ?? null;

if (!$appointment_id) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Appointment ID is missing!</span>
          </div>";
    exit;
}

// Fetch the appointment data from the database
$sql = "SELECT * FROM appointments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();

if (!$appointment) {
    echo "<div class='bg-red-50 border border-red-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
            <i class='bx bx-error-circle text-xl text-red-500 mr-2'></i>
            <span class='text-red-700 font-medium'>Appointment record not found!</span>
          </div>";
    exit;
}

// Process form submission for updating the appointment record
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'];
    $animal_id = $_POST['animal_id'];
    $veterinarian_id = $_POST['veterinarian_id'];
    $appointment_date = $_POST['appointment_date'];
    $service_type = $_POST['service_type'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $duration = $_POST['duration'];
    $fee = $_POST['fee'];
    $payment_status = $_POST['payment_status'];
    $notes = $_POST['notes'];
    
    $sql = "UPDATE appointments 
            SET client_id = ?, animal_id = ?, veterinarian_id = ?, 
                appointment_date = ?, service_type = ?, description = ?,
                status = ?, duration = ?, fee = ?, payment_status = ?, notes = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissssiissi", $client_id, $animal_id, $veterinarian_id, 
                      $appointment_date, $service_type, $description,
                      $status, $duration, $fee, $payment_status, $notes, $appointment_id);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Appointment updated successfully!</span>
              </div>";
    }
}

// Get clients for dropdown
$clients = $conn->query("SELECT id, name FROM clients ORDER BY name");

// Get animals for dropdown
$animals = $conn->query("SELECT a.id, a.name, c.name as owner_name 
                        FROM animals a 
                        JOIN clients c ON a.client_id = c.id 
                        ORDER BY a.name");

// Get veterinarians for dropdown
$veterinarians = $conn->query("SELECT id, full_name FROM users WHERE role = 'veterinarian' ORDER BY full_name");
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="max-w-3xl mx-auto bg-white md:border-2 md:border-black p-4 md:p-8 md:shadow-[8px_8px_0px_0px] rounded-xl">
        
        <div class="flex flex-col md:flex-row w-full justify-between border-b border-black mb-8 pb-4 gap-4">
            <div class="flex items-center">
                <i class='bx bx-calendar-edit text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Edit Appointment</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/appointments/list_appointments.php'" 
                    class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                           rounded-lg hover:bg-indigo-600 transition-all duration-300 
                           transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to appointments
            </button>
        </div>
        
        <form method="POST" class="space-y-6">
            <!-- Client Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-user text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Client</label>
                </div>
                <select id="client-select" name="client_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200">
                    <option value="">Select Client</option>
                    <?php while($client = $clients->fetch_assoc()): ?>
                        <option value="<?php echo $client['id']; ?>" <?php echo $client['id'] == $appointment['client_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($client['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Animal Selection -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-dog text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Patient (Animal)</label>
                </div>
                <select id="animal-select" name="animal_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200">
                    <option value="">Select Patient</option>
                    <?php while($animal = $animals->fetch_assoc()): ?>
                        <option value="<?php echo $animal['id']; ?>" <?php echo $animal['id'] == $appointment['animal_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($animal['name']) . ' (Owner: ' . htmlspecialchars($animal['owner_name']) . ')'; ?>
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
                <select id="vet-select" name="veterinarian_id" required class="w-full rounded-lg focus:ring-2 focus:ring-indigo-200">
                    <option value="">Select Veterinarian</option>
                    <?php while($vet = $veterinarians->fetch_assoc()): ?>
                        <option value="<?php echo $vet['id']; ?>" <?php echo $vet['id'] == $appointment['veterinarian_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vet['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Appointment Date and Time -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Appointment Date & Time</label>
                </div>
                <input type="datetime-local" name="appointment_date" required
                       class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                       value="<?php echo date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>">
            </div>

            <!-- Service Type -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-clipboard text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Service Type</label>
                </div>
                <select name="service_type" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                    <option value="checkup" <?php echo $appointment['service_type'] == 'checkup' ? 'selected' : ''; ?>>Checkup</option>
                    <option value="vaccination" <?php echo $appointment['service_type'] == 'vaccination' ? 'selected' : ''; ?>>Vaccination</option>
                    <option value="surgery" <?php echo $appointment['service_type'] == 'surgery' ? 'selected' : ''; ?>>Surgery</option>
                    <option value="grooming" <?php echo $appointment['service_type'] == 'grooming' ? 'selected' : ''; ?>>Grooming</option>
                    <option value="emergency" <?php echo $appointment['service_type'] == 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                </select>
            </div>

            <!-- Description -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-detail text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Description</label>
                </div>
                <textarea name="description" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
                          rows="3"><?php echo htmlspecialchars($appointment['description']); ?></textarea>
            </div>

            <!-- Status and Duration -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-timer text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Duration (minutes)</label>
                    </div>
                    <input type="number" name="duration" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           value="<?php echo $appointment['duration']; ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-stats text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Status</label>
                    </div>
                    <select name="status" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="scheduled" <?php echo $appointment['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="confirmed" <?php echo $appointment['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="no_show" <?php echo $appointment['status'] == 'no_show' ? 'selected' : ''; ?>>No Show</option>
                    </select>
                </div>
            </div>

            <!-- Fee and Payment Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-money text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Fee</label>
                    </div>
                    <input type="number" step="0.01" name="fee" required
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200"
                           value="<?php echo $appointment['fee']; ?>">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-credit-card text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Payment Status</label>
                    </div>
                    <select name="payment_status" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="pending" <?php echo $appointment['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $appointment['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $appointment['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-note text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Additional Notes</label>
                </div>
                <textarea name="notes" class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
          rows="4"><?php echo htmlspecialchars($appointment['notes']); ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end mt-8">
                <button type="submit" 
                        class="bg-indigo-500 text-white py-3 px-6 rounded-lg hover:bg-indigo-600 
                               transition-all duration-300 transform hover:scale-[1.02] 
                               flex items-center gap-2">
                    <i class='bx bx-save'></i>
                    Update Appointment
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Initialize TomSelect -->
<script>
// Initialize TomSelect for client dropdown
new TomSelect('#client-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for a client...',
    plugins: ['clear_button']
});

// Initialize TomSelect for animal dropdown
new TomSelect('#animal-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for a patient...',
    plugins: ['clear_button']
});

// Initialize TomSelect for veterinarian dropdown
new TomSelect('#vet-select', {
    create: false,
    sortField: {
        field: 'text',
        direction: 'asc'
    },
    placeholder: 'Search for veterinarian...',
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

<?php
require_once '../../includes/footer.php';
?>
<?php
require_once '../../includes/footer.php';
?>