<?php

require_once __DIR__ . '/../includes/config.php';

// Simple password check (for demonstration)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In production, use password_verify with a hashed password
    // $hashedPassword = '$2y$10$wT5gC/0N2k7Zz.z/z.z/z.z/z.z/z.z/z.z/z.z/z.z/z'; // Example hashed password
    $bouncerPassword = $bouncerConfig['password'] ?? 'bouncer123'; // Get from config or use a default
    if (isset($_POST['password']) && $_POST['password'] === $bouncerPassword) {
        $_SESSION['bouncer_logged_in'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouncer Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background: var(--background-dark);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background: var(--background-light);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 51, 119, 0.3);
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .header-text {
            font-family: var(--font-anton);
            text-align: center;
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            font-size: 0.9rem;
            color: #ccc;
            margin-bottom: 8px;
        }
        
        .input-field {
            width: 100%;
            padding: 12px;
            background-color: #1a1a1a;
            border: 1px solid #444;
            color: var(--text-color);
            border-radius: 6px;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(255, 51, 119, 0.3);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(90deg, #ff3377 0%, #ff0044 100%);
            color: var(--text-color);
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-family: var(--font-anton);
            font-size: 1.1rem;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 119, 0.4);
        }

        .error {
            background-color: #b71c1c;
            color: white;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="header-text">Bouncer Login</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="input-group">
                <label for="password"><i class="fas fa-lock mr-2"></i>Password</label>
                <input type="password" id="password" name="password" required class="input-field">
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>
    </div>
</body>
</html>
