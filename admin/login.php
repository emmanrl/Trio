<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (attemptAdminLogin($username, $password)) {
        header('Location: ' . ADMIN_BASE . '/index.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}

if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_BASE . '/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trio Outsyders - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;700&display=swap');
        
        :root {
            --primary: #c20c4e;
            --secondary: #ff3377;
            --text-color: #f7f3f3;
            --background-dark: #121212;
            --background-light: #2c2c2c;
            --font-anton: 'Anton', sans-serif;
            --font-inter: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-inter);
            background-color: var(--background-dark);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 40px;
            background: var(--background-light);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 51, 119, 0.3);
            animation: fadeIn 0.5s ease-in-out;
        }

        h1 {
            font-family: var(--font-anton);
            font-size: 2.5rem;
            color: var(--secondary);
            text-align: center;
            margin-bottom: 2rem;
            text-shadow: 0 0 10px rgba(255, 51, 119, 0.3);
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 1rem;
            color: #ccc;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            background-color: #1a1a1a;
            border: 1px solid #444444;
            color: var(--text-color);
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(255, 51, 119, 0.5);
            outline: none;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(90deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s;
            font-size: 1.1rem;
            font-family: var(--font-anton);
            text-transform: uppercase;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 51, 119, 0.4);
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: bold;
        }

        .alert.error {
            background-color: rgba(255, 0, 0, 0.2);
            color: #ff5555;
            border: 1px solid rgba(255, 0, 0, 0.5);
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container animate__animated animate__fadeIn">
        <h1>Admin Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
