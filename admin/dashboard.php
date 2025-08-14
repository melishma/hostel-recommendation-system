<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}


$admin_name = $_SESSION["admin_name"] ?? "Admin";
$admin_role = $_SESSION["admin_role"] ?? "admin";
$admin_hostel_id = $_SESSION["assigned_hostel_id"] ?? null;

require_once '../config.php';

if ($admin_role === 'super_admin') {
    $hostel_count = $conn->query("SELECT COUNT(*) as count FROM hostels")->fetch_assoc()['count'];
} elseif ($admin_hostel_id !== null) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM hostels WHERE id = ?");
    $stmt->bind_param("i", $admin_hostel_id);
    $stmt->execute();
    $hostel_count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
} else {
    $hostel_count = 0;
}


if ($admin_role === 'super_admin') {
   
    $user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
} elseif ($admin_hostel_id !== null) {
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT u.id) AS count
        FROM users u
        JOIN bookings b ON u.id = b.user_id
        WHERE b.hostel_id = ?");
    $stmt->bind_param("i", $admin_hostel_id);
    $stmt->execute();
    $user_count = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
} else {
    $user_count = 0;
}


$admin_hostel_name = null;
if ($admin_role !== 'super_admin' && $admin_hostel_id) {
    $stmt = $conn->prepare("SELECT name FROM hostels WHERE id = ?");
    $stmt->bind_param("i", $admin_hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_hostel_name = $result->fetch_assoc()['name'] ?? 'Unknown Hostel';
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom, #f3f4f8, #e6e8f2);
            margin: 0;
            padding: 20px;
            text-align: center;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .navbar {
            background: #fff;
            display: flex; 
            justify-content: space-between;
            align-items: center; 
            padding: 15px 5%;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo { 
            font-size: 24px; 
            font-weight: bold;
            color: #6a2c91;
        }
        .nav-links { 
            display: flex; 
            align-items: center;
        }
        .nav-links a {
            margin-left: 20px;
            color: #6a2c91;
            text-decoration: none; 
            font-weight: bold;
        }
        .profile-icon {
            position: relative; 
            cursor: pointer;
            margin-left: 20px;
        }
        .profile-dropdown {
            display: none; 
            position: absolute;
            top: 35px; 
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            min-width: 200px; 
            border-radius: 5px;
            z-index: 1;
        }
        .profile-dropdown a {
            color: #6a2c91;
            padding: 12px; 
            text-decoration: none; 
            display: block;
        }
        .profile-dropdown a:hover { 
            background-color: #f1f1f1; 
        }
        .profile-icon:hover .profile-dropdown { 
            display: block; 
        }

        .hero {
            position: relative;
            background: url('https://i.pinimg.com/736x/76/01/76/760176f16bc3d87298cfa76cb4383efc.jpg') center/cover no-repeat;
            height: 500px;
            display: flex;
            justify-content: center; 
            align-items: center;
        }
        .hero-overlay {
            background: rgba(105, 58, 117, 0.7);
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            color: white;
        }
        .hero-overlay h1 { 
            font-size: 2.8em; 
            margin-bottom: 10px; 
        }
        .hero-overlay p { 
            font-size: 1.2em;
            margin-bottom: 20px; 
        }

        header {
            display: inline-block;
            background: #6a2c91;
            padding: 10px 20px; 
            color: white; 
            text-align: center;
            border-radius: 10px;
            margin: 20px auto;
        }
        header h1 { 
            margin: 0; 
            font-size: 26px; 
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 1px; 
        }
        header p {
            font-size: 18px;
            margin-top: 10px;
        }

        .dashboard {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .stats {
            display: flex; 
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .card {
            background: #fff;
            padding: 30px; 
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex: 1; 
            margin: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card h3 {
            font-size: 24px;
            color: #6a2c91;
            margin-bottom: 15px; 
        }
        .card p { 
            font-size: 22px;
            margin: 0; 
            color: #444; 
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .card a {
            display: inline-block;
            margin-top: 20px;
            background-color: #6a2c91;
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .card a:hover {
            background-color: #571e78; 
        }
        footer {
            margin-top: 40px; 
            text-align: center;
        }
        footer a {
            color: #6a2c91; 
            text-decoration: none;
            font-size: 18px;
        }
        footer a:hover { 
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .stats { flex-direction: column; }
            .card { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">HostelNow Admin</div>
            <div class="nav-links">
                <a href="admin_bookings.php">Bookings</a>
                <a href="manage_hostels.php">Manage Hostels</a>
                <a href="../logout.php">Logout</a>
                <?php if ($admin_role === 'super_admin'): ?>
                    <a href="manage_users.php">Manage Users</a>
                    <a href="assign_admins.php">Assign Admins</a>
                <?php endif; ?>
                <div class="profile-icon">
                    <i class="fa fa-user-circle" style="font-size: 30px;"></i>
                    <div class="profile-dropdown">
                        <a href="#">Admin: <?= htmlspecialchars($admin_name) ?></a>
                        <a href="../logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <section class="hero">
            <div class="hero-overlay">
                <h1>Welcome to the Admin Dashboard</h1>
                <p>Manage all hostels and user activities in one place.</p>
            </div>
        </section>

        <header>
            <h1>Welcome, <?= htmlspecialchars($admin_name) ?></h1>
            <?php if ($admin_hostel_name): ?>
                <p>Assigned Hostel: <?= htmlspecialchars($admin_hostel_name) ?></p>
            <?php endif; ?>
        </header>

        <div class="dashboard">
            <div class="stats">
                <div class="card">
                    <h3><?= $admin_role === 'super_admin' ? 'Total Hostels' : 'Your Hostel' ?></h3>
                    <p><?= $hostel_count ?></p>
                </div>
                <div class="card">
                    <h3>Total Users<?= $admin_role !== 'super_admin' ? ' (Bookings)' : '' ?></h3>
                    <p><?= $user_count ?></p>
                </div>
            </div>

            <div class="card">
                <a href="manage_hostels.php">Manage Hostels</a>
                <?php if ($admin_role === 'super_admin'): ?>
                    <a href="manage_users.php">Manage Users</a>
                    <a href="manage_reviews.php">Manage Reviews</a>
                <?php endif; ?>
            </div>
        </div>

        <footer>
            <a href="../index.php"><i class="fa fa-arrow-left"></i> Back to Homepage</a>
        </footer>
    </div>
</body>
</html>
