<?php
session_start();


require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = strtolower(trim($_POST['email'] ?? ''));
    $password   = $_POST['password'] ?? '';

    
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        die('All fields are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format.');
    }

    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    
    try {
        
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash)
                VALUES (:first_name, :last_name, :email, :password_hash)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':first_name'    => $first_name,
            ':last_name'     => $last_name,
            ':email'         => $email,
            ':password_hash' => $password_hash,
        ]);

        
        header('Location: login.html?signup=success');
        exit;
    } catch (PDOException $e) {
        
        if ($e->getCode() === '23000') {
            die('This email is already registered. <a href="login.html">Login here</a>.');
        }
        
        die('Something went wrong while creating your account.');
    }
}
