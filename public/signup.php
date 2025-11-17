<?php
session_start();

// make sure this path is correct
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = strtolower(trim($_POST['email'] ?? ''));
    $password   = $_POST['password'] ?? '';

    // 2. Basic validation
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        die('All fields are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format.');
    }

    // 3. Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 4. Insert into database using PDO ($pdo)
    try {
        // ❗ If your `users` table does NOT yet have first_name/last_name,
        // run the ALTER TABLE we talked about, OR remove them from this query.
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash)
                VALUES (:first_name, :last_name, :email, :password_hash)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':first_name'    => $first_name,
            ':last_name'     => $last_name,
            ':email'         => $email,
            ':password_hash' => $password_hash,
        ]);

        // On success, send user to login page
        header('Location: login.html?signup=success');
        exit;
    } catch (PDOException $e) {
        // Duplicate email (unique constraint)
        if ($e->getCode() === '23000') {
            die('This email is already registered. <a href="login.html">Login here</a>.');
        }
        // For debugging you can temporarily echo the error:
        // echo $e->getMessage();
        die('Something went wrong while creating your account.');
    }
}
