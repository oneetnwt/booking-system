<?php
session_start();
require_once '../db/connectDB.php';
require_once '../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

// Check if user is logged in and is admin
if (!isset($_COOKIE['token'])) {
    header("Location: /auth/login");
    exit();
}

try {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    if ($decoded->data->role !== 'admin') {
        header("Location: /home");
        exit();
    }
} catch (Exception $e) {
    header("Location: /auth/login");
    exit();
}

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                if (isset($_POST['booking_id']) && isset($_POST['status'])) {
                    $stmt = $pdo->prepare("UPDATE booking SET status = ? WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['booking_id']]);
                }
                break;
        }
    }
}

// Fetch bookings with pagination and filters
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "b.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(u.firstname LIKE ? OR u.lastname LIKE ? OR r.room_name LIKE ? OR b.id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) 
    FROM booking b 
    JOIN booking_invoice bi ON b.id = bi.booking_id
    JOIN users u ON bi.user_id = u.id 
    JOIN room r ON b.room_id = r.id 
    $where_clause
";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_bookings = $stmt->fetchColumn();
$total_pages = ceil($total_bookings / $per_page);

// Get bookings
$query = "
    SELECT b.*, u.firstname, u.lastname, r.room_name, p.amount as total_amount, bd.check_in, bd.check_out
    FROM booking b 
    JOIN booking_invoice bi ON b.id = bi.booking_id
    JOIN users u ON bi.user_id = u.id 
    JOIN room r ON b.room_id = r.id 
    LEFT JOIN payment p ON b.payment_id = p.id
    LEFT JOIN booking_details bd ON b.booking_details_id = bd.id
    $where_clause
    ORDER BY bd.check_in ASC 
    LIMIT $offset, $per_page
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - K&A Resort</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="styles/admin.styles.css">
    <link rel="icon" href="../assets/K&ALogo.png">

</head>

<body>
    <header>
        <div class="logo">
            <img src="../assets/K&ALogo.png" alt="" class="circle-logo">
            <img src="../assets/K&A.png" alt="" class="logo-text">
        </div>
        <a href="/">
            <span class="mdi mdi-home"></span>
            Home
        </a>
    </header>
    <div class="main">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-content">
                <h3>ADMIN PANEL</h3>
                <nav>
                    <ul>
                        <li>
                            <a href="dashboard.php">
                                <span class="mdi mdi-view-dashboard"></span>
                                Dashboard
                            </a>
                        </li>
                        <li class="active">
                            <a href="bookings.php">
                                <span class="mdi mdi-file-tree"></span>
                                Bookings
                            </a>
                        </li>
                        <li>
                            <a href="users.php">
                                <span class="mdi mdi-account-multiple"></span>
                                Users
                            </a>
                        </li>
                        <li>
                            <a href="reviews.php">
                                <span class="mdi mdi-star-box"></span>
                                Reviews and Ratings
                            </a>
                        </li>
                        <li>
                            <a href="rooms.php">
                                <span class="mdi mdi-bed"></span>
                                Rooms
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <div class="main-container">
                <div class="main-header-row">
                    <h3>Booking Management</h3>
                    <div class="filter-actions">
                        <input type="text" id="searchInput" class="filter-select" placeholder="Search bookings...">
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>
                                Confirmed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>
                                Cancelled</option>
                            <option value="done" <?php echo $status_filter === 'done' ? 'selected' : ''; ?>>Done</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr style="<?php
                                    if ($booking['check_in'] < date('Y-m-d') && $booking['status'] == 'pending') {
                                        echo 'background-color: #F74141; color: white;';
                                    } elseif ($booking['status'] == 'done') {
                                        echo 'background-color: #77DD77; color: black;';
                                    }
                                    ?>">
                                        <td>#<?= $booking['id']; ?></td>
                                        <td><?= $booking['firstname'] . ' ' . $booking['lastname']; ?></td>
                                        <td><?= $booking['room_name']; ?></td>
                                        <td><?= date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                        <td><?= date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                        <td>₱<?= number_format($booking['total_amount'] ?? 0, 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $booking['status']; ?>">
                                                <?= ucfirst($booking['status'] === 'done' ? "Completed" : "Pending") ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view-booking.php?id=<?= $booking['id']; ?>" class="btn-icon"
                                                    title="View Booking">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn-icon"
                                                    onclick="updateStatus(<?php echo $booking['id']; ?>)"
                                                    title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"
                                    class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"
                                    class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"
                                    class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Booking Status</h2>
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" id="bookingId">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="pending">Pending</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.querySelector('tbody');
        const pagination = document.querySelector('.pagination');

        function updateTable() {
            const searchTerm = searchInput.value;
            const status = statusFilter.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', searchTerm);
            currentUrl.searchParams.set('status', status);
            currentUrl.searchParams.set('page', '1');
            currentUrl.searchParams.set('ajax', '1');

            fetch(currentUrl.toString())
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableBody = doc.querySelector('tbody');
                    const newPagination = doc.querySelector('.pagination');

                    if (newTableBody) {
                        tableBody.innerHTML = newTableBody.innerHTML;
                    }
                    if (newPagination) {
                        pagination.innerHTML = newPagination.innerHTML;
                    } else {
                        pagination.innerHTML = '';
                    }
                });
        }

        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function (e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(updateTable, 500);
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', updateTable);
        }

        // Handle pagination clicks
        document.addEventListener('click', function (e) {
            if (e.target.closest('.page-link')) {
                e.preventDefault();
                const page = e.target.closest('.page-link').getAttribute('href').split('page=')[1].split('&')[0];
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('page', page);
                currentUrl.searchParams.set('ajax', '1');

                fetch(currentUrl.toString())
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newTableBody = doc.querySelector('tbody');
                        const newPagination = doc.querySelector('.pagination');

                        if (newTableBody) {
                            tableBody.innerHTML = newTableBody.innerHTML;
                        }
                        if (newPagination) {
                            pagination.innerHTML = newPagination.innerHTML;
                        }
                    });
            }
        });

        // Modal functionality
        const modal = document.getElementById('statusModal');
        const closeBtn = document.getElementsByClassName('close')[0];

        function updateStatus(bookingId) {
            document.getElementById('bookingId').value = bookingId;
            modal.style.display = 'block';
        }

        closeBtn.onclick = function () {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>

</html>