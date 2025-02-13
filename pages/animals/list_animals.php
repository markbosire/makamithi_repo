<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$species = isset($_GET['species']) ? htmlspecialchars($_GET['species']) : '';
$breed = isset($_GET['breed']) ? htmlspecialchars($_GET['breed']) : '';
$registration_date_from = isset($_GET['registration_date_from']) ? htmlspecialchars($_GET['registration_date_from']) : '';
$registration_date_to = isset($_GET['registration_date_to']) ? htmlspecialchars($_GET['registration_date_to']) : '';

// Modify the SQL query to include filters
$sql = "SELECT * FROM animals WHERE 1=1";

if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR microchip_number LIKE '%$search%')";
}
if ($species) {
    $sql .= " AND species = '$species'";
}
if ($breed) {
    $sql .= " AND breed = '$breed'";
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
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/animals/add_animal.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-add-to-queue'></i>
                Add Animal
            </button>
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo $search; ?>"
                           class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Search animals..."
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
                <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/animals/list_animals.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-reset'></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Expanded Filters (Initially Hidden) -->
        <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block font-medium">Species</label>
                <select id="speciesFilter" name="species" placeholder="Search for a species...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Breed</label>
                <select id="breedFilter" name="breed" placeholder="Search for a breed...">
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

   <!-- Animals Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Name</th>
                    <!-- Species: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Species</th>
                    <!-- Breed: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Breed</th>
                    <!-- Date of Birth: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Date of Birth</th>
                    <!-- Gender: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Gender</th>
                    <!-- Registration Date: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Registration Date</th>
                    <!-- Actions: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['name']; ?></td>
                    <!-- Species: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['species']; ?></td>
                    <!-- Breed: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['breed']; ?></td>
                    <!-- Date of Birth: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['date_of_birth'])); ?></td>
                    <!-- Gender: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['gender']; ?></td>
                    <!-- Registration Date: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['registration_date'])); ?></td>
                    <!-- Actions: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#animalDetailsModal" class="viewDetails" rel="modal:open" data-animal-id="<?php echo $row['id']; ?>">
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

<!-- Add modal for animal details -->
<div id="animalDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: 100vw !important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full pb-2 md:pb-0">
            <h3 class="text-xl font-medium hidden md:block text-slate-800 px-2 md:px-0">Animal Details</h3>
            <button id="edit-animal" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-edit'></i>
                Edit Animal
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

    const speciesFilter = new TomSelect('#speciesFilter', {
        valueField: 'species',
        labelField: 'species',
        searchField: 'species',
        placeholder: 'Search for a species...',
        load: function(query, callback) {
            // Fetch species from the database based on the search query
            fetch(`${BASE_URL}/api/get_species.php?query=${encodeURIComponent(query)}`)
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
                // Custom rendering for each species option
                return `<div>${escape(item.species)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected species
                return `<div>${escape(item.species)}</div>`;
            }
        }
    });

    const breedFilter = new TomSelect('#breedFilter', {
        valueField: 'breed',
        labelField: 'breed',
        searchField: 'breed',
        placeholder: 'Search for a breed...',
        load: function(query, callback) {
            // Fetch breeds from the database based on the search query
            fetch(`${BASE_URL}/api/get_breeds.php?query=${encodeURIComponent(query)}`)
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
                // Custom rendering for each breed option
                return `<div>${escape(item.breed)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected breed
                return `<div>${escape(item.breed)}</div>`;
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
            fetch(`${BASE_URL}/api/search_animals.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                            div.textContent = `${item.name} - ${item.species}`;
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

    // View animal details
    document.querySelectorAll('.viewDetails').forEach(button => {
        button.addEventListener('click', function() {
            const animalId = this.getAttribute('data-animal-id');
            setCookie("currentAnimalId",animalId,1)
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

            fetch(`${BASE_URL}/api/view_animals_details.php?id=${animalId}`)
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
                                                    <i class='bx bx-dna text-gray-500'></i>
                                                    <span>Species</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.species || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-dna text-gray-500'></i>
                                                    <span>Breed</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.breed || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-calendar text-gray-500'></i>
                                                    <span>Date of Birth</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.date_of_birth ? new Date(data.date_of_birth).toLocaleDateString() : 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-male-female text-gray-500'></i>
                                                    <span>Gender</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.gender || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-chip text-gray-500'></i>
                                                    <span>Microchip Number</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.microchip_number || 'N/A'}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Owner Information -->
                                <div class="overflow-x-auto border-2 border-black rounded-lg">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-gray-50 border-b-2 border-black">
                                                <th colspan="2" class="px-4 py-3">
                                                    <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                                        <i class='bx bx-user text-xl text-green-600'></i>
                                                        <span>Owner Information</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y-2 divide-gray-100">
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-user text-gray-500'></i>
                                                    <span>Name</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.owner_info?.name || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-phone text-gray-500'></i>
                                                    <span>Contact</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.owner_info?.contact || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-envelope text-gray-500'></i>
                                                    <span>Email</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.owner_info?.email || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-home text-gray-500'></i>
                                                    <span>Address</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.owner_info?.address || 'N/A'}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Medical Information -->
                                <div class="overflow-x-auto border-2 border-black rounded-lg">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="bg-gray-50 border-b-2 border-black">
                                                <th colspan="2" class="px-4 py-3">
                                                    <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                                        <i class='bx bx-plus-medical text-xl text-green-600'></i>
                                                        <span>Medical Information</span>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y-2 divide-gray-100">
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-notepad text-gray-500'></i>
                                                    <span>Medical History</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.medical_history || 'No medical history recorded'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-error-circle text-gray-500'></i>
                                                    <span>Allergies</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.allergies || 'No known allergies'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-detail text-gray-500'></i>
                                                    <span>Special Notes</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.special_notes || 'No special notes'}</td>
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
                                                    <i class='bx bx-calendar-check text-gray-500'></i>
                                                    <span>Total Visits</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.statistics?.total_visits || '0'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-injection text-gray-500'></i>
                                                    <span>Vaccinations</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.statistics?.total_vaccinations || '0'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-plus-medical text-gray-500'></i>
                                                    <span>Procedures</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.statistics?.total_procedures || '0'}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                              
                            </div>
                              <!-- Important Dates -->
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
                                                <td class="px-4 py-3 text-gray-800">${data.statistics?.last_visit_date ? new Date(data.statistics.last_visit_date).toLocaleDateString() : 'No visits recorded'}</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-3 font-medium text-gray-600 flex items-center gap-2">
                                                    <i class='bx bx-calendar-star text-gray-500'></i>
                                                    <span>Next Appointment</span>
                                                </td>
                                                <td class="px-4 py-3 text-gray-800">${data.statistics?.next_appointment ? new Date(data.statistics.next_appointment).toLocaleDateString() : 'None scheduled'}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <!-- Medical Records Section -->
                            ${data.medical_records && data.medical_records.length > 0 ? `
                                <div class="border-2 border-black rounded-lg">
                                    <div class="bg-gray-50 border-b-2 border-black px-4 py-3">
                                        <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                            <i class='bx bx-file text-xl text-blue-600'></i>
                                            <span>Recent Medical Records</span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead class="bg-gray-50 border-b border-gray-200">
                                                <tr>
                                                    <th class="px-4 py-2">Date</th>
                                                    <th class="px-4 py-2">Diagnosis</th>
                                                    <th class="px-4 py-2">Treatment</th>
                                                    <th class="px-4 py-2">Veterinarian</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                ${data.medical_records.map(record => `
                                                    <tr>
                                                        <td class="px-4 py-2">${new Date(record.visit_date).toLocaleDateString()}</td>
                                                        <td class="px-4 py-2">${record.diagnosis}</td>
                                                        <td class="px-4 py-2">${record.treatment}</td>
                                                        <td class="px-4 py-2">${record.veterinarian_name}</td>
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ` : ''}

                            <!-- Vaccination History Section -->
                            ${data.vaccinations && data.vaccinations.length > 0 ? `
                                <div class="border-2 border-black rounded-lg mt-6">
                                    <div class="bg-gray-50 border-b-2 border-black px-4 py-3">
                                        <div class="font-semibold text-gray-700 text-lg flex items-center gap-2">
                                            <i class='bx bx-injection text-xl text-green-600'></i>
                                            <span>Vaccination History</span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left">
                                            <thead class="bg-gray-50 border-b border-gray-200">
                                                <tr>
                                                    <th class="px-4 py-2">Vaccine</th>
                                                    <th class="px-4 py-2">Date Given</th>
                                                    <th class="px-4 py-2">Next Due Date</th>
                                                    <th class="px-4 py-2">Administered By</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                ${data.vaccinations.map(vaccination => `
                                                    <tr>
                                                        <td class="px-4 py-2">${vaccination.vaccine_name}</td>
                                                        <td class="px-4 py-2">${new Date(vaccination.date_given).toLocaleDateString()}</td>
                                                        <td class="px-4 py-2">${new Date(vaccination.next_due_date).toLocaleDateString()}</td>
                                                        <td class="px-4 py-2">${vaccination.administered_by_name}</td>
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
                    console.error("Error fetching animal details:", error);
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
                                        <p class="text-sm text-red-700">Error loading animal details: ${error.message}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
        });
    });

    // Edit animal
    document.getElementById('edit-animal').addEventListener('click', function() {
        const animalId = getCookie("currentAnimalId");
        window.location.href = `${BASE_URL}/pages/animals/edit_animal.php?id=${animalId}`;
    });

    // Helper function to get cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // Add custom styles for this page's Tom Select instances
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
    speciesFilter.wrapper.appendChild(styleElement.cloneNode(true));
    breedFilter.wrapper.appendChild(styleElement.cloneNode(true));
</script>
<?php require_once '../../includes/footer.php'; ?>