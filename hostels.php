<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$user_id = $_SESSION["user_id"];


$stmt = $conn->prepare("SELECT name, email, location FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$search = trim($_GET['search'] ?? '');

$recommendedSearch = [];
$result = [];

if ($search !== '') {
    
    $sqlTop = "
        SELECT h.*, AVG(hr.rating) AS rating
        FROM hostels h
        LEFT JOIN hostel_reviews hr ON h.id = hr.hostel_id
        WHERE LOWER(h.location) LIKE LOWER(?)
        GROUP BY h.id
        ORDER BY rating DESC
        LIMIT 4
    ";
    $stmtTop = $conn->prepare($sqlTop);
    $like = "%$search%";
    $stmtTop->bind_param("s", $like);
    $stmtTop->execute();
    $recommendedSearch = $stmtTop->get_result();
    $stmtTop->close();

    $sqlRest = "
        SELECT h.*, AVG(hr.rating) AS rating
        FROM hostels h
        LEFT JOIN hostel_reviews hr ON h.id = hr.hostel_id
        WHERE LOWER(h.location) LIKE LOWER(?)
        GROUP BY h.id
        ORDER BY rating DESC
        LIMIT 100 OFFSET 4
    ";
    $stmt = $conn->prepare($sqlRest);
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    
    $stmt = $conn->prepare("
        SELECT h.*, AVG(hr.rating) AS rating
        FROM hostels h
        LEFT JOIN hostel_reviews hr ON h.id = hr.hostel_id
        GROUP BY h.id
        ORDER BY rating DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hostels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        .search-section {
            text-align: center;
            color: rgb(113, 9, 158);
            margin-top: 30px;
        }
        .search-section input {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 300px;
        }
        .search-section button {
            padding: 12px 20px;
            background: var(--primary);
            color: white;
            border: none;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .hostels-grid {
            max-width: 1200px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 0 20px;
        }
        .hostel-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
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
        .view-details {
            display: inline-block;
            margin-top: 10px;
            background: var(--primary);
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }
        h2 {
            text-align: center;
            color: var(--primary);
            font-family: Georgia, serif;
            margin-top: 40px;
        }
        p.rating {
            font-weight: bold;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">HostelNow</div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="hostels.php">Hostels</a>
        <a href="logout.php">Logout</a>
        <div class="profile-icon">
            <i class="fa fa-user-circle" style="font-size: 30px;"></i>
            <div class="profile-dropdown">
                <a href="#">Name: <?= htmlspecialchars($user['name']) ?></a>
                <a href="my_bookings.php">My Bookings</a>
            </div>
        </div>
    </div>
</nav>

<div class="search-section">
    <h2>ALL HOSTELS</h2>
    <form method="GET">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or location">
        <button type="submit"><i class="fas fa-search"></i> Search</button>
    </form>
</div>

<?php if ($search !== ''): ?>
    <h2>Top-rated Hostels in "<?= htmlspecialchars($search) ?>"</h2>
    <div class="hostels-grid">
        <?php while ($row = $recommendedSearch->fetch_assoc()): ?>
            <div class="hostel-card">
                <div class="hostel-img" style="background-image: url('<?= htmlspecialchars($row['image_url'] ?: 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5') ?>');"></div>
                <div class="hostel-info">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="location"><?= htmlspecialchars($row['location']) ?></p>
                    <p class="price">Rs.<?= number_format($row['price'], 2) ?></p>
                    <p class="rating">Rating: <?= is_numeric($row['rating']) ? number_format($row['rating'], 1) . " ⭐" : "N/A" ?></p>
                    <a href="hostel_detail.php?id=<?= $row['id'] ?>" class="view-details">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<h2><?= $search !== '' ? "Other Matching Hostels" : "Featured Hostels" ?></h2>
<div class="hostels-grid">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="hostel-card">
                <div class="hostel-img" style="background-image: url('<?= htmlspecialchars($row['image_url'] ?: 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5') ?>');"></div>
                <div class="hostel-info">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="location"><?= htmlspecialchars($row['location']) ?></p>
                    <p class="price">Rs.<?= number_format($row['price'], 2) ?></p>
                    <p class="rating">Rating: <?= is_numeric($row['rating']) ? number_format($row['rating'], 1) . " ⭐" : "N/A" ?></p>
                    <a href="hostel_detail.php?id=<?= $row['id'] ?>" class="view-details">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; 
        color:gray;">No results found.</p>
    <?php endif; ?>
</div>

</body>
</html>