<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Process form submission
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

    $sql = "INSERT INTO appointments (client_id, animal_id, veterinarian_id, appointment_date, service_type, description, 
            status, duration, fee, payment_status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissssiiss", $client_id, $animal_id, $veterinarian_id, $appointment_date, $service_type, 
                      $description, $status, $duration, $fee, $payment_status, $notes);
    
    if ($stmt->execute()) {
        echo "<div class='bg-green-50 border border-green-200 p-4 mb-6 rounded-lg shadow-sm flex items-center' role='alert'>
                <i class='bx bx-check-circle text-xl text-green-500 mr-2'></i>
                <span class='text-green-700 font-medium'>Appointment scheduled successfully!</span>
              </div>";
    }
}

// Get clients for dropdown
$clients = $conn->query("SELECT id, name FROM clients ORDER BY name");

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
                <i class='bx bx-calendar-plus text-2xl text-indigo-500 mr-3'></i>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Schedule Appointment</h1>
            </div>
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/appointments/list_appointments.php'" 
                    class="bg-indigo-500 text-white py-2 md:py-3 px-4 md:px-6 text-base font-semibold 
                           rounded-lg hover:bg-indigo-600 transition-all duration-300 
                           transform hover:scale-[1.02] flex items-center justify-center md:justify-between gap-2">
                <i class='bx bx-chevron-left-circle'></i>
                Back to Appointments
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

            <!-- Animal Selection (populated via AJAX based on client selection) -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-pet text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Animal</label>
                </div>
                <select id="animal-select" name="animal_id" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                    <option value="">Select Client First</option>
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

            <!-- Appointment Date and Duration -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-calendar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Appointment Date & Time</label>
                    </div>
                    <input type="datetime-local" name="appointment_date" required 
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-time text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Duration (minutes)</label>
                    </div>
                    <input type="number" name="duration" required min="15" step="15" value="30"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
            </div>

            <!-- Service Type and Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-category text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Service Type</label>
                    </div>
                    <select name="service_type" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="checkup">Check-up</option>
                        <option value="vaccination">Vaccination</option>
                        <option value="surgery">Surgery</option>
                        <option value="grooming">Grooming</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-tag text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Status</label>
                    </div>
                    <select name="status" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="scheduled">Scheduled</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>
            </div>

            <!-- Fee and Payment Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-dollar text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Fee</label>
                    </div>
                    <input type="number" name="fee" required min="0" step="0.01"
                           class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                </div>
                <div class="form-group">
                    <div class="flex items-center mb-2">
                        <i class='bx bx-credit-card text-lg mr-2 text-indigo-500'></i>
                        <label class="text-base font-medium text-gray-700">Payment Status</label>
                    </div>
                    <select name="payment_status" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200">
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <div class="flex items-center mb-2">
                    <i class='bx bx-detail text-lg mr-2 text-indigo-500'></i>
                    <label class="text-base font-medium text-gray-700">Description</label>
                </div>
                <textarea name="description" required class="w-full bg-gray-50 rounded-lg p-3 border border-gray-200 focus:ring-2 focus:ring-indigo-200" 
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
                <i class='bx bx-calendar-check text-lg mr-2'></i>
                Schedule Appointment
            </button>
        </form>
    </div>
</div>

<script>
    // Initialize Tom Select for dropdowns
    new TomSelect('#client-select', {
        create: false,
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        placeholder: 'Search for a client...',
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

    // Handle client selection to populate animals dropdown
    document.getElementById('client-select').addEventListener('change', function() {
        const clientId = this.value;
        if (clientId) {
            fetch(`../../api/get_client_animals.php?client_id=${clientId}`)
                .then(response => response.json())
                .then(animals => {
                    const animalSelect = document.getElementById('animal-select');
                    animalSelect.innerHTML = '<option value="">Select Animal</option>';
                    animals.forEach(animal => {
                        animalSelect.innerHTML += `<option value="${animal.id}">${animal.name} (${animal.species})</option>`;
                    });
                });
        }
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