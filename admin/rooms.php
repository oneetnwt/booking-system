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
                if (isset($_POST['room_name'], $_POST['room_price'], $_POST['room_description'])) {
                    // Handle file upload
                    $image_path = '';
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../assets/rooms/';

                        // Create directory if it doesn't exist
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png'];

                        if (!in_array($file_extension, $allowed_extensions)) {
                            header("Location: rooms.php?error=invalid_format");
                            exit();
                        }

                        // Check file size (5MB max)
                        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                            header("Location: rooms.php?error=file_too_large");
                            exit();
                        }

                        // Generate unique filename
                        $filename = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $filename;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_path = 'rooms/' . $filename;
                        } else {
                            error_log('Failed to move uploaded file. Error: ' . error_get_last()['message']);
                            header("Location: rooms.php?error=upload_failed");
                            exit();
                        }
                    } else {
                        // Log the file upload error
                        $upload_error = isset($_FILES['image']) ? $_FILES['image']['error'] : 'No file uploaded';
                        error_log('File upload error: ' . $upload_error);
                        header("Location: rooms.php?error=upload_failed");
                        exit();
                    }

                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO room (room_name, room_price, room_description, image_path) 
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $_POST['room_name'],
                            $_POST['room_price'],
                            $_POST['room_description'],
                            $image_path
                        ]);
                        // Redirect to prevent form resubmission
                        header("Location: rooms.php?success=added");
                        exit();
                    } catch (PDOException $e) {
                        error_log('Database error: ' . $e->getMessage());
                        header("Location: rooms.php?error=database_error");
                        exit();
                    }
                } else {
                    header("Location: rooms.php?error=missing_fields");
                    exit();
                }
                break;
            case 'update':
                if (isset($_POST['room_id'], $_POST['room_name'], $_POST['room_price'], $_POST['room_description'], $_POST['image_path'])) {
                    $stmt = $pdo->prepare("
                        UPDATE room 
                        SET room_name = ?, room_price = ?, room_description = ?, image_path = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['room_name'],
                        $_POST['room_price'],
                        $_POST['room_description'],
                        $_POST['image_path'],
                        $_POST['room_id']
                    ]);
                    // Redirect to prevent form resubmission
                    header("Location: rooms.php?success=updated");
                    exit();
                }
                break;
            case 'delete':
                if (isset($_POST['room_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM room WHERE id = ?");
                    $stmt->execute([$_POST['room_id']]);
                    // Redirect to prevent form resubmission
                    header("Location: rooms.php?success=deleted");
                    exit();
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

// Add success message display
$success_message = '';
$error_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success_message = 'Room added successfully!';
            break;
        case 'updated':
            $success_message = 'Room updated successfully!';
            break;
        case 'deleted':
            $success_message = 'Room deleted successfully!';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_format':
            $error_message = 'Invalid image format. Please upload JPG, JPEG, or PNG files only.';
            break;
        case 'upload_failed':
            $error_message = 'Failed to upload image. Please try again.';
            break;
    }
}

// Debug information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST request received');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));
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
    $where_conditions[] = "(room_name LIKE ? OR room_description LIKE ?)";
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
                    <?php if ($success_message): ?>
                        <div class="alert alert-success" id="successMessage">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" id="errorMessage">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
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
            <form id="addRoomForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="room_name">Room Name:</label>
                    <input type="text" name="room_name" id="room_name" required>
                </div>
                <div class="form-group">
                    <label for="room_price">Price per Night:</label>
                    <input type="number" name="room_price" id="room_price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/jpg" required><br>
                    <small>Max file size: 5MB. Allowed formats: JPG, JPEG, PNG</small>
                </div>
                <div class="form-group">
                    <label for="room_description">Description:</label>
                    <textarea name="room_description" id="room_description" rows="4" required></textarea>
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
                    <label for="edit_room_price">Price per Night:</label>
                    <input type="number" name="room_price" id="edit_room_price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_image_path">Image Path:</label>
                    <input type="text" name="image_path" id="edit_image_path"
                        placeholder="e.g., ../assets/rooms/room1.jpg" required>
                </div>
                <div class="form-group">
                    <label for="edit_room_description">Description:</label>
                    <textarea name="room_description" id="edit_room_description" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn-primary">Update Room</button>
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
        // Initialize modals
        document.addEventListener('DOMContentLoaded', function () {
            // Handle success message
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                // Remove success message from URL without refreshing
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                window.history.replaceState({}, '', url);

                // Hide message after 3 seconds
                setTimeout(() => {
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 300);
                }, 3000);
            }

            const addRoomModal = document.getElementById('addRoomModal');
            const editRoomModal = document.getElementById('editRoomModal');
            const viewModal = document.getElementById('viewModal');
            const closeBtns = document.getElementsByClassName('close');

            // Add Room button click handler
            document.querySelector('.btn-primary').addEventListener('click', function () {
                addRoomModal.style.display = 'flex';
            });

            // Close button click handlers
            Array.from(closeBtns).forEach(btn => {
                btn.onclick = function () {
                    const modal = this.closest('.modal');
                    modal.style.display = 'none';
                }
            });

            // Click outside modal to close
            window.onclick = function (event) {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            }
        });

        function showAddRoomModal() {
            const modal = document.getElementById('addRoomModal');
            modal.style.display = 'flex';
        }

        function editRoom(roomId) {
            fetch(`get_room.php?id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_room_id').value = data.id;
                    document.getElementById('edit_room_name').value = data.room_name;
                    document.getElementById('edit_room_price').value = data.room_price;
                    document.getElementById('edit_room_description').value = data.room_description;
                    document.getElementById('editRoomModal').style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load room details');
                });
        }

        function viewRoom(roomId) {
            fetch(`get_room.php?id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    const details = document.getElementById('roomDetails');
                    details.innerHTML = `
                        <div class="room-details">
                        <img src="../assets/${data.image_path}" alt="${data.room_name}" style="max-width: 100%; height: auto; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                            <p><strong>Room Name:</strong> ${data.room_name}</p>
                            <p><strong>Price:</strong> ₱${data.room_price}</p>
                            <p><strong>Description:</strong> ${data.room_description}</p>
                        </div>
                    `;
                    const modal = document.getElementById('viewModal');
                    modal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load room details');
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
    </script>

    <style>
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            transition: opacity 0.3s ease;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</body>

</html>