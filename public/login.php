<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        die('Email and password are required.');
    }

    try {
        
        $sql = "SELECT id, first_name, last_name, password_hash 
                FROM users 
                WHERE email = :email";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name']  = $user['last_name'];
            $_SESSION['full_name']  = $user['first_name'] . ' ' . $user['last_name'];

            
            header('Location: browse.php');
            exit;
        }
        else {
            echo 'Invalid email or password.';
        }

    } catch (PDOException $e) {
        die('Something went wrong while logging you in.');
    }
}
?>

