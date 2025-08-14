<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}

$admin_role = $_SESSION["admin_role"];
$admin_hostel_id = $_SESSION["assigned_hostel_id"];
$error = '';



if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'], $_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];

    $stmt = $conn->prepare("SELECT hostel_id FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if (!$booking) {
        $error = "Invalid booking ID.";
    } else {
        $hostel_id = $booking['hostel_id'];

        if ($admin_role !== 'super_admin' && $admin_hostel_id != $hostel_id) {
            $error = "Unauthorized action.";
        } else {
            if ($action === 'approve') {
                $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM bookings WHERE hostel_id = ? AND status = 'approved'");
                $count_stmt->bind_param("i", $hostel_id);
                $count_stmt->execute();
                $count = $count_stmt->get_result()->fetch_assoc()['total'];

                $cap_stmt = $conn->prepare("SELECT capacity FROM hostels WHERE id = ?");
                $cap_stmt->bind_param("i", $hostel_id);
                $cap_stmt->execute();
                $capacity = $cap_stmt->get_result()->fetch_assoc()['capacity'];

                if ($count < $capacity) {
                    $update = $conn->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?");
                    $update->bind_param("i", $booking_id);
                    $update->execute();
                } else {
                    $error = "Hostel is already full.";
                }
            } elseif ($action === 'reject') {
                $update = $conn->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?");
                $update->bind_param("i", $booking_id);
                $update->execute();
            }
        }
    }
}


$sql = "SELECT b.id, u.name AS student_name, u.email, h.id AS hostel_id, h.name AS hostel_name, h.capacity, b.booking_date, b.status
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN hostels h ON b.hostel_id = h.id";

if ($admin_role !== 'super_admin') {
    $sql .= " WHERE h.id = " . intval($admin_hostel_id);
}

$sql .= " ORDER BY h.name, b.booking_date DESC";
$result = $conn->query($sql);


$capacity_sql = "SELECT h.id, h.name, h.capacity, COUNT(b.id) AS current_bookings
                 FROM hostels h
                 LEFT JOIN bookings b ON h.id = b.hostel_id AND b.status = 'approved'";

if ($admin_role !== 'super_admin') {
    $capacity_sql .= " WHERE h.id = " . intval($admin_hostel_id);
}

$capacity_sql .= " GROUP BY h.id";
$capacity_result = $conn->query($capacity_sql);

$capacity_data = [];
while ($row = $capacity_result->fetch_assoc()) {
    $remaining = $row['capacity'] - $row['current_bookings'];
    $capacity_data[$row['id']] = [
        'name' => $row['name'],
        'capacity' => $row['capacity'],
        'current' => $row['current_bookings'],
        'remaining' => $remaining
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - All Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2, h3 {
            color: #5c247a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #5c247a;
            color: #fff;
        }
        .overbooked {
            color: red;
            font-weight: bold;
        }
        .status-ok {
            color: green;
            font-weight: bold;
        }
        .btn {
            padding: 6px 10px;
            margin: 2px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .approve {
            background: #28a745;
            color: white;
        }
        .reject {
            background: #dc3545;
            color: white;
        }
        .pending {
            color: orange;
            font-weight: bold;
        }
        .approved {
            color: green;
            font-weight: bold;
        }
        .rejected {
            color: red;
            font-weight: bold;
        }
        p.error {
            color: red;
            font-weight: bold;
            margin-top: 15px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #5c247a;
            font-size: small;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>All Hostel Bookings</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Student</th>
            <th>Email</th>
            <th>Hostel</th>
            <th>Booking Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['hostel_name']) ?>
                        <?php
                        $hostel_id = (int)$row['hostel_id'];
                        if (isset($capacity_data[$hostel_id])) {
                            $remaining = $capacity_data[$hostel_id]['remaining'];
                            echo $remaining <= 0
                                ? " <span class='overbooked'>(Full)</span>"
                                : " <span class='status-ok'>(Available: {$remaining})</span>";
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['booking_date']) ?></td>
                    <td class="<?= htmlspecialchars($row['status']) ?>"><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?= (int)$row['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                            </form>
                        <?php else: ?>
                            <em>N/A</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No bookings found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    
    <h3 style="margin-top:40px;">Hostel Booking Summary</h3>
    <table>
        <thead>
        <tr>
            <th>Hostel</th>
            <th>Capacity</th>
            <th>Approved Bookings</th>
            <th>Remaining Slots</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($capacity_data as $data): ?>
            <tr>
                <td><?= htmlspecialchars($data['name']) ?></td>
                <td><?= (int)$data['capacity'] ?></td>
                <td><?= (int)$data['current'] ?></td>
                <td><?= (int)$data['remaining'] ?></td>
                <td>
                    <?= $data['remaining'] <= 0
                        ? "<span class='overbooked'>Full</span>"
                        : "<span class='status-ok'>Available</span>" ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php"> Back to Dashboard</a>
</div>
</body>
</html>
