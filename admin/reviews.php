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

// Handle review actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['review_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                    $stmt->execute([$_POST['review_id']]);
                }
                break;
            case 'update_status':
                if (isset($_POST['review_id']) && isset($_POST['status'])) {
                    $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['review_id']]);
                }
                break;
        }
    }
}

// Fetch reviews with pagination and filters
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$rating_filter = isset($_GET['rating']) ? (int) $_GET['rating'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "r.status = ?";
    $params[] = $status_filter;
}

if ($rating_filter > 0) {
    $where_conditions[] = "r.rating = ?";
    $params[] = $rating_filter;
}

if ($search) {
    $where_conditions[] = "(u.firstname LIKE ? OR u.lastname LIKE ? OR rm.room_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN room rm ON r.room_id = rm.id 
    $where_clause
";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_reviews = $stmt->fetchColumn();
$total_pages = ceil($total_reviews / $per_page);

// Get reviews
$query = "
    SELECT r.*, u.firstname, u.lastname, rm.room_name
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN room rm ON r.room_id = rm.id 
    $where_clause
    ORDER BY r.created_at DESC 
    LIMIT $offset, $per_page
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get average rating
$avg_rating_query = "SELECT AVG(rating) as avg_rating FROM reviews";
$avg_rating = $pdo->query($avg_rating_query)->fetch(PDO::FETCH_ASSOC)['avg_rating'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management - K&A Resort</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
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
                            <li class="active">
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
            <div class="main-content">
                <div class="main-container">
                    <h3>Reviews Management</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Review ID</th>
                                    <th>Guest Name</th>
                                    <th>Room</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $review): ?>
                                    <tr>
                                        <td>#<?php echo $review['id']; ?></td>
                                        <td><?php echo $review['firstname'] . ' ' . $review['lastname']; ?></td>
                                        <td><?php echo $review['room_name']; ?></td>
                                        <td>
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i
                                                        class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </td>
                                        <td class="comment-cell">
                                            <div class="comment-content">
                                                <?php echo $review['comment']; ?>
                                            </div>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                        <td>
                                            <?php echo $review['room_name']; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <!-- Pagination (if needed) -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&rating=<?php echo $rating_filter; ?>&search=<?php echo urlencode($search); ?>"
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

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Update Review Status</h2>
            <form id="statusForm" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="review_id" id="reviewId">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <!-- View Review Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Review Details</h2>
            <div id="reviewDetails"></div>
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

        // Rating filter
        document.getElementById('ratingFilter').addEventListener('change', function (e) {
            const rating = e.target.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('rating', rating);
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        });

        // Modal functionality
        const statusModal = document.getElementById('statusModal');
        const viewModal = document.getElementById('viewModal');
        const closeBtns = document.getElementsByClassName('close');

        function updateStatus(reviewId) {
            document.getElementById('reviewId').value = reviewId;
            statusModal.style.display = 'block';
        }

        function viewReview(reviewId) {
            // Fetch review details and display in modal
            fetch(`get_review.php?id=${reviewId}`)
                .then(response => response.json())
                .then(data => {
                    const details = document.getElementById('reviewDetails');
                    details.innerHTML = `
                        <div class="review-details">
                            <p><strong>Guest:</strong> ${data.firstname} ${data.lastname}</p>
                            <p><strong>Room:</strong> ${data.room_name}</p>
                            <p><strong>Rating:</strong> 
                                <div class="stars">
                                    ${Array(5).fill().map((_, i) =>
                        `<i class="fas fa-star ${i < data.rating ? 'active' : ''}"></i>`
                    ).join('')}
                                </div>
                            </p>
                            <p><strong>Comment:</strong></p>
                            <p class="review-comment">${data.comment}</p>
                            <p><strong>Date:</strong> ${new Date(data.created_at).toLocaleDateString()}</p>
                            <p><strong>Status:</strong> <span class="status-badge ${data.status}">${data.status}</span></p>
                        </div>
                    `;
                    viewModal.style.display = 'block';
                });
        }

        function deleteReview(reviewId) {
            if (confirm('Are you sure you want to delete this review?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="review_id" value="${reviewId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        Array.from(closeBtns).forEach(btn => {
            btn.onclick = function () {
                statusModal.style.display = 'none';
                viewModal.style.display = 'none';
            }
        });

        window.onclick = function (event) {
            if (event.target == statusModal) {
                statusModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        }

        // Get modal elements
        const modal = document.getElementById('viewModal');
        const closeBtn = document.querySelector('.close');
        const reviewDetails = document.getElementById('reviewDetails');

        // Function to load review details
        function loadReviewDetails(reviewId) {
            fetch(`get_review.php?id=${reviewId}`)
                .then(response => response.json())
                .then(review => {
                    reviewDetails.innerHTML = `
                        <div class="review-info">
                            <p><strong>Guest:</strong> ${review.firstname} ${review.lastname}</p>
                            <p><strong>Room:</strong> ${review.room_name}</p>
                            <p><strong>Check-in:</strong> ${new Date(review.check_in).toLocaleDateString()}</p>
                            <p><strong>Check-out:</strong> ${new Date(review.check_out).toLocaleDateString()}</p>
                            <p><strong>Rating:</strong> 
                                <div class="stars">
                                    ${Array(5).fill().map((_, i) =>
                        `<i class="fas fa-star ${i < review.rating ? 'active' : ''}"></i>`
                    ).join('')}
                                </div>
                            </p>
                            <p><strong>Comment:</strong></p>
                            <p class="review-comment">${review.comment || 'No comment provided'}</p>
                            <p><strong>Date:</strong> ${new Date(review.created_at).toLocaleDateString()}</p>
                        </div>
                    `;
                    modal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error loading review details:', error);
                });
        }

        // Add click event to view buttons
        document.querySelectorAll('.view-review').forEach(button => {
            button.addEventListener('click', () => {
                const reviewId = button.getAttribute('data-id');
                loadReviewDetails(reviewId);
            });
        });

        // Close modal when clicking the close button
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>

</html>