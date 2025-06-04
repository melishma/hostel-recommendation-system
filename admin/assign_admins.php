<?php
require_once 'access_control.php';
require_once '../config.php';

// Only allow super admins
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'super_admin') {
    echo "Access denied.";
    exit;
}

$message = "";

// Handle admin creation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_admin'])) {
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];      // Note: No hash, just plain text for now
    $hostel_id = intval($_POST['hostel_id']);

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
    } elseif (strlen($password) < 3) {
        $message = "Password must be at least 3 characters.";
    } else {
        // Check for duplicate email
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            // Insert new admin (role = 'admin')
            $insert = $conn->prepare("
                INSERT INTO admins 
                    (name, email, password, role, hostel_id, assigned_hostel_id) 
                VALUES 
                    (?, ?, ?, 'admin', ?, ?)
            ");
            $insert->bind_param("sssii", $name, $email, $password, $hostel_id, $hostel_id);

            if ($insert->execute()) {
                // Grab the newly inserted admin's ID
                $new_admin_id = $conn->insert_id;

                // Now update that hostel row to set admin_id = $new_admin_id
                $updateHostel = $conn->prepare("
                    UPDATE hostels 
                       SET admin_id = ? 
                     WHERE id = ?
                ");
                $updateHostel->bind_param("ii", $new_admin_id, $hostel_id);

                if ($updateHostel->execute()) {
                    $message = "Admin created and assigned successfully.";
                } else {
                    // If the UPDATE fails, report the error
                    $message = "Admin was created, but failed to assign to hostel: " . $updateHostel->error;
                }
            } else {
                $message = "Error inserting admin: " . $insert->error;
            }
        } else {
            $message = "Email already exists.";
        }
    }
}


// Handle admin reassignment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reassign_admin'])) {
    $admin_id_to_update = intval($_POST['admin_id']);
    $hostel_id          = intval($_POST['hostel_id']);

    // Update the admins table
    $update = $conn->prepare("
        UPDATE admins 
           SET hostel_id = ?, assigned_hostel_id = ? 
         WHERE id = ?
    ");
    $update->bind_param("iii", $hostel_id, $hostel_id, $admin_id_to_update);

    if ($update->execute()) {
        // Now update the hostels table so that this hostel’s admin_id = $admin_id_to_update
        $updateHostel = $conn->prepare("
            UPDATE hostels 
               SET admin_id = ? 
             WHERE id = ?
        ");
        $updateHostel->bind_param("ii", $admin_id_to_update, $hostel_id);

        if ($updateHostel->execute()) {
            $message = "Admin reassigned successfully.";
        } else {
            $message = "Admin updated, but failed to assign to hostel: " . $updateHostel->error;
        }
    } else {
        $message = "Failed to update admin’s assigned hostel: " . $update->error;
    }
}


// Fetch hostels (to populate dropdowns)
$hostels_result = $conn->query("SELECT id, name FROM hostels ORDER BY name");

// Fetch existing admins (role = 'admin') to show in the table
$admins_result = $conn->query("
    SELECT id, name, email, hostel_id 
      FROM admins 
     WHERE role = 'admin'
    ORDER BY name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Admins to Hostels</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; }
        h2 { color: #6a2c91; }
        form, table { margin-top: 20px; }
        input, select, button {
            width: 100%; 
            padding: 10px; 
            margin-top: 10px; 
            border-radius: 6px; 
            border: 1px solid #ccc;
        }
        button { background: #6a2c91; color: #fff; border: none; }
        button:hover { background: #4b1f6b; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
        th { background: #6a2c91; color: #fff; }
        .message { color: green; text-align: center; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Create & Assign Admin</h2>

    <!-- Show success / error message -->
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- == Create New Admin Form == -->
    <form method="POST">
        <input type="hidden" name="create_admin" value="1">

        <label>Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="text" name="password" required>

        <label>Assign to Hostel</label>
        <select name="hostel_id" required>
            <option value="">-- Select Hostel --</option>
            <?php while ($h = $hostels_result->fetch_assoc()): ?>
                <option value="<?= $h['id'] ?>">
                    <?= htmlspecialchars($h['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Create Admin</button>
    </form>


    <!-- == Show Existing Admins & Reassign Form == -->
    <h2>Current Admins</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Current Hostel</th>
                <th>Reassign</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Reset pointer so we can loop through hostels again for each row’s dropdown
            $hostels_result->data_seek(0);

            while ($a = $admins_result->fetch_assoc()):
                $current_hostel = 'Not Assigned';
                if ($a['hostel_id']) {
                    $res = $conn->query("SELECT name FROM hostels WHERE id = " . intval($a['hostel_id']));
                    $data = $res->fetch_assoc();
                    $current_hostel = $data['name'] ?? 'Unknown';
                }
            ?>
                <tr>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($current_hostel) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="reassign_admin" value="1">
                            <input type="hidden" name="admin_id" value="<?= $a['id'] ?>">

                            <select name="hostel_id" required>
                                <option value="">Select Hostel</option>
                                <?php
                                // Reset pointer again for this select
                                $hostels_result->data_seek(0);
                                while ($h = $hostels_result->fetch_assoc()):
                                ?>
                                    <option value="<?= $h['id'] ?>"
                                        <?= ($a['hostel_id'] == $h['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($h['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <button type="submit">Assign</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
