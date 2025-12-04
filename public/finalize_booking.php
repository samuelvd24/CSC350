<?php
session_start();
require "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: browse.php");
    exit;
}

$user_id    = $_SESSION["user_id"];
$car_id     = (int) $_POST["car_id"];
$start_date = $_POST["start_date"];
$end_date   = $_POST["end_date"];


$stmt = $pdo->prepare("
    INSERT INTO rentals (user_id, car_id, start_date, end_date, status)
    VALUES (:uid, :car_id, :start_date, :end_date, 'pending')
");

$stmt->execute([
    ":uid"        => $user_id,
    ":car_id"     => $car_id,
    ":start_date" => $start_date,
    ":end_date"   => $end_date
]);

header("Location: my_bookings.php?success=booking_confirmed");
exit;
