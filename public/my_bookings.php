<?php
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=login_required');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_rental_id'])) {
    $rental_id = (int)$_POST['cancel_rental_id'];

    $check = $pdo->prepare("SELECT status FROM rentals WHERE id = ? AND user_id = ?");
    $check->execute([$rental_id, $user_id]);
    $r = $check->fetch();

    if (!$r) {
        $message = "Booking not found.";
    } elseif (!in_array($r['status'], ['pending','confirmed'])) {
        $message = "This booking cannot be canceled.";
    } else {
        $upd = $pdo->prepare("UPDATE rentals SET status = 'canceled' WHERE id = ? AND user_id = ?");
        $upd->execute([$rental_id, $user_id]);
        $message = "Booking canceled.";
    }
}

$sql = "
    SELECT r.id AS rental_id, r.start_date, r.end_date, r.status, r.created_at,
           c.id AS car_id, c.make, c.model, c.year, c.daily_rate, c.image_url
    FROM rentals r
    JOIN cars c ON c.id = r.car_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Bookings - QRS Car Rentals</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="style.css">
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

<main class="browse-main" style="width:90%; max-width:900px; margin:2rem auto;">
  <h1 style="text-align:center; margin-bottom:1rem;">My Bookings</h1>

  <?php if ($message): ?>
    <p style="color:green; text-align:center;"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <?php if (empty($bookings)): ?>
    <p style="padding:1rem; text-align:center;">You have no bookings yet.</p>
  <?php else: ?>
    <?php foreach ($bookings as $b):
        $car_title = htmlspecialchars($b['make'].' '.$b['model']);
        $dates = htmlspecialchars($b['start_date']) . ' → ' . htmlspecialchars($b['end_date']);
        $status = htmlspecialchars(ucfirst($b['status']));
        $rate = number_format($b['daily_rate'], 2);
        $img = !empty($b['image_url']) ? $b['image_url'] : 'image/corolla.avif';

        $days = (strtotime($b['end_date']) - strtotime($b['start_date'])) / (60*60*24);
        $total_price = $days * $b['daily_rate'];
        $total_price_formatted = number_format($total_price, 2);
    ?>
      <div class="card gradient" style="margin-bottom:1rem;">
        <div style="display:flex; gap:1rem; align-items:center;">
          <div style="flex:0 0 140px;">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= $car_title ?>" style="width:120px; border-radius:8px;">
          </div>

          <div style="flex:1; text-align:left;">
            <h3 style="margin:0 0 .25rem 0; color:#fff;"><?= $car_title ?> <small style="color:#ffd66b; font-weight:600;">$<?= $rate ?>/day</small></h3>
            <p style="margin:0 0 .25rem 0; color:#ddd;"><?= $dates ?></p>
            <p style="margin:0 0 .25rem 0;"><strong>Total Price:</strong> $<?= $total_price_formatted ?></p>
            <p style="margin:0 0 .5rem 0;"><strong>Status:</strong> <?= $status ?></p>
            <p style="margin:0; color:#bbb; font-size:.9rem;">Booked on <?= htmlspecialchars(date('Y-m-d', strtotime($b['created_at']))) ?></p>
          </div>

          <div style="flex:0 0 160px; text-align:right;">
            <a href="rent.php?car_id=<?= (int)$b['car_id'] ?>"><button style="margin-bottom:.5rem;">View Car</button></a>
            <?php if (in_array($b['status'], ['pending','confirmed'])): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="cancel_rental_id" value="<?= (int)$b['rental_id'] ?>">
                <button type="submit" style="background:#ff7f50; color:#fff; border:none; padding:.5rem .75rem; border-radius:6px; cursor:pointer;">Cancel</button>
              </form>
            <?php else: ?>
              <button disabled style="background:#555; color:#ccc; border:none; padding:.5rem .75rem; border-radius:6px;">Cannot cancel</button>
            <?php endif; ?>
          </div>
        </div>
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

