<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$animal_name = isset($_GET['animal_name']) ? htmlspecialchars($_GET['animal_name']) : '';
$vaccine_name = isset($_GET['vaccine_name']) ? htmlspecialchars($_GET['vaccine_name']) : '';
$date_from = isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '';

// Modify the SQL query to include filters
$sql = "SELECT v.*, a.name AS animal_name, u.full_name AS veterinarian_name 
        FROM vaccinations v
        JOIN animals a ON v.animal_id = a.id
        JOIN users u ON v.administered_by = u.id
        WHERE 1=1";

if ($search) {
    $sql .= " AND (a.name LIKE '%$search%' OR v.vaccine_name LIKE '%$search%')";
}
if ($animal_name) {
    $sql .= " AND a.name = '$animal_name'";
}
if ($vaccine_name) {
    $sql .= " AND v.vaccine_name = '$vaccine_name'";
}
if ($date_from) {
    $sql .= " AND DATE(v.date_given) >= '$date_from'";
}
if ($date_to) {
    $sql .= " AND DATE(v.date_given) <= '$date_to'";
}

$sql .= " ORDER BY v.date_given DESC";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/vaccinations/add_vaccination.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-add-to-queue'></i>
                Add Vaccination Record
            </button>
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo $search; ?>"
                           class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Search vaccination records..."
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
                <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/vaccinations/list_vaccinations.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-reset'></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Expanded Filters (Initially Hidden) -->
        <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block font-medium">Animal Name</label>
                <select id="animalNameFilter" name="animal_name" placeholder="Search for an animal...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Vaccine Name</label>
                <select id="vaccineNameFilter" name="vaccine_name" placeholder="Search for a vaccine...">
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
                <label class="block font-medium">Date From</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Date To</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
        </div>
    </div>

    <!-- Vaccinations Table -->
    <!-- Vaccinations Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Animal Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Animal Name</th>
                    <!-- Vaccine Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Vaccine Name</th>
                    <!-- Date Given: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Date Given</th>
                    <!-- Next Due Date: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Next Due Date</th>
                    <!-- Administered By: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Administered By</th>
                    <!-- Actions: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Animal Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['animal_name']; ?></td>
                    <!-- Vaccine Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['vaccine_name']; ?></td>
                    <!-- Date Given: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['date_given'])); ?></td>
                    <!-- Next Due Date: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['next_due_date'])); ?></td>
                    <!-- Administered By: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['veterinarian_name']; ?></td>
                    <!-- Actions: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#vaccinationDetailsModal" class="viewDetails" rel="modal:open" data-vaccination-id="<?php echo $row['id']; ?>">
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

<!-- Add modal for vaccination details -->
<div id="vaccinationDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: fit-content;!important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full gap-4 pb-2 md:pb-0">
            <h3 class="hidden md:block text-xl font-medium text-slate-800 px-2 md:px-0">Vaccination Details</h3>
            <button id="edit-vaccination-record" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-edit'></i>
                Edit Vaccination Record
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

    const vaccineNameFilter = new TomSelect('#vaccineNameFilter', {
        valueField: 'vaccine_name',
        labelField: 'vaccine_name',
        searchField: 'vaccine_name',
        placeholder: 'Search for a vaccine...',
        load: function(query, callback) {
            // Fetch vaccine names from the database based on the search query
            fetch(`${BASE_URL}/api/get_vaccine_names.php?query=${encodeURIComponent(query)}`)
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
                // Custom rendering for each vaccine option
                return `<div>${escape(item.vaccine_name)}</div>`;
            },
            item: function(item, escape) {
                // Custom rendering for selected vaccine
                return `<div>${escape(item.vaccine_name)}</div>`;
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
            fetch(`${BASE_URL}/api/search_vaccinations.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                            div.textContent = `${item.animal_name} - ${item.vaccine_name}`;
                            div.addEventListener('click', () => {
                                searchInput.value = item.animal_name;
                                searchResults.classList.add('hidden');
                                const url = new URL(window.location);
                                url.searchParams.set('search', item.animal_name);
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

    // View vaccination details
    document.querySelectorAll('.viewDetails').forEach(button => {
        button.addEventListener('click', function() {
            const vaccinationId = this.getAttribute('data-vaccination-id');
            setCookie("currentVaccinationId", vaccinationId, 1);
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

            fetch(`${BASE_URL}/api/view_vaccination_details.php?id=${vaccinationId}`)
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
                                <i class='bx bx-user text-xl text-blue-600'></i> Basic Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Animal Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Species</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details?.species || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Breed</td>
                            <td class="px-4 py-3 text-gray-800">${data.animal_details?.breed || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Vaccine Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.vaccine_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Date Given</td>
                            <td class="px-4 py-3 text-gray-800">${data.date_given ? new Date(data.date_given).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Next Due Date</td>
                            <td class="px-4 py-3 text-gray-800">${data.next_due_date ? new Date(data.next_due_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Administered By</td>
                            <td class="px-4 py-3 text-gray-800">${data.veterinarian_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Batch Number</td>
                            <td class="px-4 py-3 text-gray-800">${data.batch_number || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Notes Section -->
            <div class="overflow-x-auto rounded-lg w-full ">
               <!-- Client Information -->
           
                <table class="w-full text-left max-h-auto border-2 border-gray-300 mb-2">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-user-circle text-xl text-green-600'></i> Client Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Client Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_info?.name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Contact</td>
                            <td class="px-4 py-3 text-gray-800">${data.client_info?.contact || 'N/A'}</td>
                        </tr>
                    </tbody>
                </table>
                       <table class="w-full text-left border-2 border-gray-300">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-note text-xl text-purple-600'></i> Additional Notes
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-4 py-3 text-gray-800 whitespace-pre-line">${data.notes || 'No additional notes'}</td>
                        </tr>
                    </tbody>
                </table>
            
            </div>
        </div>
    </div>
`;
                });
        });
    });

    // Handle edit vaccination button
    document.getElementById('edit-vaccination-record').addEventListener('click', function() {
        const vaccinationId = getCookie("currentVaccinationId");
        if (vaccinationId) {
            window.location.href = `${BASE_URL}/pages/vaccinations/edit_vaccination.php?id=${vaccinationId}`;
        }
    });

    // Search button click handler
    document.getElementById('searchBtn').addEventListener('click', function() {
        const searchInput = document.getElementById('search');
        const url = new URL(window.location);
        url.searchParams.set('search', searchInput.value);
        window.location = url;
    });

    // Cookie helper functions
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
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
</script>

<?php
require_once '../../includes/footer.php';
?>