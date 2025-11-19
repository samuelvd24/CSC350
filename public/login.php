<?php
// login.php
session_start();
require __DIR__ . '/db.php'; // this file defines $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        die('Email and password are required.');
    }

    try {
        // If your users table has first_name/last_name:
        // $sql = "SELECT id, first_name, last_name, password_hash FROM users WHERE email = :email";

        // If you have NOT added first_name/last_name columns yet, use this instead:
        $sql = "SELECT id, password_hash FROM users WHERE email = :email";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login OK
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];

            // Only set these if those columns exist
            // $_SESSION['first_name'] = $user['first_name'];
            // $_SESSION['last_name']  = $user['last_name'];

            header('Location: browse.php'); // or wherever you want after login
            exit;
        } else {
            echo 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        // For debugging you can temporarily uncomment this:
        // echo $e->getMessage();
        die('Something went wrong while logging you in.');
    }
}
