<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}

// CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$admin_role = $_SESSION["admin_role"];
$admin_hostel_id = $_SESSION["assigned_hostel_id"];
$id = intval($_GET['id'] ?? 0);
$message = "";

// Fetch hostel
$stmt = $conn->prepare("SELECT * FROM hostels WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$hostel = $stmt->get_result()->fetch_assoc();

if (!$hostel) {
    die("Hostel not found.");
}

if ($admin_role !== 'super_admin' && $hostel['id'] != $admin_hostel_id) {
    die("Unauthorized access.");
}

// Fetch images
$image_stmt = $conn->prepare("SELECT * FROM hostel_images WHERE hostel_id = ?");
$image_stmt->bind_param("i", $id);
$image_stmt->execute();
$existing_images = $image_stmt->get_result();

// Fetch all features
$all_features_result = $conn->query("SELECT * FROM features");
$selected_stmt = $conn->prepare("SELECT feature_id FROM hostel_features WHERE hostel_id = ?");
$selected_stmt->bind_param("i", $id);
$selected_stmt->execute();
$selected_result = $selected_stmt->get_result();
$selected_features = [];
while ($row = $selected_result->fetch_assoc()) {
    $selected_features[] = $row['feature_id'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $price = floatval($_POST["price"]);
    $description = $_POST["description"];
    $capacity = (int)$_POST["capacity"];
    $mainImagePath = $hostel['image_url'];

    if ($price < 0 || $capacity < 1) {
        $message = "Invalid data entered.";
    } else {
        // Handle main image
        if (!empty($_FILES["main_image"]["name"])) {
            $mainImage = $_FILES["main_image"];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $mime = mime_content_type($mainImage["tmp_name"]);

            if (!in_array($mime, $allowedTypes)) {
                $message = "Invalid main image type.";
            } else {
                $targetDir = "../uploads/";
                if (!file_exists($targetDir)) mkdir($targetDir, 0755, true);
                $newFileName = uniqid() . "_" . basename($mainImage["name"]);
                $targetFilePath = $targetDir . $newFileName;

                if (move_uploaded_file($mainImage["tmp_name"], $targetFilePath)) {
                    if (!empty($hostel['image_url']) && file_exists("../" . $hostel['image_url'])) {
                        unlink("../" . $hostel['image_url']);
                    }
                    $mainImagePath = "uploads/" . $newFileName;
                } else {
                    $message = "Failed to upload main image.";
                }
            }
        }

        // Update hostel info
        $update = $conn->prepare("UPDATE hostels SET name=?, location=?, price=?, capacity=?, description=?, image_url=? WHERE id=?");
        $update->bind_param("ssdissi", $name, $location, $price, $capacity, $description, $mainImagePath, $id);

        if ($update->execute()) {
            // Handle additional images
            if (!empty($_FILES["images"]["name"][0])) {
                foreach ($_FILES["images"]["tmp_name"] as $index => $tmpName) {
                    if (!in_array(mime_content_type($tmpName), $allowedTypes)) continue;

                    $filename = uniqid() . "_" . basename($_FILES["images"]["name"][$index]);
                    $targetFile = "../uploads/" . $filename;
                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $relativePath = "uploads/" . $filename;
                        $imgInsert = $conn->prepare("INSERT INTO hostel_images (hostel_id, image_path) VALUES (?, ?)");
                        $imgInsert->bind_param("is", $id, $relativePath);
                        $imgInsert->execute();
                    }
                }
            }

            // Update features
            $conn->query("DELETE FROM hostel_features WHERE hostel_id = $id");
            if (!empty($_POST['features'])) {
                $insert = $conn->prepare("INSERT INTO hostel_features (hostel_id, feature_id) VALUES (?, ?)");
                foreach ($_POST['features'] as $feature_id) {
                    $insert->bind_param("ii", $id, $feature_id);
                    $insert->execute();
                }
            }

            header("Location: manage_hostels.php");
            exit;
        } else {
            $message = "Error updating: " . $update->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Hostel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 40px;
        }
        .form-container {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #6a2c91;
            margin-bottom: 20px;
        }
        input, textarea, button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
        }
        .features-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px 20px;
    margin-top: 10px;
}

.feature-item {
    display: flex;
    align-items: center;
    width: 45%; /* You can change this to 50% or 30% for different column layouts */
}

        button {
            background: #6a2c91;
            color: white;
            border: none;
        }
        button:hover {
            background: #582077;
        }
        .error {
            color: red;
            text-align: center;
        }
        .preview img {
            height: 100px;
            margin: 5px;
            border-radius: 5px;
        }
        .image-box {
            display: inline-block;
            margin: 10px;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Edit Hostel</h2>
    <?php if ($message): ?><p class="error"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <input type="text" name="name" value="<?= htmlspecialchars($hostel['name']) ?>" required>
        <input type="text" name="location" value="<?= htmlspecialchars($hostel['location']) ?>" required>
        <input type="number" step="0.01" name="price" value="<?= $hostel['price'] ?>" required min="0">
        <input type="number" name="capacity" value="<?= $hostel['capacity'] ?>" required min="1">
        <textarea name="description" required><?= htmlspecialchars($hostel['description']) ?></textarea>

        <label>Main Image:</label>
        <?php if ($hostel['image_url']): ?>
            <div class="preview">
                <img src="../<?= htmlspecialchars($hostel['image_url']) ?>" alt="Main Image">
            </div>
        <?php endif; ?>
        <input type="file" name="main_image" accept="image/*">

        <label>Additional Images:</label>
        <input type="file" name="images[]" multiple accept="image/*">

        <label>Features:</label>
<div class="features-grid">
    <?php $all_features_result->data_seek(0); while ($feature = $all_features_result->fetch_assoc()): ?>
        <div class="feature-item">
            <label>
                <input type="checkbox" name="features[]" value="<?= $feature['id'] ?>"
                    <?= in_array($feature['id'], $selected_features) ? 'checked' : '' ?>>
                <?= htmlspecialchars($feature['name']) ?>
            </label>
        </div>
    <?php endwhile; ?>
</div>


        <button type="submit">Update Hostel</button>
    </form>
    <a href="manage_hostels.php">Back</a>

    <?php if ($existing_images->num_rows > 0): ?>
        <h4>Uploaded Additional Images:</h4>
        <div class="preview">
            <?php while ($img = $existing_images->fetch_assoc()): ?>
                <div class="image-box">
                    <img src="../<?= htmlspecialchars($img['image_path']) ?>" alt="Additional Image">
                    <form method="POST" action="delete_image.php" onsubmit="return confirm('Delete this image?');">
                        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                        <input type="hidden" name="hostel_id" value="<?= $id ?>">
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
