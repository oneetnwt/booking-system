<?php
// The business logic has been moved to the controller
// This view now only handles presentation logic

if (!isset($_COOKIE['token'])) {
    header("Location: /home?error=unauthorized");
    exit();
}

// Get room details if room_id is set (data should be provided by the controller)
$room = $room ?? null; // This will use the room variable provided by the controller
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles//booking.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/K&ALogo.png">
    <title>Booking Process</title>
</head>

<body>
    <header>
        <div class="heading">
            <span id="active">1</span>
            Confirmation & Extras
        </div>
        <div class="heading">
            <span>2</span>
            Guest Details
        </div>
        <div class="heading">
            <span>3</span>
            Payment
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="content">
                <div class="booking-container">
                    <form action="" method="POST" id="booking-confirmation-form" onsubmit="return handleSubmit(event)">
                        <div class="form-group">
                            <label for="">Check in:</label>
                            <input type="datetime-local"
                                value="<?= !empty($_SESSION['booking_details']['check_in']) ? htmlspecialchars($_SESSION['booking_details']['check_in']) : ''; ?>"
                                name="check_in" required>
                        </div>
                        <div class="form-group">
                            <label for="">Check out:</label>
                            <input type="datetime-local"
                                value="<?= !empty($_SESSION['booking_details']['check_out']) ? htmlspecialchars($_SESSION['booking_details']['check_out']) : ''; ?>"
                                name="check_out" required>
                        </div>
                        <div class="form-group">
                            <label for="">Adult:</label>
                            <input type="number" value="<?= htmlspecialchars($_SESSION['booking_details']['adult']); ?>"
                                placeholder="0" name="adult">
                        </div>
                        <div class="form-group">
                            <label for="">Children:</label>
                            <input type="number"
                                value="<?= htmlspecialchars($_SESSION['booking_details']['children']); ?>"
                                placeholder="0" name="children">
                        </div>
                        <div class="form-group">
                            <label for="">Overnight:</label>
                            <div class="overnight">
                                <div class="form-group">
                                    <input type="radio" id="overnight-yes" value="yes" name="overnight"
                                        <?= ($_SESSION['booking_details']['overnight'] ?? 'no') == 'yes' ? 'checked' : ''; ?>>
                                    <label for="overnight-yes">Yes</label>
                                </div>
                                <div class="form-group">
                                    <input type="radio" id="overnight-no" value="no" name="overnight"
                                        <?= ($_SESSION['booking_details']['overnight'] ?? 'no') == 'no' ? 'checked' : ''; ?>>
                                    <label for="overnight-no">No</label>
                                </div>
                            </div>
                        </div>
                        <button>Submit</button>
                    </form>
                </div>
                <div class="details">
                    <h2>Room</h2>
                    <div class="room-details">
                        <div class="detail-main">
                            <h3><?= htmlspecialchars($room['room_name'] ?? 'Room not selected'); ?></h3>
                            <p><i class="fa-solid fa-check" style="color: green; margin-right: 0.5rem;"></i>No refund<i
                                    class="fa fa-info-circle" style="margin-left: 0.5rem;" aria-hidden="true"></i></p>
                        </div>
                        <div class="detail-sub">
                            <p>1 room, <?= htmlspecialchars($_SESSION['booking_details']['adult']); ?> adults and
                                <?= htmlspecialchars($_SESSION['booking_details']['children']); ?> child included in
                                price
                            </p>
                            <h3>₱ <?= number_format($room['room_price'] ?? 0, 2, '.', ''); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="booking-policy">
                    <div class="policy-header">
                        <h3>Booking Policy</h3>
                        <p>Our booking includes items with different booking policies</p>
                    </div>
                    <a href="">View all policies</a>
                </div>
                <div class="buttons">
                    <a href="javascript:history.go(-1)"><i class="fa-solid fa-arrow-left"></i>Back</a>
                    <a href="javascript:void(0)" onclick="submitAndProceed()">Next <i
                            class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="order-details">
                <h3>Price Summary</h3>
                <div class="date">
                    <p><?= !empty($_SESSION['booking_details']['check_in']) ? date('D, d M Y', strtotime($_SESSION['booking_details']['check_in'])) : '-'; ?><br><span>From:
                            <?= !empty($_SESSION['booking_details']['check_in']) ? date('h:i A', strtotime($_SESSION['booking_details']['check_in'])) : '-'; ?></span>
                    </p>
                    <i class="fa-solid fa-arrow-right"></i>
                    <p><?= !empty($_SESSION['booking_details']['check_out']) ? date('D, d M Y', strtotime($_SESSION['booking_details']['check_out'])) : '-'; ?><br><span>Until:
                            <?= !empty($_SESSION['booking_details']['check_out']) ? date('h:i A', strtotime($_SESSION['booking_details']['check_out'])) : '-'; ?></span>
                    </p>
                </div>
                <div class="prices">
                    <div class="room">
                        <p>Room</p>
                        <p class="amount">₱ <?= number_format($_SESSION['booking_details']['roomPrice'], 2, '.', ''); ?>
                        </p>
                    </div>
                    <div class="entrance">
                        <p>Entrance Fee</p>
                        <p class="amount">₱
                            <?= number_format($_SESSION['booking_details']['entranceFee'], 2, '.', ''); ?>
                        </p>
                    </div>
                    <div class="booking-fee">
                        <p>Booking Fee (1%)</p>
                        <p class="amount">₱
                            <?= number_format($_SESSION['booking_details']['bookingFee'], 2, '.', ''); ?>
                        </p>
                    </div>
                    <div class="booking-fee">
                        <p>Number of Days</p>
                        <p class="amount">
                            <?= $_SESSION['booking_details']['days'] ?>
                        </p>
                    </div>
                </div>
                <div class="total">
                    <p>Total</p>
                    <p>₱ <?= number_format($_SESSION['booking_details']['totalPrice'], 2, '.', ''); ?>
                        <br>
                        <span>
                            Price includes ₱
                            <?= number_format($_SESSION['booking_details']['bookingFee'], 2, '.', ''); ?>
                        </span>
                    </p>
                </div>
                <?php if (isset($_SESSION['error'])): ?>
                    <p style="color: #F74141; font-weight: 500; text-align: center; margin-top: 12px; font-style: italic;">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <script>
        function handleSubmit(event) {
            const checkIn = document.querySelector('input[name="check_in"]').value;
            const checkOut = document.querySelector('input[name="check_out"]').value;
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set to start of day

            if (!checkIn || !checkOut) {
                event.preventDefault();
                alert('Please select both check-in and check-out dates before proceeding.');
                return false;
            }

            const checkInDate = new Date(checkIn);
            if (checkInDate < today) {
                event.preventDefault();
                alert('Check-in date cannot be before today.');
                return false;
            }

            return true;
        }

        function submitAndProceed() {
            const form = document.getElementById('booking-confirmation-form');
            const checkIn = document.querySelector('input[name="check_in"]').value;
            const checkOut = document.querySelector('input[name="check_out"]').value;
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set to start of day

            if (!checkIn || !checkOut) {
                alert('Please select both check-in and check-out dates before proceeding.');
                return;
            }

            const checkInDate = new Date(checkIn);
            if (checkInDate < today) {
                alert('Check-in date cannot be before today.');
                return;
            }

            form.submit();
        }
    </script>
</body>

</html>