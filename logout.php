<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logged Out - HostelNow</title>
    <meta http-equiv="refresh" content="2;url=login.php">
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
        .logout-box {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logout-box h2 {
            color: #6b3c89;
            margin-bottom: 10px;
        }
        .logout-box p {
            color: #555;
            font-size: 16px;
        }
        .logout-box a {
            display: inline-block;
            margin-top: 20px;
            background-color: #6b3c89;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 15px;
        }
        .logout-box a:hover {
            background-color: #5a3272;
        }
    </style>
</head>
<body>

<div class="logout-box">
    <h2>You have been logged out</h2>
    <p>Redirecting to login page...</p>
    <p><a href="login.php">Click here if not redirected</a></p>
</div>

</body>
</html>
