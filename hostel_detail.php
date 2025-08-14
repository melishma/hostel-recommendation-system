<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$user_id = $_SESSION["user_id"]; 

$hostel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($hostel_id <= 0) {
    echo "<p>Invalid hostel ID.</p>";
    exit;
}


$stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->bind_param("i", $hostel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Invalid hostel ID. Sorry, the hostel you're looking for is not available.</p>";
    exit;
}

$hostel = $result->fetch_assoc();


$image_stmt = $conn->prepare("SELECT image_path FROM hostel_images WHERE hostel_id = ?");
$image_stmt->bind_param("i", $hostel_id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();


$booking_stmt = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND hostel_id = ?");
$booking_stmt->bind_param("ii", $user_id, $hostel_id);
$booking_stmt->execute();
$has_booking = $booking_stmt->get_result()->num_rows > 0;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $has_booking) {
    $rating = (isset($_POST['rating']) && $_POST['rating'] !== '') ? round(floatval($_POST['rating']), 1) : 0; 
    $review = isset($_POST['review']) ? trim($_POST['review']) : ''; 

    $check_stmt = $conn->prepare("SELECT id, rating, review FROM hostel_reviews WHERE user_id = ? AND hostel_id = ?");
    $check_stmt->bind_param("ii", $user_id, $hostel_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();

    if ($existing) {
        $update_stmt = $conn->prepare("UPDATE hostel_reviews SET rating = ?, review = ?, created_at = NOW() WHERE id = ?");
        $update_stmt->bind_param("dsi", $rating, $review, $existing['id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO hostel_reviews (user_id, hostel_id, rating, review) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("iiis", $user_id, $hostel_id, $rating, $review);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
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
            margin-bottom: 40px;
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

       
        .facility-badge {
            background-color: #f0f4ff;
            color: #222;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
        }

       
        h3 {
            margin-top: 40px;
            color: #55236e;
        }

       
        .star-rating {
            direction: rtl;
            font-size: 28px;
            unicode-bidi: bidi-override;
            display: inline-block;
            margin-bottom: 10px;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label {
            color: #f5b301;
        }

       
        textarea {
            width: 100%;
            max-width: 100%;
            border-radius: 10px;
            border: 1px solid #ccc;
            padding: 10px;
            font-size: 15px;
            font-family: Arial, sans-serif;
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #6b3c89;
            border: none;
            color: white;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #532c6f;
        }

        /* User reviews */
        .review-item {
            border-bottom: 1px solid #ccc;
            padding: 15px 0;
        }
        .reviewer-name {
            font-weight: bold;
            color: #55236e;
        }
        .review-date {
            color: gray;
            font-size: 12px;
            margin-left: 10px;
        }
        .review-stars {
            color: #f5b301;
            font-size: 18px;
            margin-left: 5px;
        }
        .review-text {
            margin-top: 8px;
            font-size: 15px;
            line-height: 1.4;
        }
        .no-reviews {
            font-style: italic;
            color: #666;
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
                <div class="facility-badge">
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
        <p>✔️ Student bookings</p>
        <p>✔️ Booking takes just 2 minutes</p>
        <p>✔️ Admin Approval Required</p>
    </div>
</div>
<a href="hostels.php">Back to Hostels</a>
    <a href="book.php?hostel_id=<?= $hostel['id'] ?>" class="book-button" style="text-decoration:none; padding: 10px 20px; background:#28a745; color:#fff; border-radius:6px;">
    Book Now
</a>

    
    <h3>Reviews</h3>

    <?php
    
    $review_query = "
       SELECT r.rating, r.review, r.created_at, u.name
FROM hostel_reviews r
JOIN users u ON r.user_id = u.id
WHERE r.hostel_id = ?
ORDER BY r.created_at DESC

    ";
    $review_stmt = $conn->prepare($review_query);
    $review_stmt->bind_param("i", $hostel_id);
    $review_stmt->execute();
    $review_result = $review_stmt->get_result();

    if ($review_result->num_rows === 0) {
        echo '<p class="no-reviews">No reviews yet. Be the first to review!</p>';
    } else {
        while ($review = $review_result->fetch_assoc()) {
            $stars = intval(round($review['rating'])); 
            ?>
            <div class="review-item">
            <span class="reviewer-name"><?= htmlspecialchars($review['name']) ?></span>
                <span class="review-date">(<?= date('d M Y', strtotime($review['created_at'])) ?>)</span>
                <span class="review-stars">
                    <?php for ($i = 0; $i < $stars; $i++): ?>
                        ★
                    <?php endfor; ?>
                    <?php for ($i = $stars; $i < 5; $i++): ?>
                        ☆
                    <?php endfor; ?>
                </span>
                <p class="review-text"><?= nl2br(htmlspecialchars($review['review'])) ?></p>
            </div>
            <?php
        }
    }
    $review_stmt->close();
    ?>

    <?php if ($has_booking): ?>
        <h3>Submit or Update Your Review</h3>

        <?php
        
        $user_review_stmt = $conn->prepare("SELECT rating, review FROM hostel_reviews WHERE user_id = ? AND hostel_id = ?");
        $user_review_stmt->bind_param("ii", $user_id, $hostel_id);
        $user_review_stmt->execute();
        $user_review = $user_review_stmt->get_result()->fetch_assoc();
        $user_review_stmt->close();

        $current_rating = $user_review['rating'] ?? 0;
        $current_review_text = $user_review['review'] ?? '';
        ?>

        <form method="post" action="">
            <div class="star-rating">
                <?php
                
                for ($i = 5; $i >= 1; $i--) {
                    $checked = ($current_rating == $i) ? "checked" : "";
                    echo '<input type="radio" id="star' . $i . '" name="rating" value="' . $i . '" ' . $checked . '>';
                    echo '<label for="star' . $i . '">★</label>';
                }
                ?>
            </div>

            <textarea name="review" rows="4" placeholder="Write your review here..." required><?= htmlspecialchars($current_review_text) ?></textarea>
            <br><br>
            <input type="submit" value="Submit Review">
        </form>
    <?php else: ?>
        <p><em>You must have booked this hostel to submit a review.</em></p>
    <?php endif; ?>

</div>
</body>
</html>
