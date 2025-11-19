<?php
session_start();
require __DIR__ . '/db.php';

$search       = trim($_GET['search']      ?? '');
$model        = trim($_GET['model']       ?? '');
$min_year     = $_GET['min_year']         ?? '';
$max_year     = $_GET['max_year']         ?? '';
$min_seats    = $_GET['min_seats']        ?? '';
$min_luggage  = $_GET['min_luggage']      ?? '';
$transmission = $_GET['transmission']     ?? '';
$max_rate     = $_GET['max_rate']         ?? '';
$start_date   = $_GET['start_date']       ?? '';
$end_date     = $_GET['end_date']         ?? '';

// Main query: cars + "is_available_today" flag
$sql = "SELECT c.*,
               NOT EXISTS (
                   SELECT 1
                   FROM rentals rt
                   WHERE rt.car_id = c.id
                     AND rt.status IN ('pending','confirmed')
                     AND CURDATE() BETWEEN rt.start_date AND rt.end_date
               ) AS is_available_today
        FROM cars c
        WHERE 1=1";
$params = [];

// search by name (make + model)
if ($search !== '') {
    $sql .= " AND (CONCAT(c.make, ' ', c.model) LIKE :search
              OR c.make LIKE :search
              OR c.model LIKE :search)";
    $params[':search'] = '%'.$search.'%';
}

// model
if ($model !== '') {
    $sql .= " AND c.model LIKE :model";
    $params[':model'] = '%'.$model.'%';
}

// year
if ($min_year !== '') {
    $sql .= " AND c.year >= :min_year";
    $params[':min_year'] = (int)$min_year;
}
if ($max_year !== '') {
    $sql .= " AND c.year <= :max_year";
    $params[':max_year'] = (int)$max_year;
}

// seats
if ($min_seats !== '') {
    $sql .= " AND c.seats >= :min_seats";
    $params[':min_seats'] = (int)$min_seats;
}

// luggage
if ($min_luggage !== '') {
    $sql .= " AND c.luggage_capacity >= :min_luggage";
    $params[':min_luggage'] = (int)$min_luggage;
}

// transmission
if ($transmission !== '') {
    $sql .= " AND c.transmission = :transmission";
    $params[':transmission'] = $transmission;
}

// max daily rate
if ($max_rate !== '') {
    $sql .= " AND c.daily_rate <= :max_rate";
    $params[':max_rate'] = (float)$max_rate;
}

// availability using rentals table, for selected date range
if ($start_date !== '' && $end_date !== '') {
    if ($start_date > $end_date) {
        $tmp        = $start_date;
        $start_date = $end_date;
        $end_date   = $tmp;
    }

    $sql .= " AND NOT EXISTS (
                SELECT 1
                FROM rentals r
                WHERE r.car_id = c.id
                  AND r.status IN ('pending','confirmed')
                  AND NOT (
                        r.end_date   <= :start_date
                        OR r.start_date >= :end_date
                  )
            )";

    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
}

// only generally available cars
$sql .= " AND c.available = 1
          ORDER BY c.daily_rate ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css?v=3">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <title>Browse Cars | QRS Car Rental</title>
</head>
<body>
<header>
  <a href="index.php"><h2>QRS Car Rentals</h2></a>
  <nav>
    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="browse.php">Browse</a></li>
        <li><a href="my_bookings.php">My Bookings</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
        <li><a href="login.html">Login</a></li>
        <li><a href="signup.html">Sign Up</a></li>
        <?php endif; ?>
    </ul>
  </nav>
</header>

<main class="browse-main">

    <!-- Filter / search form -->
    <form method="get" class="filter-form">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by car name (e.g. Honda Civic)"
            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
        >

        <input 
            type="text" 
            name="model" 
            placeholder="Model (e.g. Civic)"
            value="<?php echo htmlspecialchars($_GET['model'] ?? ''); ?>"
        >

        <input 
            type="number" 
            name="min_year" 
            placeholder="Min year"
            value="<?php echo htmlspecialchars($_GET['min_year'] ?? ''); ?>"
        >
        <input 
            type="number" 
            name="max_year" 
            placeholder="Max year"
            value="<?php echo htmlspecialchars($_GET['max_year'] ?? ''); ?>"
        >

        <input 
            type="number" 
            name="min_seats" 
            placeholder="Min seats"
            value="<?php echo htmlspecialchars($_GET['min_seats'] ?? ''); ?>"
        >

        <input 
            type="number" 
            name="min_luggage" 
            placeholder="Min luggage"
            value="<?php echo htmlspecialchars($_GET['min_luggage'] ?? ''); ?>"
        >

        <select name="transmission">
            <option value="">Any transmission</option>
            <option value="Automatic" <?php echo (($_GET['transmission'] ?? '') === 'Automatic') ? 'selected' : ''; ?>>Automatic</option>
            <option value="Manual" <?php echo (($_GET['transmission'] ?? '') === 'Manual') ? 'selected' : ''; ?>>Manual</option>
        </select>

        <input 
            type="number" 
            step="0.01"
            name="max_rate" 
            placeholder="Max daily rate"
            value="<?php echo htmlspecialchars($_GET['max_rate'] ?? ''); ?>"
        >

        <label>
            From:
            <input 
                type="date" 
                name="start_date" 
                value="<?php echo htmlspecialchars($_GET['start_date'] ?? ''); ?>"
            >
        </label>
        <label>
            To:
            <input 
                type="date" 
                name="end_date" 
                value="<?php echo htmlspecialchars($_GET['end_date'] ?? ''); ?>"
            >
        </label>

        <button type="submit">Search &amp; Filter</button>
    </form>

    <?php if (empty($cars)): ?>
        <p style="padding:1rem">No cars match your filters. Try changing your search or dates.</p>
    <?php else: ?>
        <?php foreach ($cars as $car): 
            $title    = htmlspecialchars($car['make'].' '.$car['model']);
            $subtitle = htmlspecialchars($car['year'].' | Sedan');
            $rate     = number_format($car['daily_rate'], 2);
            $img      = !empty($car['image_url']) ? $car['image_url'] : "image/corolla.avif";
        ?>
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

            <?php
            // Button logic:
            // - If not available today       -> "Not Available Today"
            // - Else if not logged in        -> "Login to Rent"
            // - Else                         -> "Rent Now"
            ?>
            <?php if (!$car['is_available_today']): ?>
                <button type="button" disabled>Not Available Today</button>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                <a href="login.html"><button type="button">Login to Rent</button></a>
            <?php else: ?>
                <a href="rent.php?car_id=<?= $car['id'] ?>"><button type="button">Rent Now</button></a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

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
