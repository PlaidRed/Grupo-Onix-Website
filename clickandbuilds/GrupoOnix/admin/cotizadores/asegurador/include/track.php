<?php
// track.php
include '../../.../include/Database.php'; // adjust to your db connection

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = $_SESSION['user_id'] ?? null;
    $timestamp = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO tracking_logs (user_id, action, timestamp) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $action, $timestamp);
    $stmt->execute();
    $stmt->close();
}