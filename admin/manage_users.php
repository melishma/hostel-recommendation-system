<?php 
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../config.php';

$admin_role = $_SESSION["admin_role"];
$admin_id = $_SESSION["admin_id"] ?? null;

// Handle deletion - Super Admins only
if (isset($_GET['delete_id']) && $admin_role === 'super_admin') {
    $delete_id = intval($_GET['delete_id']);

    // Fetch role from admins for the user to delete (if admin)
    $stmt = $conn->prepare("
        SELECT role FROM admins WHERE id = ?
        UNION
        SELECT 'user' -- fallback role for normal users
        WHERE NOT EXISTS (SELECT 1 FROM admins WHERE id = ?)
    ");
    $stmt->bind_param("ii", $delete_id, $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $role_to_delete = $row['role'] ?? 'user';

    // Prevent deleting super_admins and self
    if (strtolower($role_to_delete) !== 'super_admin' && $delete_id !== $admin_id) {
        // Delete bookings first
        if ($role_to_delete === 'admin') {
            // Delete hostels managed by this admin (if needed)
            $stmt = $conn->prepare("UPDATE hostels SET admin_id = NULL WHERE admin_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
        
            // Delete from admins table
            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
        
        } else {
            // Delete bookings first
            $stmt1 = $conn->prepare("DELETE FROM bookings WHERE user_id = ?");
            $stmt1->bind_param("i", $delete_id);
            $stmt1->execute();
        
            // Then delete user
            $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt2->bind_param("i", $delete_id);
            $stmt2->execute();
        }
        
        header("Location: manage_users.php");
        exit;
    }
}

// Fetch users, admins, and super_admins with hostels for admins
$query = "
  (
    SELECT 
      u.id,
      u.name,
      u.email,
      'user' AS role,
      NULL AS hostel_names
    FROM users u
  )
  UNION ALL
  (
    SELECT 
      a.id,
      a.name,
      a.email,
      a.role,
      (
        SELECT GROUP_CONCAT(h.name SEPARATOR ', ')
        FROM hostels h
        WHERE h.admin_id = a.id
      ) AS hostel_names
    FROM admins a
  )
  ORDER BY FIELD(role, 'super_admin', 'admin', 'user'), name ASC
";

$users = $conn->query($query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        h2 {
            color: #5c247a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background: #5c247a;
            color: white;
        }
        .btn-delete {
            background-color: #d9534f;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-delete:hover {
            background-color: #c9302c;
        }
        .disabled {
            color: #999;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #5c247a;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Manage Users</h2>

    <table>
        <thead>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Hostel(s) (if Admin)</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($users && $users->num_rows > 0): ?>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                    <td>
                        <?= in_array(strtolower($user['role']), ['admin', 'super_admin']) 
                            ? htmlspecialchars($user['hostel_names'] ?? 'None') 
                            : '-' ?>
                    </td>
                    <td>
                        <?php if (
                            $admin_role === 'super_admin' &&
                            strtolower($user['role']) !== 'super_admin' &&
                            $user['id'] !== $admin_id
                        ): ?>
                            <a href="?delete_id=<?= htmlspecialchars($user['id']) ?>" onclick="return confirm('Are you sure you want to delete this user?');">
                                <button class="btn-delete">Delete</button>
                            </a>
                        <?php else: ?>
                            <span class="disabled">Not Allowed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a class="back-link" href="dashboard.php"> Back to Dashboard</a>
</div>
</body>
</html>
