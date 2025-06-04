<?php
session_start();
require_once 'config.php';

if (!isset($_GET['hostel_id']) || !is_numeric($_GET['hostel_id'])) {
    $error_message = "Invalid hostel ID. Sorry, the hostel you're looking for is not available.";
} else {
    $hostel_id = intval($_GET['hostel_id']);

    // Check if hostel exists (only once)
    $stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
    $stmt->bind_param("i", $hostel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error_message = "Invalid hostel ID. Sorry, the hostel you're looking for is not available.";
    } else {
        $hostel = $result->fetch_assoc();

        // Make sure user is logged in
        if (!isset($_SESSION["user_id"])) {
            $error_message = "You must be logged in to book a hostel.";
        } else {
            $user_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
            $user_stmt->bind_param("i", $_SESSION["user_id"]);
            $user_stmt->execute();
            $user = $user_stmt->get_result()->fetch_assoc();
            $student_name = $user['name'];
            $student_email = $user['email'];

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $student_phone = $_POST['student_phone'];
                $duration = !empty($_POST['duration']) ? intval($_POST['duration']) : null;

                $check = $conn->prepare("SELECT * FROM bookings WHERE hostel_id = ? AND user_id = ?");
                $check->bind_param("ii", $hostel_id, $_SESSION["user_id"]);
                $check->execute();
                $existing = $check->get_result();

                if ($existing->num_rows > 0) {
                    $info_message = "You have already requested a booking for this hostel. Please wait for admin approval.";
                } else {
                    // Check approved bookings count
                    $count_stmt = $conn->prepare("SELECT COUNT(*) as approved_count FROM bookings WHERE hostel_id = ? AND status = 'approved'");
                    $count_stmt->bind_param("i", $hostel_id);
                    $count_stmt->execute();
                    $count_stmt->bind_result($approved_count);
                    $count_stmt->fetch();
                    $count_stmt->close();

                    if ($approved_count >= $hostel['capacity']) {
                        $info_message = "Sorry, this hostel is fully booked. You cannot request a booking at this time.";
                    } else {
                        $stmt = $conn->prepare("INSERT INTO bookings (hostel_id, student_name, student_email, student_phone, duration, booking_date, user_id, status) VALUES (?, ?, ?, ?, ?, NOW(), ?, 'pending')");
                        $stmt->bind_param("issssi", $hostel_id, $student_name, $student_email, $student_phone, $duration, $_SESSION["user_id"]);

                        if ($stmt->execute()) {
                            $success_message = "Booking request submitted successfully! Please wait for admin approval.";
                        } else {
                            $error_message = "Error: " . $stmt->error;
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Hostel</title>
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
        input, textarea, button {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            background: #6b3c89;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background: #532c6f;
        }
        .message {
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
            font-size: 16px;
            line-height: 1.4;
        }
        .message.error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .message.info {
            background-color: #cff4fc;
            color: #055160;
            border: 1px solid #b6effb;
        }
        .message.success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .message.success svg {
            width: 48px;
            height: 48px;
        }
        .back-link {
            background-color: #0f5132;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #0a3625;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <a href="hostels.php" class="back-link">&larr; Back to Hostels</a>
        <?php elseif (!empty($info_message)): ?>
            <div class="message info"><?= htmlspecialchars($info_message) ?></div>
            <a href="hostels.php" class="back-link">&larr; Back to Hostels</a>
        <?php elseif (!empty($success_message)): ?>
            <div class="message success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#0f5132" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM7 10.793l5.146-5.147-.708-.708L7 9.379 4.854 7.232l-.708.707L7 10.793z"/>
                </svg>
                <p><strong><?= htmlspecialchars($success_message) ?></strong></p>
                <a href="hostels.php" class="back-link">&larr; Back to Hostels</a>
            </div>
        <?php else: ?>
            <h2>Book Hostel: <?= htmlspecialchars($hostel['name']) ?></h2>

            <p><strong>Location:</strong> <?= htmlspecialchars($hostel['location']) ?></p>
            <p><strong>Price:</strong> Rs.<?= $hostel['price'] ?> per month</p>
            <p><strong>Your Name:</strong> <?= htmlspecialchars($student_name) ?></p>
            <p><strong>Your Email:</strong> <?= htmlspecialchars($student_email) ?></p>

            <form method="POST">
                <input type="text" name="student_phone" placeholder="Your Phone Number" required>
                <input type="number" name="duration" placeholder="Duration (days)">
                <button type="submit">Send Booking Request</button>
            </form>

            <a href="hostels.php">Back to Hostels</a>
        <?php endif; ?>
    </div>
</body>
</html>
