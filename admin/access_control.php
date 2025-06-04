<?php
session_start();

// Check login and role
if (!isset($_SESSION['admin_logged_in']) || !isset($_SESSION['admin_role'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_role = $_SESSION['admin_role'];
$hostel_id = $_SESSION['assigned_hostel_id'] ?? null;

// Role helpers
function isSuperAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin';
}

function isHostelAdmin() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hostel_admin';
}
?>
