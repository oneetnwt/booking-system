<?php
session_start();

if (!isset($_COOKIE['token'])) {
    header("Location: home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['bookingId'];
}

if (!isset($booking_id)) {
    header("Location: my-bookings.php");
    exit();
}

if (!isset($_POST['bookingId']) || empty($_POST['bookingId'])) {
    header("Location: my-bookings.php");
    exit();
}


require_once '../vendor/autoload.php';
require '../db/connectDB.php';

$stmt = $pdo->prepare("
    SELECT
        bi.*,
        b.*,
        u.*,
        bd.*,
        r.*,
        p.*
    FROM
        booking_invoice bi
    JOIN
        booking b on bi.booking_id = b.id
    JOIN
        users u on bi.user_id = u.id
    JOIN
        booking_details bd on b.booking_details_id = bd.id
    JOIN
        room r on b.room_id = r.id
    JOIN
        payment p on b.payment_id = p.id
    WHERE
        bi.booking_id = ?
");
$stmt->execute([$booking_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/invoice.styles.css">
    <title>Payment Invoice</title>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <img src="../assets/K&A_Dark.png" alt="K&A Logo">
                <img src="../assets/K&ALogo.png" alt="K&A Logo">
            </div>
            <div class="details">
                <div class="booking-details">
                    <h3>Booking Details</h3>
                    <div class="detail-group">
                        <p>Check-in</p>
                        <p><?= $data['check_in'] ?></p>
                    </div>
                    <div class="detail-group">
                        <p>Check-out</p>
                        <p><?= $data['check_out'] ?></p>
                    </div>
                    <div class="detail-group">
                        <p>Overnight</p>
                        <p><?= ucfirst($data['overnight']) ?></p>
                    </div>
                    <div class="detail-group">
                        <p>Guest</p>
                        <p><?= $data['adult'] ?> adult, <?= $data['child'] ?> child</p>
                    </div>
                    <div class="detail-group">
                        <p>Accommodation</p>
                        <p><?= $data['room_name'] ?></p>
                    </div>
                </div>
                <div class="bookedby">
                    <div class="user">
                        <h3>Booked By</h3>
                        <div class="booked">
                            <p><?= $data['firstname'] . ' ' . $data['lastname'] ?></p>
                            <p><?= $data['email'] ?></p>
                        </div>
                    </div>
                    <div class="booking-status">

                        <div class="status-group">
                            <p>Transaction ID</p>
                            <p><?= $data['transaction_id'] ?></p>
                        </div>
                        <div class="status-group">
                            <p>Booking Date</p>
                            <p><?= $data['created_at'] ?></p>
                        </div>
                        <div class="status-group">
                            <p>Payment Method</p>
                            <p><?= ucfirst($data['payment_method']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="payment-details">
                    <table>
                        <thead>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td><?= $data['room_name'] ?></td>
                                <td>₱ <?= $data['room_price'] ?></td>
                                <td>₱ <?= $data['room_price'] ?></td>
                            </tr>
                            <tr>
                                <td><?= $data['adult'] ?></td>
                                <td>Adult Entrance</td>
                                <td>₱ <?= $data['overnight'] === 'yes' ? 200 : 50 ?>.00</td>
                                <td>₱
                                    <?= $data['overnight'] === 'yes' ? $data['adult'] * 200 : $data['adult'] * 50 ?>.00
                                </td>
                            </tr>
                            <tr>
                                <td><?= $data['child'] ?></td>
                                <td>Child Entrance</td>
                                <td>₱ <?= $data['overnight'] === 'yes' ? 200 : 50 ?>.00</td>
                                <td>₱
                                    <?= $data['overnight'] === 'yes' ? $data['child'] * 200 : $data['child'] * 50 ?>.00
                                </td>
                            </tr>
                            <tr style="border-top: 1px solid #00000050;">
                                <td></td>
                                <td></td>
                                <td>Booking Fee</td>
                                <td>₱ <?= $data['booking_fee'] ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td>₱ <?= $data['amount'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <p style="text-align: center; margin-top: 1rem;">&copy; <?= date("Y") ?> K&A Natural Spring Resort</p>
            <a href="my-bookings.php"
                style="padding: 0.5rem 1rem; background-color: #3165e8; color: white; text-decoration: none; border-radius: 0.5rem; text-align: center; align-self: center; margin-top: 1rem;">Return</a>
        </div>
    </div>
</body>

</html>