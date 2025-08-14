<?php
session_start();


if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_role"] !== 'super_admin') {
    header("Location: admin_login.php");
    exit;
}

require_once '../config.php';

$message = "";


$features_result = $conn->query("SELECT * FROM features");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $location = trim($_POST["location"]);
    $price = max(0, floatval($_POST["price"]));
    $description = $_POST["description"];
    $capacity = intval($_POST["capacity"]);
    $selected_features = $_POST['features'] ?? [];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; 

    $main_image_url = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        if (!in_array($_FILES['main_image']['type'], $allowedTypes) || $_FILES['main_image']['size'] > $maxSize) {
            $message = "Invalid main image type or size.";
        } else {
            $main_filename = uniqid() . '_' . basename($_FILES['main_image']['name']);
            $main_destination = "../uploads/" . $main_filename;
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $main_destination)) {
                $main_image_url = "uploads/" . $main_filename;
            } else {
                $message = "Failed to upload main image.";
            }
        }
    } else {
        $message = "Main image is required.";
    }

    $uploadedPaths = [];
    if (isset($_FILES['images']) && empty($message)) {
        foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $type = $_FILES['images']['type'][$i];
                $size = $_FILES['images']['size'][$i];

                if (!in_array($type, $allowedTypes) || $size > $maxSize) continue;

                $filename = uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                $destination = "../uploads/" . $filename;
                if (move_uploaded_file($tmpName, $destination)) {
                    $uploadedPaths[] = "uploads/" . $filename;
                }
            }
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO hostels (name, location, price, description, image_url, capacity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $location, $price, $description, $main_image_url, $capacity);

        if ($stmt->execute()) {
            $hostel_id = $conn->insert_id;

         
            if (!empty($selected_features)) {
                $feature_stmt = $conn->prepare("INSERT INTO hostel_features (hostel_id, feature_id) VALUES (?, ?)");
                foreach ($selected_features as $feature_id) {
                    $feature_stmt->bind_param("ii", $hostel_id, $feature_id);
                    $feature_stmt->execute();
                }
            }

            
            foreach ($uploadedPaths as $path) {
                $img_stmt = $conn->prepare("INSERT INTO hostel_images (hostel_id, image_path) VALUES (?, ?)");
                $img_stmt->bind_param("is", $hostel_id, $path);
                $img_stmt->execute();
            }

            header("Location: manage_hostels.php");
            exit;
        } else {
            $message = "Database error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Hostel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
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
            margin-bottom: 20px;
            color: #6a2c91;
            text-align: center;
        }
        input, textarea, button, select {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        button {
            background: #6a2c91;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background: #542370;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        a {
            display: block;
            margin-top: 10px;
            text-align: center;
            color: #6a2c91;
            text-decoration: none;
        }

        .features-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 20px;
            margin-top: 5px;
            margin-bottom: 20px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            width: 45%; 
        .feature-item input[type="checkbox"] {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Hostel</h2>
        <?php if ($message): ?>
            <p class="error"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Hostel Name" maxlength="100" required>
            <input type="text" name="location" placeholder="Location" maxlength="100" required>
            <input type="number" name="price" placeholder="Price" step="0.01" min="0" required>
            <input type="number" name="capacity" placeholder="Capacity" min="1" required>
            <textarea name="description" placeholder="Description" rows="4" maxlength="500" required></textarea>

            
            <label>Hostel Features:</label>
            <div class="features-grid">
                <?php
                
                $features_result->data_seek(0);
                while ($feature = $features_result->fetch_assoc()):
                ?>
                    <div class="feature-item">
                        <label>
                            <input type="checkbox" name="features[]" value="<?= $feature['id'] ?>">
                            <?= htmlspecialchars($feature['name']) ?>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>

            <label>Main Cover Image:</label>
            <input type="file" name="main_image" accept="image/*" required>

            <label>Additional Images:</label>
            <input type="file" name="images[]" multiple accept="image/*">

            <button type="submit">Add Hostel</button>
        </form>

        <a href="manage_hostels.php">Back</a>
    </div>
</body>
</html>
