<?php
require_once '../../auth/middleware.php';
checkAdmin(); 
require_once '../../includes/header.php';
require_once '../../includes/nav.php';
require_once '../../db/db_connection.php';
// Initialize search parameters from URL
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$role = isset($_GET['role']) ? htmlspecialchars($_GET['role']) : '';
$created_at_from = isset($_GET['created_at_from']) ? htmlspecialchars($_GET['created_at_from']) : '';
$created_at_to = isset($_GET['created_at_to']) ? htmlspecialchars($_GET['created_at_to']) : '';

// Modify the SQL query to include filters
$sql = "SELECT * FROM users WHERE 1=1";

if ($search) {
    $sql .= " AND (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($role) {
    $sql .= " AND role = '$role'";
}
if ($created_at_from) {
    $sql .= " AND DATE(created_at) >= '$created_at_from'";
}
if ($created_at_to) {
    $sql .= " AND DATE(created_at) <= '$created_at_to'";
}

$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8 min-h-screen">
    <div class="bg-white border-2 border-black rounded-lg p-6 mb-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
        <div class="flex flex-col lg:flex-row justify-between gap-4">
            <button onclick="window.location.href='<?php echo BASE_URL; ?>/pages/users/add_user.php'" class="px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-add-to-queue'></i>
                Add Staff
            </button>
            <!-- Search Bar -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="<?php echo $search; ?>"
                           class="w-full px-4 py-2 border-2 border-black rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Search staff..."
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
                <button onclick="window.location.href='<?php echo BASE_URL;?>/pages/users/list_users.php'" class="px-4 py-2 bg-gray-200 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-gray-300 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                    <i class='bx bx-reset'></i>
                    Reset
                </button>
            </div>
        </div>

        <!-- Expanded Filters (Initially Hidden) -->
        <div id="filterPanel" class="hidden mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
                <label class="block font-medium">Role</label>
                <select id="roleFilter" name="role" placeholder="Select role..." class="w-full px-3 py-2 border-2 border-black rounded-lg">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="veterinarian">Veterinarian</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Created At From</label>
                <input type="date" name="created_at_from" value="<?php echo $created_at_from; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
            <div class="space-y-2">
                <label class="block font-medium">Created At To</label>
                <input type="date" name="created_at_to" value="<?php echo $created_at_to; ?>" class="w-full px-3 py-2 border-2 border-black rounded-lg">
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <!-- Users Table -->
<div class="bg-white border-2 border-black rounded-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-black">
                <tr>
                    <!-- Username: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Username</th>
                    <!-- Full Name: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Full Name</th>
                    <!-- Role: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Role</th>
                    <!-- Email: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Email</th>
                    <!-- Contact: Hidden on small screens, visible on medium and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden md:table-cell">Contact</th>
                    <!-- Created At: Hidden on small and medium screens, visible on large and above -->
                    <th class="px-6 py-3 text-left text-sm font-semibold hidden lg:table-cell">Created At</th>
                    <!-- Actions: Visible on all screens -->
                    <th class="px-6 py-3 text-left text-sm font-semibold table-cell">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <!-- Username: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['username']; ?></td>
                    <!-- Full Name: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo $row['full_name']; ?></td>
                    <!-- Role: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell"><?php echo ucfirst($row['role']); ?></td>
                    <!-- Email: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo $row['email']; ?></td>
                    <!-- Contact: Hidden on small screens, visible on medium and above -->
                    <td class="px-6 py-4 text-sm hidden md:table-cell"><?php echo $row['contact']; ?></td>
                    <!-- Created At: Hidden on small and medium screens, visible on large and above -->
                    <td class="px-6 py-4 text-sm hidden lg:table-cell"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <!-- Actions: Visible on all screens -->
                    <td class="px-6 py-4 text-sm table-cell">
                        <div class="flex gap-2">
                            <button class="p-1 hover:bg-gray-100 rounded">
                                <a href="#userDetailsModal" class="viewDetails" rel="modal:open" data-user-id="<?php echo $row['id']; ?>">
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

<!-- Add modal for user details -->
<div id="userDetailsModal" class="modal px-0 py-2.5 md:p-10" style="max-width: 100vw !important;">
    <div class="modal-content">
        <div class="modal-header flex flex-row justify-between w-full gap-4 pb-2 md:pb-0">
            <h3 class="hidden md:block text-xl font-medium text-slate-800 px-2 md:px-0">User Details</h3>
            <button id="edit-user" class="ml-2 md:ml-0 px-4 py-2 bg-yellow-400 border-2 border-black rounded-lg flex items-center gap-2 hover:bg-yellow-500 transition-colors shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                <i class='bx bx-edit'></i>
                Edit User
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
            fetch(`${BASE_URL}/api/search_users.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer';
                            div.textContent = `${item.username} - ${item.role}`;
                            div.addEventListener('click', () => {
                                searchInput.value = item.username;
                                searchResults.classList.add('hidden');
                                const url = new URL(window.location);
                                url.searchParams.set('search', item.username);
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

    // View user details
    document.querySelectorAll('.viewDetails').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            setCookie("currentUserId", userId, 1);
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = '<div class="flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';

            fetch(`${BASE_URL}/api/view_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                  modalContent.innerHTML = `
    <div class="space-y-6 p-2 md:p-4 bg-white rounded-lg">
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
            <!-- User Information -->
            <div class="overflow-x-auto border-2 border-gray-300 rounded-lg h-fit w-full">
                <table class="w-full text-left max-h-auto">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-300">
                            <th colspan="2" class="px-4 py-3 text-lg font-semibold text-gray-700">
                                <i class='bx bx-user text-xl text-green-600'></i> User Information
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-200">
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Username</td>
                            <td class="px-4 py-3 text-gray-800">${data.username || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Full Name</td>
                            <td class="px-4 py-3 text-gray-800">${data.full_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Role</td>
                            <td class="px-4 py-3 text-gray-800">${data.role || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Email</td>
                            <td class="px-4 py-3 text-gray-800">${data.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Contact</td>
                            <td class="px-4 py-3 text-gray-800">${data.contact || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-600">Created At</td>
                            <td class="px-4 py-3 text-gray-800">${data.created_at ? new Date(data.created_at).toLocaleDateString() : 'N/A'}</td>
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

    // Edit user
    document.getElementById('edit-user').addEventListener('click', function() {
        const userId = getCookie("currentUserId");
        if (userId) {
            window.location.href = `${BASE_URL}/pages/users/edit_user.php?id=${userId}`;
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
        for(let i = 0; i < ca.length; i++)
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
</script>

<?php require_once '../../includes/footer.php'; ?>
