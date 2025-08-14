<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$user_id = $_SESSION["user_id"];
$booking_id = $_POST['booking_id'] ?? 0;


$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if ($booking) {
    
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        header("Location: my_bookings.php");
        exit;
    } else {
        echo "Error: Could not cancel the booking.";
    }
} else {
    echo "Invalid booking.";
}
?>
