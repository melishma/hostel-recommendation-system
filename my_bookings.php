<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
$user_id = $_SESSION["user_id"];


$stmt = $conn->prepare("SELECT bookings.id, hostels.name AS hostel_name, bookings.student_name, bookings.student_email, bookings.student_phone, bookings.duration, bookings.booking_date, bookings.status FROM bookings INNER JOIN hostels ON bookings.hostel_id = hostels.id WHERE bookings.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f6fc;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #6b3c89;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #6b3c89;
            color: white;
        }
        .cancel-btn {
            background-color: #e74c3c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>My Bookings</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Hostel</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Duration (days)</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['hostel_name']) ?></td>
                            <td><?= htmlspecialchars($booking['student_name']) ?></td>
                            <td><?= htmlspecialchars($booking['student_email']) ?></td>
                            <td><?= htmlspecialchars($booking['student_phone']) ?></td>
                            <td><?= htmlspecialchars($booking['duration']) ?></td>
                            <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                            <td>
                                <?php 
                                $status = $booking['status'];
                                if ($status === 'approved') {
                                    echo '<span style="color:green; font-weight:bold;">Approved</span>';
                                } elseif ($status === 'rejected') {
                                    echo '<span style="color:red; font-weight:bold;">Rejected</span>';
                                } else {
                                    echo '<span style="color:orange; font-weight:bold;">Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                
                                <form action="cancel_booking.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <button type="submit" class="cancel-btn">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no bookings.</p>
        <?php endif; ?>
        <br>
        <a href="hostels.php"> Back to Hostels</a>
    </div>
</body>
</html>
