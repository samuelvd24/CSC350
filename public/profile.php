<?php
session_start();
require __DIR__ . '/db.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user info
$stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (!$first_name || !$last_name || !$email) {
        $error = "First name, Last name, and Email are required.";
    } else {
        try {
            if ($password !== '') {
                // Hash password before storing
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $upd = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, email = ?, password_hash = ?
                    WHERE id = ?
                ");
                $upd->execute([$first_name, $last_name, $email, $password_hash, $user_id]);
            } else {
                // Update only names/email if password is empty
                $upd = $pdo->prepare("
                    UPDATE users 
                    SET first_name = ?, last_name = ?, email = ?
                    WHERE id = ?
                ");
                $upd->execute([$first_name, $last_name, $email, $user_id]);
            }

            $message = "Profile updated successfully!";
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - QRS Car Rentals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 500px;
            margin: 2rem auto;
            background: #111;
            padding: 2rem;
            border-radius: 10px;
            color: #fff;
        }
        .profile-container label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: bold;
            color: #fff;
        }
        .profile-container input {
            width: 100%;
            padding: 0.6rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        .profile-container button {
            padding: 0.75rem 1.2rem;
            background: #ffc300;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .profile-container button:hover {
            background: #e0a800;
        }
        .message {
            margin-bottom: 1rem;
            font-weight: bold;
            color: #0f0;
        }
        .error {
            margin-bottom: 1rem;
            font-weight: bold;
            color: #f33;
        }
    </style>
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

<main>
    <div class="profile-container">
        <h1>My Profile</h1>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? $user['first_name']) ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? $user['last_name']) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>

            <label for="password">Password <small>(leave blank to keep current)</small></label>
            <input type="password" name="password" id="password">

            <button type="submit">Update</button>
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

