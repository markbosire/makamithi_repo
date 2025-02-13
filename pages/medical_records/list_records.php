<?php
require_once '../../auth/middleware.php';
checkAuth(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';

// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$animal_name = isset($_GET['animal_name']) ? htmlspecialchars($_GET['animal_name']) : '';
$veterinarian = isset($_GET['veterinarian']) ? htmlspecialchars($_GET['veterinarian']) : '';
$visit_date_from = isset($_GET['visit_date_from']) ? htmlspecialchars($_GET['visit_date_from']) : '';
$visit_date_to = isset($_GET['visit_date_to']) ? htmlspecialchars($_GET['visit_date_to']) : '';

// Modify the SQL query to include filters
$sql = "SELECT mr.*, a.name AS animal_name, u.full_name AS veterinarian_name 
        FROM medical_records mr
        JOIN animals a ON mr.animal_id = a.id
        JOIN users u ON mr.veterinarian_id = u.id
        WHERE 1=1";

if ($search) {
    $sql .= " AND (a.name LIKE '%$search%' OR mr.diagnosis LIKE '%$search%')";
}
if ($animal_name) {
    $sql .= " AND a.name = '$animal_name'";
}
if ($veterinarian) {
    $sql .= " AND u.full_name = '$veterinarian'";
}
if ($visit_date_from) {
    $sql .= " AND DATE(mr.visit_date) >= '$visit_date_from'";
}
if ($visit_date_to) {
    $sql .= " AND DATE(mr.visit_date) <= '$visit_date_to'";
}

$sql .= " ORDER BY mr.visit_date DESC";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/medical_records/add_record.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-add-to-queue'></i>
                Add Medical Record
            </button>
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo $search; ?>"
                           class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Search medical records..."
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
                <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/medical_records/list_records.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
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
                <label class="block font-medium">Veterinarian</label>
                <select id="veterinarianFilter" name="veterinarian" placeholder="Search for a veterinarian...">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Visit Date From</label>
                <input type="date" name="visit_date_from" value="<?php echo $visit_date_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Visit Date To</label>
                <input type="date" name="visit_date_to" value="<?php echo $visit_date_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
        </div>
    </div>

   <!-- Medical Records Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Animal Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Animal Name</th>
                    <!-- Veterinarian: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Veterinarian</th>
                    <!-- Visit Date: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Visit Date</th>
                    <!-- Diagnosis: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Diagnosis</th>
                    <!-- Treatment: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Treatment</th>
                    <!-- Actions: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Animal Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['animal_name']; ?></td>
                    <!-- Veterinarian: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['veterinarian_name']; ?></td>
                    <!-- Visit Date: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['visit_date'])); ?></td>
                    <!-- Diagnosis: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['diagnosis']; ?></td>
                    <!-- Treatment: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo $row['treatment']; ?></td>
                    <!-- Actions: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#medicalRecordDetailsModal" class="viewDetails" rel="modal:open" data-record-id="<?php echo $row['id']; ?>">
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

<!-- Add modal for medical record details -->
<div id="medicalRecordDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: fit-content;!important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full gap-4  pb-2 md:pb-0">
            <h3 class="hidden md:block text-xl font-medium text-slate-800 px-2 md:px-0">Medical Record Details</h3>
            <button id="edit-medical-record" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-edit'></i>
                Edit Medical Record
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
            fetch(`${BASE_URL}/api/search_medical_records.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                            div.textContent = `${item.animal_name} - ${item.diagnosis}`;
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

    // View medical record details
    document.querySelectorAll('.viewDetails').forEach(button => {
        button.addEventListener('click', function() {
            const recordId = this.getAttribute('data-record-id');
            setCookie("currentMedicalRecordId", recordId, 1);
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

            fetch(`${BASE_URL}/api/view_medical_records_details.php?id=${recordId}`)
                .then(response => response.json())
                .then(data => {
                  modalContent.innerHTML = `
    <div class="space-y-6 p-2 md:p-4 bg-white rounded-lg">
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
            <!-- Client Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg h-fit w-full">
                <table class="w-full text-left max-h-auto">
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
            </div>
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
                            <td class="px-4 py-3 font-medium text-gray-600">Veterinarian</td>
                            <td class="px-4 py-3 text-gray-800">${data.veterinarian_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Visit Date</td>
                            <td class="px-4 py-3 text-gray-800">${data.visit_date ? new Date(data.visit_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Diagnosis</td>
                            <td class="px-4 py-3 text-gray-800">${data.diagnosis || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Treatment</td>
                            <td class="px-4 py-3 text-gray-800">${data.treatment || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Prescription</td>
                            <td class="px-4 py-3 text-gray-800">${data.prescription || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Lab Results</td>
                            <td class="px-4 py-3 text-gray-800">${data.lab_results || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Next Visit Date</td>
                            <td class="px-4 py-3 text-gray-800">${data.next_visit_date ? new Date(data.next_visit_date).toLocaleDateString() : 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Notes</td>
                            <td class="px-4 py-3 text-gray-800">${data.notes || 'N/A'}</td>
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

    // Edit medical record
    document.getElementById('edit-medical-record').addEventListener('click', function() {
        const recordId = getCookie("currentMedicalRecordId");
        if (recordId) {
            window.location.href = `${BASE_URL}/pages/medical_records/edit_record.php?id=${recordId}`;
        }
    });

    // Cookie functions
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
        for(let i = 0; i < ca.length; i++) {
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

<?php require_once '../../includes/footer.php'; ?>