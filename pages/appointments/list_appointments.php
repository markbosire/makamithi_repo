<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$client_name = isset($_GET['client_name']) ? htmlspecialchars($_GET['client_name']) : '';
$animal_name = isset($_GET['animal_name']) ? htmlspecialchars($_GET['animal_name']) : '';
$service_type = isset($_GET['service_type']) ? htmlspecialchars($_GET['service_type']) : '';
$date_from = isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$payment_status = isset($_GET['payment_status']) ? htmlspecialchars($_GET['payment_status']) : '';

// Modify the SQL query to include filters
$sql = "SELECT a.*, c.name AS client_name, an.name AS animal_name, u.full_name AS veterinarian_name 
        FROM appointments a
        JOIN clients c ON a.client_id = c.id
        JOIN animals an ON a.animal_id = an.id
        JOIN users u ON a.veterinarian_id = u.id
        WHERE 1=1";

if ($search) {
    $sql .= " AND (c.name LIKE '%$search%' OR an.name LIKE '%$search%' OR a.service_type LIKE '%$search%')";
}
if ($client_name) {
    $sql .= " AND c.name = '$client_name'";
}
if ($animal_name) {
    $sql .= " AND an.name = '$animal_name'";
}
if ($service_type) {
    $sql .= " AND a.service_type = '$service_type'";
}
if ($status) {
    $sql .= " AND a.status = '$status'";
}
if ($payment_status) {
    $sql .= " AND a.payment_status = '$payment_status'";
}
if ($date_from) {
    $sql .= " AND DATE(a.appointment_date) >= '$date_from'";
}
if ($date_to) {
    $sql .= " AND DATE(a.appointment_date) <= '$date_to'";
}

$sql .= " ORDER BY a.appointment_date DESC";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/appointments/schedule_appointment.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-add-to-queue'></i>
                Add Appointment
            </button>
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo $search; ?>"
                           class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Search appointment records..."
                    >
                    <button id="searchBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-1 bg-yellow-400 border-2 border-black rounded-lg hover:bg-yellow-500 transition-colors">
                        <i class='bx bx-search'></i>
                    </button>
                </div>
                <!-- Autocomplete Dropdown (Initially Hidden) -->
                <div id="searchResults" class="hidden absolute z-10 w-fit mt-1 bg-white border-2 border-black rounded-lg shadow-lg">
                    <!-- Results will be populated via JavaScript -->
                </div>
            </div>
            <!-- Filter Buttons -->
            <div class="flex gap-4">
                <button id="filterToggle" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-filter'></i>
                    Filters
                </button>
                <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/appointments/list_appointments.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-reset'></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Expanded Filters (Initially Hidden) -->
        <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block font-medium">Client Name</label>
                <select id="clientNameFilter" name="client_name" placeholder="Search for a client...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Animal Name</label>
                <select id="animalNameFilter" name="animal_name" placeholder="Search for an animal...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
             <div class="space-y-2">
                <label class="block font-medium">Veterinarian</label>
                <select id="veterinarianFilter" name="veterinarian" placeholder="Search for a veterinarian...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Service Type</label>
                <select id="serviceTypeFilter" name="service_type" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">Select Service Type</option>
                    <option value="checkup" <?php echo $service_type === 'checkup' ? 'selected' : ''; ?>>Checkup</option>
                    <option value="vaccination" <?php echo $service_type === 'vaccination' ? 'selected' : ''; ?>>Vaccination</option>
                    <option value="surgery" <?php echo $service_type === 'surgery' ? 'selected' : ''; ?>>Surgery</option>
                    <option value="grooming" <?php echo $service_type === 'grooming' ? 'selected' : ''; ?>>Grooming</option>
                    <option value="emergency" <?php echo $service_type === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="block font-medium">Status</label>
                <select id="statusFilter" name="status" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">Select Status</option>
                    <option value="scheduled" <?php echo isset($_GET['status']) && $_GET['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="confirmed" <?php echo isset($_GET['status']) && $_GET['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="no_show" <?php echo isset($_GET['status']) && $_GET['status'] === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="block font-medium">Payment Status</label>
                <select id="paymentStatusFilter" name="payment_status" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">Select Payment Status</option>
                    <option value="pending" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Date From</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Date To</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Client Name: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Client Name</th>
                    <!-- Animal Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Animal Name</th>
                    <!-- Service Type: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Service Type</th>
                    <!-- Appointment Date: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Appointment Date</th>
                    <!-- Veterinarian: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Veterinarian</th>
                    <!-- Status: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Status</th>
                    <!-- Actions: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Client Name: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['client_name']; ?></td>
                    <!-- Animal Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['animal_name']; ?></td>
                    <!-- Service Type: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['service_type']; ?></td>
                    <!-- Appointment Date: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y H:i', strtotime($row['appointment_date'])); ?></td>
                    <!-- Veterinarian: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['veterinarian_name']; ?></td>
                    <!-- Status: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo $row['status']; ?></td>
                    <!-- Actions: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#appointmentDetailsModal" class="viewDetails" rel="modal:open" data-appointment-id="<?php echo $row['id']; ?>">
                                    <i class='bx bx-show text-xl'></i>
                                </a>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Add modal for appointment details -->
<div id="appointmentDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: fit-content;!important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full gap-4 pb-2 md:pb-0">
            <h3 class="hidden md:block text-xl font-medium text-slate-800 px-2 md:px-0">Appointment Details</h3>
            <button id="edit-appointment-record" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-edit'></i>
                Edit Appointment Record
            </button>
        </div>
        <div id="modalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
    const BASE_URL = "<?php echo BASE_URL ?>";

    // Toggle filter panel
    document.getElementById('filterToggle').addEventListener('click', function() {
        const filterPanel = document.getElementById('filterPanel');
        filterPanel.classList.toggle('hidden');
    });

    // Handle filter changes
    document.querySelectorAll('select, input[type="date"]').forEach(element => {
        element.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set(this.name, this.value);
            window.location = url;
        });
    });

    const clientNameFilter = new TomSelect('#clientNameFilter', {
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        placeholder: 'Search for a client...',
        load: function(query, callback) {
            // Fetch client names from the database based on the search query
            fetch(`${BASE_URL}/api/get_client_names.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data)
                    callback(data); // Pass the data to Tom Select
                })
                .catch(() => {
                    callback(); // Handle errors
                });
        },
        render: {
            option: function(item, escape) {
                // Custom rendering for each client name option
                return `<div>${escape(item.name)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected client name
                return `<div>${escape(item.name)}</div>`;
            }
        }
    });

    const animalNameFilter = new TomSelect('#animalNameFilter', {
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        placeholder: 'Search for an animal...',
        load: function(query, callback) {
            // Fetch animal names from the database based on the search query
            fetch(`${BASE_URL}/api/get_animal_names.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data)
                    callback(data); // Pass the data to Tom Select
                })
                .catch(() => {
                    callback(); // Handle errors
                });
        },
        render: {
            option: function(item, escape) {
                // Custom rendering for each animal name option
                return `<div>${escape(item.name)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected animal name
                return `<div>${escape(item.name)}</div>`;
            }
        }
    });

 
     const veterinarianFilter = new TomSelect('#veterinarianFilter', {
        valueField: 'full_name',
        labelField: 'full_name',
        searchField: 'full_name',
        placeholder: 'Search for a veterinarian...',
        load: function(query, callback) {
            // Fetch veterinarian names from the database based on the search query
            fetch(`${BASE_URL}/api/get_veterinarian_names.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    callback(data); // Pass the data to Tom Select
                })
                .catch(() => {
                    callback(); // Handle errors
                });
        },
        render: {
            option: function(item, escape) {
                // Custom rendering for each veterinarian option
                return `<div>${escape(item.full_name)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected veterinarian
                return `<div>${escape(item.full_name)}</div>`;
            }
        }
    });


    // Search autocomplete
    let searchTimeout;
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value;

        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`${BASE_URL}/api/search_appointments.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                            div.textContent = `${item.client_name} - ${item.animal_name} - ${item.service_type}`;
                            div.addEventListener('click', () => {
                                searchInput.value = item.client_name;
                                searchResults.classList.add('hidden');
                                const url = new URL(window.location);
                                url.searchParams.set('search', item.client_name);
                                window.location = url;
                            });
                            searchResults.appendChild(div);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.classList.add('hidden');
                    }
                });
        }, 300);
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });

    // View appointment details
    document.querySelectorAll('.viewDetails').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-appointment-id');
            setCookie("currentAppointmentId", appointmentId, 1);
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

            fetch(`${BASE_URL}/api/view_appointments_details.php?id=${appointmentId}`)
                .then(response => response.json())
                .then(data => {
                   modalContent.innerHTML = `
    <div class="space-y-6 p-2 md:p-4 bg-white rounded-lg">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Basic Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-calendar text-xl text-blue-600'></i> Appointment Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Service Type</td>
                            <td class="px-4 py-3 text-gray-800">${data.service_type || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Appointment Date</td>
                            <td class="px-4 py-3 text-gray-800">${data.appointment_date ? new Date(data.appointment_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Duration</td>
                            <td class="px-4 py-3 text-gray-800">${data.duration ? data.duration + ' minutes' : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Status</td>
                            <td class="px-4 py-3 text-gray-800">${data.status || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Fee</td>
                            <td class="px-4 py-3 text-gray-800">${data.fee ? '$' + parseFloat(data.fee).toFixed(2) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Payment Status</td>
                            <td class="px-4 py-3 text-gray-800">${data.payment_status || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Client Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-user text-xl text-green-600'></i> Client Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Email</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_details.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Primary Contact</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_details.contact_primary || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Emergency Contact</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_details.contact_emergency || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Address</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_details.address || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">City</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_details.city || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Animal Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-dog text-xl text-yellow-600'></i> Animal Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Species</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details.species || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Breed</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details.breed || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Date of Birth</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details.date_of_birth ? new Date(data.animal_details.date_of_birth).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Gender</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details.gender || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Weight</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details.weight ? data.animal_details.weight + ' kg' : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Microchip</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details.microchip_number || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Veterinarian Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-plus-medical text-xl text-red-600'></i> Veterinarian Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.veterinarian_details.name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Email</td>
                            <td class="px-4 py-3 text-gray-800">${data.veterinarian_details.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Contact</td>
                            <td class="px-4 py-3 text-gray-800">${data.veterinarian_details.contact || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Medical History and Notes -->
        <div class="border-2 border-gray-300 rounded-lg overflow-hidden">
            <div class="bg-gray-100 px-4 py-3 border-b-2 border-gray-300">
                <h3 class="text-lg font-semibold text-gray-700">
                    <i class='bx bx-notepad text-xl text-purple-600'></i> Medical History and Notes
                </h3>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <h4 class="font-medium text-gray-700 mb-1">Medical History:</h4>
                    <p class="text-gray-800 whitespace-pre-line">${data.animal_details.medical_history || 'No medical history available'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-1">Allergies:</h4>
                    <p class="text-gray-800">${data.animal_details.allergies || 'No known allergies'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-1">Special Notes:</h4>
                    <p class="text-gray-800">${data.animal_details.special_notes || 'No special notes'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-1">Appointment Notes:</h4>
                    <p class="text-gray-800">${data.notes || 'No appointment notes'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-700 mb-1">Description:</h4>
                    <p class="text-gray-800">${data.description || 'No description available'}</p>
                </div>
            </div>
        </div>
    </div>`;
  

                    // Setup edit button functionality
                    document.getElementById('edit-appointment-record').onclick = function() {
                        window.location.href = `${BASE_URL}/pages/appointments/edit_appointment.php?id=${appointmentId}`;
                    };
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = '<p class="text-red-500">Error loading appointment details</p>';
                });
        });
    });

    document.getElementById('searchBtn').addEventListener('click', function() {
        const searchInput = document.getElementById('search');
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value);
        window.location = url;
    });

    // Set current appointment id in cookie
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
     const customStyles = `
    .ts-control {
          font-family: "IBM Plex Sans", serif;
          font-optical-sizing: auto;
          font-weight: 500;
          font-style: normal;
          font-variation-settings:
            "wdth" 100;
        font-size: 1rem !important;
        border: 2px solid black !important;
        border-radius: 0.5rem !important;
        padding: 0.75rem 1rem !important;
        box-shadow: none !important;
        background: white !important;
    }

    .ts-control:focus {
        outline: none !important;
        box-shadow: 0 0 0 2px rgb(250 204 21) !important;
    }

    .ts-dropdown {
        border: 2px solid black !important;
        border-radius: 0.5rem !important;
        padding: 0.25rem !important;
        margin-top: 0.25rem !important;
        box-shadow: 4px 4px 0px 0px rgba(0,0,0,1) !important;
        background: white !important;
    }

    .ts-dropdown .option {
        padding: 0.5rem 1rem !important;
        border-radius: 0.25rem !important;
    }

    .ts-dropdown .active {
        background-color: rgb(250 204 21) !important;
        color: black !important;
    }

    .ts-dropdown .option:hover {
        background-color: rgb(253 224 71) !important;
        color: black !important;
    }

    .ts-control input::placeholder {
        color: #6b7280 !important;
    }

    .ts-control .item {
        background: rgb(250 204 21) !important;
        border: 2px solid black !important;
        border-radius: 0.25rem !important;
        color: black !important;
        padding: 0.25rem 0.5rem !important;
    }

    .ts-control .clear-button {
        color: black !important;
    }

    .ts-wrapper.loading .ts-control::before {
        border-color: black !important;
    }
`;

// Create style element
const styleElement = document.createElement('style');
styleElement.textContent = customStyles;

// Insert styles after Tom Select is initialized
veterinarianFilter.wrapper.appendChild(styleElement);
document.querySelectorAll('#statusFilter, #paymentStatusFilter').forEach(element => {
    element.addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set(this.name, this.value);
        window.location = url;
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>