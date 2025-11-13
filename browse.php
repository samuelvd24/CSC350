<?php require __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

  <title>QRS Car Rental</title>
</head>
<body>
<header>
  <a href="/index.html"><h2>QRS Car Rentals</h2></a>
  <nav>
    <ul>
      <li><a href="browse.php">Browse</a></li>
      <li><a href="signup.html">Sign Up</a></li>
    </ul>
  </nav>
</header>

<main class="browse-main">
<?php
// Fetch available cars
$sql = "SELECT id, make, model, year, seats, luggage_capacity, transmission,
               daily_rate, available, image_url
        FROM cars
        WHERE available = 1
        ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$cars = $pdo->query($sql)->fetchAll();

// Helper to build an image path (you can replace with a real column later)
function carImagePath($make, $model) {
  $filename = strtolower(preg_replace('/\s+/', '-', $make . '-' . $model)) . ".avif";
  $path = "./image/" . $filename;
  return file_exists($path) ? $path : ".image/corolla.avif"; // fallback
}

if (!$cars) {
  echo "<p style='padding:1rem'>No cars available right now. Please check back later.</p>";
}

foreach ($cars as $car):
    $title = htmlspecialchars($car['make'].' '.$car['model']);
    $subtitle = htmlspecialchars($car['year'].' | Sedan'); // keep or replace when you add body_style
    $rate = number_format($car['daily_rate'], 2);
    $img  = $car['image_url']; // already defaulted by SQL

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
  </div>
<?php endforeach; ?>
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
