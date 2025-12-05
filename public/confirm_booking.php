<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: browse.php");
    exit;
}

$car_id     = (int) $_POST["car_id"];
$start_date = $_POST["start_date"];
$end_date   = $_POST["end_date"];

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id");
$stmt->execute([":id" => $car_id]);
$car = $stmt->fetch();

if (!$car) {
    die("Car not found.");
}

$start = new DateTime($start_date);
$end   = new DateTime($end_date);

$days = $start->diff($end)->days;
if ($days <= 0) {
    die("Invalid date range.");
}

$rate  = $car["daily_rate"];
$total = $days * $rate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Confirm Booking</title>
</head>
<body>

<header>
    <a href="index.php"><h2>QRS Car Rentals</h2></a>
    <nav>
        <ul>
            <li><a href="browse.php">Browse</a></li>
            <li><a href="my_bookings.php">My Bookings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main class="browse-main">
    <div class="card gradient" style="max-width:600px;margin:auto;">

        <h2>Review Your Booking</h2>

        <h3><?= htmlspecialchars($car["make"] . " " . $car["model"]) ?></h3>
        <p><?= htmlspecialchars($car["year"]) ?></p>

        <img src="<?= htmlspecialchars($car["image_url"]) ?>"
             style="width:100%; border-radius:8px; margin-bottom:1rem;">

        <p><strong>Start Date:</strong> <?= htmlspecialchars($start_date) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($end_date) ?></p>

        <p><strong>Daily Rate:</strong> $<?= number_format($rate, 2) ?></p>
        <p><strong>Total Days:</strong> <?= $days ?></p>

        <h3><strong>Total Price: $<?= number_format($total, 2) ?></strong></h3>

        <form action="finalize_booking.php" method="post">

            <input type="hidden" name="car_id" value="<?= $car_id ?>">
            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">

            <button type="submit">Confirm Booking</button>
        </form>

        <br>

        <a href="javascript:history.back();" style="color:#fff;">Go Back</a>
    </div>
</main>

</body>
</html>
