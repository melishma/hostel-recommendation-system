<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM hostels WHERE name LIKE ? OR location LIKE ? ORDER BY rating DESC LIMIT 4";  // Updated SQL
$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelNow | Browse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary:rgb(88, 3, 109);
            --secondary: #2ecc71;
            --dark:rgb(88, 3, 109);
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
            background: rgb(105, 58, 117,0.9);
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
                <a href="#">Name: <?= htmlspecialchars($user['name']) ?></a>
                <a href="my_bookings.php">My Bookings</a>
            </div>
        </div>
    </div>
</nav>


<section class="hero">
    <div class="hero-overlay">
        <h1>Helping Students Feel at Home</h1>
        <p>Student stays made affordable</p>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search by name or location..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>
</section>


<h2 style="text-align: center; font-size: 2em; color: var(--primary); margin-bottom: 20px;">Featured Hostels</h2>


<div class="hostels-grid">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="hostel-card">
                <div class="hostel-img" style="background-image: url('<?= htmlspecialchars($row['image_url'] ?? 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5') ?>');"></div>
                <div class="hostel-info">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($row['location']) ?></p>
                    <p class="price">Rs.<?= number_format($row['price'], 2) ?></p>
                    <?php /* ?>
<p class="rating"><i class="fas fa-star"></i> <?= $row['rating'] ?? '4.5' ?> (<?= $row['reviews'] ?? '100' ?> reviews)</p>
<?php */ ?>

                    <a href="hostel_detail.php?id=<?= $row['id'] ?>" class="view-details">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-results"><p>No hostels found. Try a different search!</p></div>
    <?php endif; ?>
</div>


<footer class="footer">
    <div class="footer-col">
        <h4>Contact</h4>
        <p>Email: info@hostelNow.com</p>
        <p>Phone: 9800110011</p>
    </div>
    <div class="footer-col">
        <h4>Newsletter</h4>
        <p>Subscribe to get latest offers</p>
    </div>
    <div class="footer-col">
        <h4>Follow Us</h4>
        <p><i class="fab fa-facebook"></i> <i class="fab fa-instagram"></i> <i class="fab fa-twitter"></i></p>
    </div>
</footer>

</body>
</html>
