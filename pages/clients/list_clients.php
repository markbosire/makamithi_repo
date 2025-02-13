<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$city = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';
$registration_date_from = isset($_GET['registration_date_from']) ? htmlspecialchars($_GET['registration_date_from']) : '';
$registration_date_to = isset($_GET['registration_date_to']) ? htmlspecialchars($_GET['registration_date_to']) : '';

// Modify the SQL query to include filters
$sql = "SELECT * FROM clients WHERE 1=1";

if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR contact_primary LIKE '%$search%')";
}
if ($city) {
    $sql .= " AND city = '$city'";
}
if ($registration_date_from) {
    $sql .= " AND DATE(registration_date) >= '$registration_date_from'";
}
if ($registration_date_to) {
    $sql .= " AND DATE(registration_date) <= '$registration_date_to'";
}

$sql .= " ORDER BY registration_date DESC";
$result = $conn->query($sql);
?>


    <div class="container mx-auto px-4 py-8 min-h-screen">
        <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
            <div class="flex flex-col lg:flex-row justify-between gap-4">
                <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/clients/add_client.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-add-to-queue'></i>
                    Add Client
                </button>
                <!-- Search Bar -->
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="<?php echo $search; ?>"
                               class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                               placeholder="Search clients..."
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
                    <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/clients/list_clients.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <i class='bx bx-reset'></i>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Expanded Filters (Initially Hidden) -->
            <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-2">
                    <label class="block font-medium">City</label>
                    <select id="cityFilter" name="city" placeholder="Search for a city...">
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="block font-medium">Registration Date From</label>
                    <input type="date" name="registration_date_from" value="<?php echo $registration_date_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                </div>
                <div class="space-y-2">
                    <label class="block font-medium">Registration Date To</label>
                    <input type="date" name="registration_date_to" value="<?php echo $registration_date_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
                </div>
            </div>
        </div>

        <!-- Clients Table -->
        <!-- Clients Table -->
<!-- Clients Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Name: Always visible -->
                    <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                    <!-- Email: Hidden on mobile and medium, visible on large -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Email</th>
                    <!-- Primary Contact: Always visible -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Primary Contact</th>
                    <!-- City: Hidden on mobile, visible on medium and larger -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">City</th>
                    <!-- Registration Date: Hidden on mobile and medium, visible on large -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Registration Date</th>
                    <!-- Actions: Always visible -->
                    <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Name: Always visible -->
                    <td class="px-6 py-4 text-sm"><?php echo $row['name']; ?></td>
                    <!-- Email: Hidden on mobile and medium, visible on large -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo $row['email']; ?></td>
                    <!-- Primary Contact: Always visible -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['contact_primary']; ?></td>
                    <!-- City: Hidden on mobile, visible on medium and larger -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['city']; ?></td>
                    <!-- Registration Date: Hidden on mobile and medium, visible on large -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['registration_date'])); ?></td>
                    <!-- Actions: Always visible -->
                    <td class="px-6 py-4 text-sm">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#clientDetailsModal" class="viewDetails" rel="modal:open" data-client-id="<?php echo $row['id']; ?>">
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

    <!-- Add modal for client details -->
    <div id="clientDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: 100vw !important;">
        <div class="modal-content">
            <div class="modal-header flex flex-row justify-between w-full pb-2 md:pb-0">
                <h3 class="text-xl hidden md:block font-medium text-slate-800 px-2 md:px-0">Client Details</h3>
                <button id="edit-client" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-edit'></i>
                    Edit Client
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
        const cityFilter = new TomSelect('#cityFilter', {
        valueField: 'city',
        labelField: 'city',
        searchField: 'city',
        placeholder: 'Search for a city...',
        load: function(query, callback) {
            // Fetch cities from the database based on the search query
            fetch(`${BASE_URL}/api/get_cities.php?query=${encodeURIComponent(query)}`)
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
                // Custom rendering for each city option
                return `<div>${escape(item.city)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected city
                return `<div>${escape(item.city)}</div>`;
            }
        }
    });

    // Handle filter changes
    document.getElementById('cityFilter').addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set('city', this.value);
        window.location = url;
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
                fetch(`${BASE_URL}/api/search_clients.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                                div.textContent = `${item.name} - ${item.email}`;
                                div.addEventListener('click', () => {
                                    searchInput.value = item.name;
                                    searchResults.classList.add('hidden');
                                    const url = new URL(window.location);
                                    url.searchParams.set('search', item.name);
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

        // View client details
        document.querySelectorAll('.viewDetails').forEach(button => {
            button.addEventListener('click', function() {
                const clientId = this.getAttribute('data-client-id');
                const modalContent = document.getElementById('modalContent');
                modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

                fetch(`${BASE_URL}/api/view_client_details.php?id=${clientId}`)
                    .then(response => response.json())
                    .then(data => {
                       modalContent.innerHTML = `
    <div class="space-y-6 p-2 md:p-4 bg-white rounded-lg">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="overflow-x-auto border-2 border-black rounded-lg">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-black">
                            <th colspan="2" class="px-4 py-3">
                                <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                    <i class='bx bx-user text-xl text-blue-600'></i>
                                    <span>Basic Information</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-id-card text-gray-500'></i>
                                <span>Name</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-envelope text-gray-500'></i>
                                <span>Email</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-phone text-gray-500'></i>
                                <span>Primary Contact</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.contact_primary || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-phone-incoming text-gray-500'></i>
                                <span>Emergency Contact</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.contact_emergency || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Address Information -->
            <div class="overflow-x-auto border-2 border-black rounded-lg">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-black">
                            <th colspan="2" class="px-4 py-3">
                                <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                    <i class='bx bx-map-alt text-xl text-green-600'></i>
                                    <span>Address Information</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-home text-gray-500'></i>
                                <span>Address</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.address || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-buildings text-gray-500'></i>
                                <span>City</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.city || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-map-pin text-gray-500'></i>
                                <span>Postal Code</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.postal_code || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Statistics -->
            <div class="overflow-x-auto border-2 border-black rounded-lg">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-black">
                            <th colspan="2" class="px-4 py-3">
                                <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                    <i class='bx bx-stats text-xl text-purple-600'></i>
                                    <span>Statistics</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bxs-dog text-gray-500'></i>
                                <span>Total Pets</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.statistics?.total_pets || '0'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-cart text-gray-500'></i>
                                <span>Total Purchases</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.statistics?.total_purchases || '0'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-calendar-check text-gray-500'></i>
                                <span>Total Appointments</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.statistics?.total_appointments || '0'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-dollar-circle text-gray-500'></i>
                                <span>Lifetime Value</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">$${data.statistics?.lifetime_value || '0.00'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Dates -->
            <div class="overflow-x-auto border-2 border-black rounded-lg">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-black">
                            <th colspan="2" class="px-4 py-3">
                                <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                    <i class='bx bx-calendar text-xl text-orange-600'></i>
                                    <span>Important Dates</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-calendar-plus text-gray-500'></i>
                                <span>Registration Date</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.registration_date ? new Date(data.registration_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-calendar-check text-gray-500'></i>
                                <span>Last Visit</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.last_visit_date ? new Date(data.last_visit_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                <i class='bx bx-calendar-star text-gray-500'></i>
                                <span>Next Appointment</span>
                            </td>
                            <td class="px-4 py-3 text-gray-800">${data.statistics?.next_appointment ? new Date(data.statistics.next_appointment).toLocaleDateString() : 'None Scheduled'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="border-2 border-black rounded-lg">
            <div class="bg-gray-50 border-b-2 border-black px-4 py-3">
                <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                    <i class='bx bx-note text-xl text-red-600'></i>
                    <span>Notes</span>
                </div>
            </div>
            <div class="p-4 text-gray-800 whitespace-pre-wrap">${data.notes || 'No notes available'}</div>
        </div>

        <!-- Recent Pets Section -->
        ${data.recent_pets && data.recent_pets.length > 0 ? `
            <div class="border-2 border-black rounded-lg">
                <div class="bg-gray-50 border-b-2 border-black px-4 py-3">
                    <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                        <i class='bx bxs-dog text-xl text-yellow-600'></i>
                        <span>Recent Pets</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Species</th>
                                <th class="px-4 py-2">Breed</th>
                                <th class="px-4 py-2">Birth Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.recent_pets.map(pet => `
                                <tr>
                                    <td class="px-4 py-2">${pet.name}</td>
                                    <td class="px-4 py-2">${pet.species}</td>
                                    <td class="px-4 py-2">${pet.breed}</td>
                                    <td class="px-4 py-2">${new Date(pet.date_of_birth).toLocaleDateString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        ` : ''}

        <!-- Recent Appointments Section -->
        ${data.recent_appointments && data.recent_appointments.length > 0 ? `
            <div class="border-2 border-black rounded-lg">
                <div class="bg-gray-50 border-b-2 border-black px-4 py-3">
                    <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                        <i class='bx bx-calendar text-xl text-blue-600'></i>
                        <span>Recent Appointments</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Service</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.recent_appointments.map(appointment => `
                                <tr>
                                    <td class="px-4 py-2">${new Date(appointment.appointment_date).toLocaleString()}</td>
                                    <td class="px-4 py-2">${appointment.service_type}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            ${appointment.status === 'completed' ? 'bg-green-100 text-green-800' :
                                              appointment.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                              appointment.status === 'scheduled' ? 'bg-blue-100 text-blue-800' :
                                              appointment.status === 'no_show' ? 'bg-gray-100 text-gray-800' :
                                              'bg-yellow-100 text-yellow-800'}">
                                            ${appointment.status}
                                        </span>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        ` : ''}

        <!-- Recent Purchases Section -->
        ${data.recent_purchases && data.recent_purchases.length > 0 ? `
            <div class="border-2 border-black rounded-lg">
                <div class="bg-gray-50 border-b-2 border-black px-4 py-3">
                    <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                        <i class='bx bx-shopping-bag text-xl text-green-600'></i>
                        <span>Recent Purchases</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Product</th>
                                <th class="px-4 py-2">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${data.recent_purchases.map(purchase => `
                                <tr>
                                    <td class="px-4 py-2">${new Date(purchase.sale_date).toLocaleDateString()}</td>
                                    <td class="px-4 py-2">${purchase.product_name}</td>
                                    <td class="px-4 py-2">$${parseFloat(purchase.total).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        ` : ''}
        
       
    </div>
`;
                    })
                    .catch(error => {
                        console.error("Error fetching client details:", error);
                        modalContent.innerHTML = `
                            <div class="p-4">
                                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-700">Error loading client details: ${error.message}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
            });
        });

        // Edit client
        document.getElementById('edit-client').addEventListener('click', function() {
            const clientId = getCookie("currentClientId");
            window.location.href = `${BASE_URL}/pages/clients/edit_client.php?id=${clientId}`;
        });

        // Helper function to get cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        // Add custom styles for this page's Tom Select instance
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
cityFilter.wrapper.appendChild(styleElement);
    </script>
<?php require_once '../../includes/footer.php'; ?>