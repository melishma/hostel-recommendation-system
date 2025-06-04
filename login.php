<?php
session_start();
require_once 'config.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password, $role);
        $stmt->fetch();

        // If using hashed passwords, use password_verify()
        if ($password === $hashed_password) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user_name"] = $name;
            $_SESSION["user_role"] = $role;

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: admin/admin_login.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "User not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - HostelNow</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-box h2 {
            margin-bottom: 25px;
            text-align: center;
            color: #333;
        }
        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        .login-box button {
            width: 100%;
            background-color: #6b3c89;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .login-box button:hover {
            background-color: #5a3272;
        }
        .login-box .register-link {
            margin-top: 15px;
            text-align: center;
        }
        .login-box .register-link a {
            color: #6b3c89;
            text-decoration: none;
        }
        .login-box .register-link a:hover {
            text-decoration: underline;
        }
        .message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login to HostelNow</h2>
    <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <div class="register-link">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>
