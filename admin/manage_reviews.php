<?php
session_start();
$admin_role = $_SESSION["admin_role"];
$admin_hostel_id = $_SESSION["assigned_hostel_id"];

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}
require_once '../config.php';

// Delete review if requested
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM hostel_reviews WHERE id = $delete_id");
    header("Location: manage_reviews.php");
    exit;
}

// Fetch all reviews
$sql = "SELECT r.id, r.rating, r.review, r.created_at, u.name AS student_name, h.name AS hostel_name
        FROM hostel_reviews r
        JOIN users u ON r.user_id = u.id
        JOIN hostels h ON r.hostel_id = h.id
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reviews</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f1f7;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #6a2c91;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        table th {
            background-color: #6a2c91;
            color: white;
        }
        .delete-btn {
            color: white;
            background-color: #c0392b;
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .delete-btn:hover {
            background-color: #a93226;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Hostel Reviews</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Hostel</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['hostel_name']) ?></td>
                        <td><?= number_format($row['rating'], 1) ?>/5</td>
                        <td><?= nl2br(htmlspecialchars($row['review'])) ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td><a class="delete-btn" href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No reviews found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <br>
    <a href="dashboard.php"> Back to Dashboard</a>
</div>
</body>
</html>
