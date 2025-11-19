<?php
session_start();
require __DIR__ . '/db.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html?error=login_required');
    exit;
}

$user_id          = $_SESSION['user_id'];
$error            = '';
$success          = '';
$start_input_date = '';
$end_input_date   = '';

// If the form was submitted, handle the booking (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id     = (int)($_POST['car_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';

    // keep values for re-display
    $start_input_date = $start_date;
    $end_input_date   = $end_date;

    if ($car_id <= 0 || $start_date === '' || $end_date === '') {
        $error = 'Please fill in all fields.';
    } else {
        // basic date validation
        $start = date_create($start_date);
        $end   = date_create($end_date);

        if (!$start || !$end) {
            $error = 'Invalid dates.';
        } elseif ($start >= $end) {
            // same rule as browse.php: end must be after start
            $error = 'End date must be after start date.';
        } else {
            // 1) Check for overlapping rentals for this car
            //    Same overlap rule as browse.php:
            //    existing overlaps new when NOT (
            //        existing.end_date <= new_start OR existing.start_date >= new_end
            //    )
            $sql = "
                SELECT COUNT(*) AS cnt
                FROM rentals
                WHERE car_id = :car_id
                  AND status IN ('pending', 'confirmed')
                  AND NOT (
                      end_date   <= :start_date
                      OR start_date >= :end_date
                  )
            ";
            $check = $pdo->prepare($sql);
            $check->execute([
                ':car_id'     => $car_id,
                ':start_date' => $start_date,
                ':end_date'   => $end_date,
            ]);
            $row = $check->fetch();

            if ($row && $row['cnt'] > 0) {
                $error = 'Sorry, this car is already booked for those dates.';
            } else {
                // 2) Insert rental
                $ins = $pdo->prepare("
                    INSERT INTO rentals (user_id, car_id, start_date, end_date, status)
                    VALUES (:user_id, :car_id, :start_date, :end_date, 'pending')
                ");
                $ins->execute([
                    ':user_id'    => $user_id,
                    ':car_id'     => $car_id,
                    ':start_date' => $start_date,
                    ':end_date'   => $end_date,
                ]);

                $success = 'Your booking has been created!';
            }
        }
    }

    // Load car info again for redisplay
    $car_id_for_display = $car_id;
} else {
    // Initial GET – coming from browse.php with ?car_id=...
    $car_id_for_display = (int)($_GET['car_id'] ?? 0);

    // Optional: prefill dates if passed via GET
    $start_input_date = $_GET['start_date'] ?? '';
    $end_input_date   = $_GET['end_date'] ?? '';
}

// Fetch the car being rented
$carStmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id AND available = 1");
$carStmt->execute([':id' => $car_id_for_display]);
$car = $carStmt->fetch();

if (!$car) {
    http_response_code(404);
    echo "Car not found.";
    exit;
}

$title    = htmlspecialchars($car['make'] . ' ' . $car['model']);
$subtitle = htmlspecialchars($car['year'] . ' | Sedan');
$rate     = number_format($car['daily_rate'], 2);
$img      = $car['image_url'] ?: 'image/corolla.avif';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Rent <?= $title ?> | QRS Car Rentals</title>
</head>
<body>
<header>
  <a href="index.php"><h2>QRS Car Rentals</h2></a>
  <nav>
    <ul>
        <li><a href="browse.php">Browse</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>
</header>

<main class="browse-main" style="max-width: 800px; margin: 2rem auto;">
    <div class="card gradient">
        <div class="card-top">
            <h3 class="card-title"><?= $title ?></h3>
            <p class="card-p"><?= $subtitle ?></p>
            <div class="icon-container">
                <div class="card-details">
                    <img class="card-people-icon" src="image/person-icon.svg" alt="number of people icon">
                    <p><?= (int)$car['seats'] ?></p>
                </div>
                <div class="card-details">
                    <img class="card-luggage-icon" src="image/luggage-icon.svg" alt="number of luggage icon">
                    <p><?= (int)$car['luggage_capacity'] ?></p>
                </div>
                <div class="card-details">
                    <img class="card-transmission-icon" src="image/automatic-icon.svg" alt="transmission icon">
                    <p><?= ucfirst($car['transmission']) ?></p>
                </div>
            </div>
        </div>

        <div class="card-img-container">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= $title ?>">
        </div>

        <p>$<?= $rate ?> /day</p>

        <?php if ($error): ?>
            <p style="color: red; margin-top: 0.5rem;"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p style="color: green; margin-top: 0.5rem;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

<form action="confirm_booking.php" method="post"
 style="margin-top: 1rem;">
            <input type="hidden" name="car_id" value="<?= $car['id'] ?>">

            <label>
                Start date:
                <input
                    type="date"
                    name="start_date"
                    required
                    value="<?= htmlspecialchars($start_input_date) ?>"
                    min="<?= date('Y-m-d') ?>"
                >
            </label>
            <br><br>
            <label>
                End date:
                <input
                    type="date"
                    name="end_date"
                    required
                    value="<?= htmlspecialchars($end_input_date) ?>"
                    min="<?= date('Y-m-d') ?>"
                >
            </label>
            <br><br>
            <button type="submit">Confirm Booking</button>
        </form>
    </div>
</main>

<footer>
    <div class="footer-contact">
        <div class="footer-container">
            <h5>CONTACT</h5>
            <p><a href="">Example@email.com</a></p>
        </div>
        <div class="footer-container">
            <h5>FOLLOW</h5>
            <div class="footer-social-media">
                <a href=""><img class="footer-icon" src="image/instagram-brands-solid.svg" alt="instagram icon"></a>
                <a href=""><img class="footer-icon" src="image/twitter-brands-solid.svg" alt="twitter icon"></a>
            </div>
        </div>
    </div>
    <p class="footer-p">&copy;2025 QRS Rental System. All rights reserved</p>
</footer>
</body>
</html>
