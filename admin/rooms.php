<?php
session_start();
require_once '../db/connectDB.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['JWT_SECRET_KEY'];

// Check if user is logged in and is admin
if (!isset($_COOKIE['token'])) {
    header("Location: ../auth/login.php");
    exit();
}

try {
    $token = $_COOKIE['token'];
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

    if ($decoded->data->role !== 'admin') {
        header("Location: ../home/home.php");
        exit();
    }
} catch (Exception $e) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle room actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['room_name'], $_POST['room_type'], $_POST['price'], $_POST['capacity'], $_POST['description'])) {
                    $stmt = $pdo->prepare("
                        INSERT INTO room (room_name, room_type, price, capacity, description, status) 
                        VALUES (?, ?, ?, ?, ?, 'available')
                    ");
                    $stmt->execute([
                        $_POST['room_name'],
                        $_POST['room_type'],
                        $_POST['price'],
                        $_POST['capacity'],
                        $_POST['description']
                    ]);
                }
                break;
            case 'update':
                if (isset($_POST['room_id'], $_POST['room_name'], $_POST['room_type'], $_POST['price'], $_POST['capacity'], $_POST['description'])) {
                    $stmt = $pdo->prepare("
                        UPDATE room 
                        SET room_name = ?, room_type = ?, price = ?, capacity = ?, description = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['room_name'],
                        $_POST['room_type'],
                        $_POST['price'],
                        $_POST['capacity'],
                        $_POST['description'],
                        $_POST['room_id']
                    ]);
                }
                break;
            case 'delete':
                if (isset($_POST['room_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM room WHERE id = ?");
                    $stmt->execute([$_POST['room_id']]);
                }
                break;
            case 'update_status':
                if (isset($_POST['room_id'], $_POST['status'])) {
                    $stmt = $pdo->prepare("UPDATE room SET status = ? WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['room_id']]);
                }
                break;
        }
    }
}

// Fetch rooms with pagination and filters
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if ($type_filter) {
    $where_conditions[] = "room_type = ?";
    $params[] = $type_filter;
}

if ($search) {
    $where_conditions[] = "(room_name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM room $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_rooms = $stmt->fetchColumn();
$total_pages = ceil($total_rooms / $per_page);

// Get rooms
$query = "SELECT * FROM room $where_clause ORDER BY id DESC LIMIT $offset, $per_page";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get room types for filter
$room_types = $pdo->query("SELECT DISTINCT room_name FROM room")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - K&A Resort</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/admin.styles.css">
</head>

<body>
    <div class="admin-container">
        <header>
            <div class="logo">
                <img src="../assets/K&ALogo.png" alt="" class="circle-logo">
                <img src="../assets/K&A.png" alt="" class="logo-text">
            </div>
            <a href="../home/home.php">
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
                            <li>
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
                            <li class="active">
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
            <div class="main-content">
                <div class="main-container">
                    <div class="main-header-row">
                        <h3>Room Management</h3>
                        <button class="btn-primary" onclick="showAddRoomModal()">
                            <i class="fas fa-plus"></i> Add Room
                        </button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Room ID</th>
                                    <th>Room Name</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $room): ?>
                                    <tr>
                                        <td>#<?php echo $room['id']; ?></td>
                                        <td><?php echo $room['room_name']; ?></td>
                                        <td>₱<?php echo number_format($room['room_price'], 2); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon" onclick="viewRoom(<?php echo $room['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-icon" onclick="editRoom(<?php echo $room['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-icon"
                                                    onclick="updateRoomStatus(<?php echo $room['id']; ?>)">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <button class="btn-icon delete"
                                                    onclick="deleteRoom(<?php echo $room['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Pagination (if needed) -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div id="addRoomModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Room</h2>
            <form id="addRoomForm" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="room_name">Room Name:</label>
                    <input type="text" name="room_name" id="room_name" required>
                </div>
                <div class="form-group">
                    <label for="room_type">Room Type:</label>
                    <select name="room_type" id="room_type" required>
                        <option value="standard">Standard</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="suite">Suite</option>
                        <option value="family">Family</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price per Night:</label>
                    <input type="number" name="price" id="price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity:</label>
                    <input type="number" name="capacity" id="capacity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Add Room</button>
            </form>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Room</h2>
            <form id="editRoomForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="room_id" id="edit_room_id">
                <div class="form-group">
                    <label for="edit_room_name">Room Name:</label>
                    <input type="text" name="room_name" id="edit_room_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_room_type">Room Type:</label>
                    <select name="room_type" id="edit_room_type" required>
                        <option value="standard">Standard</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="suite">Suite</option>
                        <option value="family">Family</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_price">Price per Night:</label>
                    <input type="number" name="price" id="edit_price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_capacity">Capacity:</label>
                    <input type="number" name="capacity" id="edit_capacity" min="1" required>
                </div>
                <div class="form-group">
                    <label for="edit_description">Description:</label>
                    <textarea name="description" id="edit_description" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Update Room</button>
            </form>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Room Status</h2>
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="room_id" id="status_room_id">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <!-- View Room Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Room Details</h2>
            <div id="roomDetails"></div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function (e) {
            const searchTerm = e.target.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('search', searchTerm);
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function (e) {
            const status = e.target.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('status', status);
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        });

        // Type filter
        document.getElementById('typeFilter').addEventListener('change', function (e) {
            const type = e.target.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('type', type);
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        });

        // Modal functionality
        const addRoomModal = document.getElementById('addRoomModal');
        const editRoomModal = document.getElementById('editRoomModal');
        const statusModal = document.getElementById('statusModal');
        const viewModal = document.getElementById('viewModal');
        const closeBtns = document.getElementsByClassName('close');

        function showAddRoomModal() {
            addRoomModal.style.display = 'block';
        }

        function editRoom(roomId) {
            // Fetch room details and populate form
            fetch(`get_room.php?id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_room_id').value = data.id;
                    document.getElementById('edit_room_name').value = data.room_name;
                    document.getElementById('edit_room_type').value = data.room_type;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_capacity').value = data.capacity;
                    document.getElementById('edit_description').value = data.description;
                    editRoomModal.style.display = 'block';
                });
        }

        function updateRoomStatus(roomId) {
            document.getElementById('status_room_id').value = roomId;
            statusModal.style.display = 'block';
        }

        function viewRoom(roomId) {
            // Fetch room details and display in modal
            fetch(`get_room.php?id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    const details = document.getElementById('roomDetails');
                    details.innerHTML = `
                        <div class="room-details">
                            <p><strong>Room Name:</strong> ${data.room_name}</p>
                            <p><strong>Type:</strong> ${data.room_type}</p>
                            <p><strong>Price:</strong> ₱${data.price}</p>
                            <p><strong>Capacity:</strong> ${data.capacity} persons</p>
                            <p><strong>Status:</strong> <span class="status-badge ${data.status}">${data.status}</span></p>
                            <p><strong>Description:</strong></p>
                            <p class="room-description">${data.description}</p>
                        </div>
                    `;
                    viewModal.style.display = 'block';
                });
        }

        function deleteRoom(roomId) {
            if (confirm('Are you sure you want to delete this room?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="room_id" value="${roomId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        Array.from(closeBtns).forEach(btn => {
            btn.onclick = function () {
                addRoomModal.style.display = 'none';
                editRoomModal.style.display = 'none';
                statusModal.style.display = 'none';
                viewModal.style.display = 'none';
            }
        });

        window.onclick = function (event) {
            if (event.target == addRoomModal) {
                addRoomModal.style.display = 'none';
            }
            if (event.target == editRoomModal) {
                editRoomModal.style.display = 'none';
            }
            if (event.target == statusModal) {
                statusModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        }
    </script>
</body>

</html>