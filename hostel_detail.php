<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$user_id = $_SESSION["user_id"]; // Get logged in user ID

$hostel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hostel_id <= 0) {
    echo "<p>Invalid hostel ID.</p>";
    exit;
}

// Fetch hostel info
$stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Invalid hostel ID. Sorry, the hostel you're looking for is not available.</p>";
    exit;
}

$hostel = $result->fetch_assoc();

// Fetch hostel images
$image_stmt = $conn->prepare("SELECT image_path FROM hostel_images WHERE hostel_id = ?");
$image_stmt->bind_param("i", $hostel_id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();

// Check if user has booked this hostel
$booking_stmt = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND hostel_id = ?");
$booking_stmt->bind_param("ii", $user_id, $hostel_id);
$booking_stmt->execute();
$has_booking = $booking_stmt->get_result()->num_rows > 0;

// Handle rating submission
//if ($_SERVER['REQUEST_METHOD'] === 'POST' && $has_booking) {
    //$rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? round(floatval($_POST['rating']), 1) : null;
    //$review = trim($_POST['review']);

    //$check_stmt = $conn->prepare("SELECT id, rating FROM hostel_reviews WHERE user_id = ? AND hostel_id = ?");
    //$check_stmt->bind_param("ii", $user_id, $hostel_id);
    //$check_stmt->execute();
    //$existing = $check_stmt->get_result()->fetch_assoc();

    //if ($existing) {
        //$update_stmt = $conn->prepare("UPDATE hostel_reviews SET rating = ?, review = ?, created_at = NOW() WHERE id = ?");
        //$update_stmt->bind_param("dsi", $rating, $review, $existing['id']);
        //$update_stmt->execute();
    //} else {
        //$insert_stmt = $conn->prepare("INSERT INTO hostel_reviews (user_id, hostel_id, rating, review) VALUES (?, ?, ?, ?)");
        //$insert_stmt->bind_param("iiis", $user_id, $hostel_id, $rating, $review);
        //$insert_stmt->execute();
   // }
//}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($hostel['name']) ?> Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f6fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color:rgb(85, 3, 110);
        }
        .hostel-images {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .hostel-images img {
            height: 250px;
            border-radius: 10px;
            object-fit: cover;
        }
        p {
            font-size: 16px;
            color: #333;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background: #6b3c89;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
        }
        a:hover {
            background: #532c6f;
        }
        .book-button {
            background: #28a745;
            margin-left: 10px;
        }
        .book-button:hover {
            background: #218838;
        }
      
        .student-availability-box {
            background: #fefefe;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            max-width: 360px;
            font-family: 'Segoe UI', sans-serif;
        }

        .student-availability-box h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .student-fields {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .field {
            width: 100%;
        }

        .field label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }

        .field input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 14px;
        }

        .search-btn {
            background-color: #ff5a36;
            color: white;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-btn:hover {
            background-color: #e14c28;
        }

        .student-notes {
            margin-top: 15px;
            font-size: 14px;
            color: #555;
        }

        .student-notes p {
            margin: 6px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($hostel['name']) ?></h2>

    <div class="hostel-images">
        <?php if ($image_result->num_rows > 0): ?>
            <?php while ($img = $image_result->fetch_assoc()): ?>
                <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="Hostel Image">
            <?php endwhile; ?>
        <?php else: ?>
            <p>No images available for this hostel.</p>
        <?php endif; ?>
    </div>

    <p><strong>Location:</strong> <?= htmlspecialchars($hostel['location']) ?></p>
    <p><strong>Price:</strong> Rs.<?= $hostel['price'] ?></p>
    <p><strong>Capacity:</strong> <?= $hostel['capacity'] ?></p>

    <?php
    // Fetch features for this hostel
$features = [];
$feature_stmt = $conn->prepare("
    SELECT f.name
    FROM features f
    JOIN hostel_features hf ON f.id = hf.feature_id
    WHERE hf.hostel_id = ?
");
$feature_stmt->bind_param("i", $hostel_id);
$feature_stmt->execute();
$feature_result = $feature_stmt->get_result();

while ($row = $feature_result->fetch_assoc()) {
    $features[] = $row['name'];
}

$feature_stmt->close();

    ?>

    <?php if (!empty($features)): ?>
        <h3>Facilities</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin: 15px 0;">
            <?php foreach ($features as $feature): ?>
                <div style="background-color: #f0f4ff; color: #222; padding: 8px 15px; border-radius: 25px; font-size: 14px; font-weight: 500;">
                    <?= htmlspecialchars($feature) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p><strong>Facilities:</strong> Not specified</p>
    <?php endif; ?>

    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($hostel['description'])) ?></p>

    <div class="student-availability-box">
    <h2>Check Availability</h2>

    <form method="post">
        <div class="student-fields">
            <div class="field">
                <label for="check_students">Number of Students</label>
                <input type="number" id="check_students" name="check_students" value="1" min="1" required>
            </div>
        </div>

        <button class="search-btn" type="submit" name="check_availability">Search</button>
    </form>

    <?php
    if (isset($_POST['check_availability'])) {
        $requested = intval($_POST['check_students']);
        if ($requested <= 0) {
            echo "<p style='color:red;'>Invalid number of students.</p>";
        } else {
            // Get total approved bookings for this hostel
            $cap_stmt = $conn->prepare("SELECT COUNT(*) AS booked FROM bookings WHERE hostel_id = ? AND status = 'approved'");
            $cap_stmt->bind_param("i", $hostel_id);
            $cap_stmt->execute();
            $cap_result = $cap_stmt->get_result()->fetch_assoc();
            $booked = intval($cap_result['booked']);
            $available = max(0, $hostel['capacity'] - $booked);

            if ($requested <= $available) {
                echo "<p style='color:green; margin-top: 15px;'>✅ $requested student(s) can be accommodated. ($available spots available)</p>";
            } else {
                echo "<p style='color:red; margin-top: 15px;'>❌ Only $available spot(s) available. Cannot accommodate $requested student(s).</p>";
            }
        }
    }
    ?>

    <div class="student-notes">
        <p> ✔️ Student bookings</p>
        <p> ✔️ Booking takes just 2 minutes</p>
        <p> ✔️ Admin Approval Required</p>
    </div>
</div>


    <!-- Book Hostel Button -->
    <a href="hostels.php">Back to Hostels</a>
    <a href="book.php?hostel_id=<?= $hostel['id'] ?>" class="book-button" style="text-decoration:none; padding: 10px 20px; background:#28a745; color:#fff; border-radius:6px;">
    Book Now
</a>


    <?php
    $reviews_stmt = $conn->prepare("SELECT r.rating, r.review, r.created_at, u.name FROM hostel_reviews r JOIN users u ON r.user_id = u.id WHERE r.hostel_id = ? ORDER BY r.created_at DESC");
    $reviews_stmt->bind_param("i", $hostel_id);
    $reviews_stmt->execute();
    $reviews = $reviews_stmt->get_result();
    ?>

    <?php if ($has_booking): ?>
        <h3>Submit Your Review</h3>
        <form method="post">
            <label for="rating">Rating (optional):</label><br>
            <select name="rating" id="rating">
                <option value="">-- No Rating --</option>
                <?php 
                // Show preselected rating if user already reviewed
                $user_rating = null;
                if (isset($existing['rating'])) {
                    $user_rating = $existing['rating'];
                }
                for ($i = 0.5; $i <= 5.0; $i += 0.5): 
                    $i_rounded = round($i, 1);
                    $selected = ($user_rating == $i_rounded) ? 'selected' : '';
                ?>
                    <option value="<?= $i_rounded ?>" <?= $selected ?>><?= $i_rounded ?></option>
                <?php endfor; ?>
            </select><br><br>
            <label for="review">Your Review:</label><br>
            <textarea name="review" id="review" rows="4" cols="50" placeholder="Write something..."><?= isset($existing['review']) ? htmlspecialchars($existing['review']) : '' ?></textarea><br><br>
            <button type="submit">Submit Review</button>
        </form>
    <?php else: ?>
        <p style="margin-top: 30px; font-style: italic; color: #555;">
            You need to book this hostel before submitting a review.
        </p>
    <?php endif; ?>

    <h3>User Reviews</h3>
    <?php if ($reviews->num_rows > 0): ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div style="border-bottom: 1px solid #ddd; margin-bottom: 15px; padding-bottom: 10px;">
                <p><strong><?= htmlspecialchars($review['name']) ?></strong> <em>(<?= htmlspecialchars($review['created_at']) ?>)</em></p>
                <p>Rating: <?= $review['rating'] ? htmlspecialchars($review['rating']) : 'No rating' ?></p>
                <p><?= nl2br(htmlspecialchars($review['review'])) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>

</div>
</body>
</html>
