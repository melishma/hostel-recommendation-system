<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';
require_once 'recommend_hostels.php';

$user_id = $_SESSION["user_id"];
$recommendedData = recommendHostels($conn, $user_id);
$recommendedHostels = $recommendedData['hostels'];
$recommendationSource = $recommendedData['source'];

$stmt = $conn->prepare("SELECT name, email, location FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
   
    $sql = "
        SELECT h.*, AVG(hr.rating) AS rating,
            CASE WHEN LOWER(h.location) LIKE LOWER(?) THEN 1 ELSE 0 END AS is_location_match
        FROM hostels h
        LEFT JOIN hostel_reviews hr ON h.id = hr.hostel_id
        WHERE h.name LIKE ? OR h.location LIKE ?
        GROUP BY h.id
        ORDER BY is_location_match DESC, rating DESC
        LIMIT 4
    ";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $sql = "
        SELECT h.*, AVG(hr.rating) AS rating
        FROM hostels h
        LEFT JOIN hostel_reviews hr ON h.id = hr.hostel_id
        GROUP BY h.id
        ORDER BY rating DESC
        LIMIT 4
    ";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HostelNow | Browse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        :root {
            --primary: rgb(88, 3, 109);
            --secondary: #2ecc71;
            --dark: rgb(88, 3, 109);
            --light: #f9f9f9;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            color: #333;
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
            color: var(--primary);
        }
        .nav-links {
            display: flex;
            align-items: center;
        }
        .nav-links a {
            margin-left: 20px;
            color: var(--dark);
            text-decoration: none;
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
            color: #6b3c89;
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
            background: rgba(105, 58, 117, 0.9);
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
        .search-box {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        .search-box input {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 300px;
        }
        .search-box button {
            padding: 12px 20px;
            background: #fff;
            color: var(--primary);
            border: none;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }
        .hostels-grid,
        .recommended-grid {
            max-width: 1200px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 0 20px;
        }
        .hostels-grid-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .hostel-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        .hostel-card:hover {
            transform: translateY(-5px);
        }
        .hostel-img {
            height: 180px;
            background-size: cover;
            background-position: center;
        }
        .hostel-info {
            padding: 15px;
        }
        .hostel-info h3 {
            margin: 0 0 10px;
            color: var(--dark);
        }
        .location {
            color: var(--primary);
        }
        .price {
            font-weight: bold;
            color: var(--secondary);
        }
        .rating {
            color: #f39c12;
        }

        .view-details {
            display: inline-block;
            margin-top: 10px;
            background: var(--primary);
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            background: #222;
            color: #ccc;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            padding: 40px 20px;
        }
        .footer-col {
            flex: 1 1 200px;
            margin: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">HostelNow</div>
    <div class="nav-links">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="hostels.php"><i class="fas fa-bed"></i> Hostels</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <div class="profile-icon">
            <i class="fa fa-user-circle" style="font-size: 30px;"></i>
            <div class="profile-dropdown">
                <p style="padding: 12px; 
                margin: 0; 
                font-weight: bold;
                 border-bottom: 1px solid #ccc;">
                    <?= htmlspecialchars($user['name']) ?></p>
                <p style="padding: 8px 12px;
                 margin: 0; 
                 font-size: 0.9em;
                  color: #666;">
                  <?= htmlspecialchars($user['email']) ?></p>
                <a href="my_bookings.php">My Bookings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="hero-overlay">
        <h1>Find Your Perfect Hostel</h1>
        <p>Explore top-rated hostels near you.</p>
        <form class="search-box" method="GET" action="hostels.php">
            <input type="search" name="search" placeholder="Search by name or location" value="<?= htmlspecialchars($search) ?>" />
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</section>

<?php if ($recommendedData['source'] === 'collaborative'): ?>
    <p class="info">Recommended based on your reviews and bookings.</p>
<?php elseif ($recommendedData['source'] === 'location'): ?>
    <p class="info">Top-rated hostels near your location.</p>
<?php else: ?>
    <p class="info">Top-rated hostels overall.</p>
<?php endif; ?>


<h2 style="text-align: center;
 font-size: 2em; 
 color: var(--primary); 
 margin: 40px 0 20px;">Recommended for You</h2>

<div class="hostels-grid">
    <?php foreach ($recommendedHostels as $hostel): ?>
        <div class="hostel-card">
            <div class="hostel-img" style="background-image: url('<?= htmlspecialchars($hostel['image_url'] ?: 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5') ?>');"></div>
            <div class="hostel-info">
                <h3><?= htmlspecialchars($hostel['name']) ?></h3>
                <p class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($hostel['location']) ?></p>
                <p class="price">Rs.<?= number_format($hostel['price'], 2) ?></p>
                <?php
                $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM hostel_reviews WHERE hostel_id = ?");
                $stmt->bind_param("i", $hostel['id']);
                $stmt->execute();
                $avgRes = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                ?>
                <p class="rating">Rating: <?= is_numeric($avgRes['avg_rating']) ? number_format($avgRes['avg_rating'], 1) . " ⭐" : "N/A" ?></p>
                <a href="hostel_detail.php?id=<?= $hostel['id'] ?>" class="view-details">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<h2 style="text-align: center; 
font-size: 2em; color: var(--primary); 
margin: 40px 0 20px;">
    <?= $search !== '' ? "Search Results" : "Featured Hostels" ?>
</h2>

<div class="hostels-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="hostel-card">
                <div class="hostel-img" style="background-image: url('<?= htmlspecialchars($row['image_url'] ?: 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5') ?>');"></div>
                <div class="hostel-info">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['location']) ?></p>
                    <p class="price">Rs.<?= number_format($row['price'], 2) ?></p>
                    <p class="rating">Rating: <?= is_numeric($row['rating']) ? number_format($row['rating'], 1) . " ⭐" : "N/A" ?></p>
                    <a href="hostel_detail.php?id=<?= $row['id'] ?>" class="view-details">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align:center; 
        color:gray;
         font-style: italic; 
         padding: 40px 0;">
            <?= $search !== '' ? "No results found for \"$search\"." : "No hostels found." ?>
        </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="footer-col">
        <h3>About HostelNow</h3>
        <p>HostelNow is your go-to platform for discovering and booking the best hostels near you.</p>
    </div>
    <div class="footer-col">
        <h3>Contact</h3>
        <p>Email: support@hostelnow.com</p>
        <p>Phone: +977 123456789</p>
        <p>Address: Kathmandu, Nepal</p>
    </div>
    <div class="footer-col">
        <h3>Follow Us</h3>
        <p>
            <a href="#" style="color: #ccc;">Facebook</a> |
            <a href="#" style="color: #ccc;">Twitter</a> |
            <a href="#" style="color: #ccc;">Instagram</a>
        </p>
    </div>
</footer>

</body>
</html>


