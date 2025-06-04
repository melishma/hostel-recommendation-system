<?php
require_once 'config.php';
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    if ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        // Store password as plain text (not recommended for production)
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - HostelNow</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6fb;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .register-box {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #6b3c89;
            margin-bottom: 25px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #6b3c89;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #5a3272;
        }
        .message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .link {
            text-align: center;
            margin-top: 15px;
        }
        .link a {
            color: #6b3c89;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Register</h2>
    <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
    <div class="link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
