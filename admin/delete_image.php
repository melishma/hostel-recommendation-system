<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}

$admin_role = $_SESSION["admin_role"];
$admin_hostel_id = $_SESSION["assigned_hostel_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $image_id = intval($_POST['image_id']);
    $hostel_id = intval($_POST['hostel_id']);

 
    if ($admin_role !== 'super_admin') {
        $stmt = $conn->prepare("SELECT hostel_id FROM hostel_images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $img = $result->fetch_assoc();

        if (!$img || $img['hostel_id'] != $admin_hostel_id) {
            die("Unauthorized access.");
        }
    }

    $stmt = $conn->prepare("SELECT image_path FROM hostel_images WHERE id = ?");
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $image_path = "../" . $row['image_path'];
        if (file_exists($image_path)) {
            unlink($image_path); 
        }
        $del = $conn->prepare("DELETE FROM hostel_images WHERE id = ?");
        $del->bind_param("i", $image_id);
        $del->execute();
    }

    header("Location: edit_hostel.php?id=" . $hostel_id);
    exit;
}
?>
