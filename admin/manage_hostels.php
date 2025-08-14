<?php
session_start();

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../config.php';

$admin_role = $_SESSION["admin_role"];
$admin_hostel_id = $_SESSION["assigned_hostel_id"];

if (isset($_GET['delete']) && $admin_role === 'super_admin') {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT image_url FROM hostels WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($hostel = $res->fetch_assoc()) {
      
        if (!empty($hostel['image_url']) && file_exists("../" . $hostel['image_url'])) {
            unlink("../" . $hostel['image_url']);
        }

        $imgs = $conn->prepare("SELECT image_path FROM hostel_images WHERE hostel_id = ?");
        $imgs->bind_param("i", $id);
        $imgs->execute();
        $imgRes = $imgs->get_result();
        while ($img = $imgRes->fetch_assoc()) {
            if (!empty($img['image_path']) && file_exists("../" . $img['image_path'])) {
                unlink("../" . $img['image_path']);
            }
        }

        $conn->query("DELETE FROM hostel_images WHERE hostel_id = $id");
        $conn->query("DELETE FROM hostel_features WHERE hostel_id = $id");
        $conn->query("DELETE FROM hostels WHERE id = $id");
    }

    header("Location: manage_hostels.php");
    exit;
}


if ($admin_role === "super_admin") {
    $hostels = $conn->query("SELECT * FROM hostels ORDER BY created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
    $stmt->bind_param("i", $admin_hostel_id);
    $stmt->execute();
    $hostels = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Hostels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 960px;
            margin: auto;
        }
        h2 {
            color: #6b3c89;
            margin-bottom: 20px;
        }
        .add-link {
            background-color: #6b3c89;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        .add-link:hover {
            background-color: #532c6f;
        }
        .hostel-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .hostel-card h3 {
            margin: 0 0 10px;
            color: #333;
        }
        .hostel-card p {
            margin: 5px 0;
            color: #555;
        }
        .actions a {
            margin-right: 10px;
            text-decoration: none;
            font-weight: bold;
        }
        .actions a.edit {
            color: #6b3c89;
        }
        .actions a.edit:hover {
            text-decoration: underline;
        }
        .actions a.delete {
            color: red; 
        }
        .actions a.delete:hover {
            text-decoration: underline;
        }
        img.preview {
            width: 100px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Hostels</h2>

    <?php if ($admin_role === 'super_admin'): ?>
        <p><a class="add-link" href="add_hostel.php">+ Add New Hostel</a></p>
    <?php endif; ?>

    <?php while ($row = $hostels->fetch_assoc()): ?>
        <div class="hostel-card">
            <div style="display: flex; align-items: center;">
                <?php if (!empty($row['image_url'])): ?>
                    <img class="preview" src="<?= htmlspecialchars($row['image_url']) ?>" alt="Hostel Image">
                <?php endif; ?>
                <div>
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= htmlspecialchars($row['location']) ?> - Rs.<?= $row['price'] ?></p>
                    <div class="actions">
                        <a href="edit_hostel.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
                        <?php if ($admin_role === 'super_admin'): ?>
                            <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this hostel? This will also delete its images and features.')">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <br>
    <a href="dashboard.php"> Back to Dashboard</a>
</div>
</body>
</html>
