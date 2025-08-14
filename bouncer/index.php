<?php

require_once __DIR__ . '/../includes/config.php';

// If bouncer is not logged in, redirect to the correct login page
if (!isset($_SESSION['bouncer_logged_in']) || $_SESSION['bouncer_logged_in'] !== true) {
    header("Location: bouncer_login.php");
    exit();
}

$verificationResult = null;

// Handle AJAX verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['identifier'])) {
    header('Content-Type: application/json');
    $identifier = trim($_POST['identifier']);
    $nickname = $identifier;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
        $stmt->execute([$nickname]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $response = [
                'success' => true,
                'user' => $user
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No registration found.'
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error.'
        ];
    }
    
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bouncer - Verification</title>
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
            align-items: flex-start;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: var(--background-light);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 51, 119, 0.3);
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 51, 119, 0.3);
        }
        
        .header h1 {
            font-family: var(--font-anton);
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            color: var(--secondary);
            text-shadow: 0 0 10px rgba(255, 51, 119, 0.3);
            text-transform: uppercase;
            margin: 0;
        }
        
        .logout-btn {
            background: none;
            border: 1px solid var(--secondary);
            color: var(--secondary);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-family: var(--font-inter);
        }
        
        .logout-btn:hover {
            background-color: var(--secondary);
            color: var(--background-dark);
        }

        .scanner-container {
            margin: 20px 0;
        }
        
        #qr-reader {
            width: 100%;
            border: 2px solid #555;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .input-field {
            flex-grow: 1;
            padding: 10px;
            background-color: #1a1a1a;
            border: 1px solid #444;
            color: var(--text-color);
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(255, 51, 119, 0.3);
        }
        
        .btn-submit {
            background: linear-gradient(90deg, #ff3377 0%, #ff0044 100%);
            color: var(--text-color);
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-family: var(--font-inter);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 119, 0.4);
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-box {
            background: var(--background-light);
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            color: var(--text-color);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
            text-align: center;
            border: 1px solid rgba(255, 51, 119, 0.3);
        }
        
        .modal-success {
            border-left: 5px solid #4caf50;
        }
        
        .modal-error {
            border-left: 5px solid #ff0000;
        }

        .modal-box h3 {
            font-family: var(--font-anton);
            font-size: 1.8rem;
            color: var(--secondary);
            border-bottom: 2px solid rgba(255, 51, 119, 0.3);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .status-confirmed { color: #4caf50; font-weight: bold; }
        .status-pending { color: #ff9800; font-weight: bold; }

        .access-granted {
            font-family: var(--font-anton);
            font-size: 1.8rem;
            color: #4caf50;
            margin-top: 15px;
        }
        
        .access-denied {
            font-family: var(--font-anton);
            font-size: 1.8rem;
            color: #ff0000;
            margin-top: 15px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verification</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="scanner-container">
            <div id="qr-reader"></div>
        </div>
        
        <form method="post" class="form-group" id="manual-form">
            <input type="text" name="identifier" placeholder="Enter nickname manually" required class="input-field">
            <button type="submit" name="verify" class="btn-submit">Verify</button>
        </form>
        
        <div id="verification-results" style="display:none;"></div>
    </div>

    <div id="result-modal-overlay" class="modal-overlay">
        <div id="result-modal-box" class="modal-box">
            <div id="modal-content"></div>
        </div>
    </div>

    <script>
        function showModal(contentHtml, isSuccess) {
            const modalOverlay = document.getElementById('result-modal-overlay');
            const modalBox = document.getElementById('result-modal-box');
            const modalContent = document.getElementById('modal-content');

            modalContent.innerHTML = contentHtml;
            
            // Set success or error class for styling
            modalBox.classList.remove('modal-success', 'modal-error');
            if (isSuccess !== null) {
                modalBox.classList.add(isSuccess ? 'modal-success' : 'modal-error');
            }

            modalOverlay.style.display = 'flex';

            // Auto-close modal after 2 seconds
            setTimeout(() => {
                modalOverlay.style.display = 'none';
            }, 2000);
        }

        function onScanSuccess(decodedText) {
            const formData = new FormData();
            formData.append('identifier', decodedText);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let contentHtml = '';
                let isSuccess = null;

                if (data.success) {
                    contentHtml = `
                        <h3>Verification Result</h3>
                        <p><strong>Nickname:</strong> ${data.user.nickname}</p>
                        <p><strong>Plan:</strong> ${data.user.table_type.charAt(0).toUpperCase() + data.user.table_type.slice(1)}</p>
                        <p><strong>Status:</strong> 
                            <span class="status-${data.user.payment_status}">
                                ${data.user.payment_status.charAt(0).toUpperCase() + data.user.payment_status.slice(1)}
                            </span>
                        </p>
                    `;
                    if (data.user.payment_status === 'confirmed') {
                        contentHtml += `<p class="access-granted">ACCESS GRANTED</p>`;
                        isSuccess = true;
                    } else {
                        contentHtml += `<p class="access-denied">ACCESS DENIED - Payment not confirmed</p>`;
                        isSuccess = false;
                    }
                } else {
                    contentHtml = `<p>${data.message}</p>`;
                    isSuccess = false;
                }
                showModal(contentHtml, isSuccess);
            })
            .catch(error => {
                showModal('<p>An error occurred. Please try again.</p>', false);
                console.error('Error:', error);
            });
        }

        const html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess);

        document.getElementById('manual-form').addEventListener('submit', function(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let contentHtml = '';
                let isSuccess = null;

                if (data.success) {
                    contentHtml = `
                        <h3>Verification Result</h3>
                        <p><strong>Nickname:</strong> ${data.user.nickname}</p>
                        <p><strong>Plan:</strong> ${data.user.table_type.charAt(0).toUpperCase() + data.user.table_type.slice(1)}</p>
                        <p><strong>Status:</strong> 
                            <span class="status-${data.user.payment_status}">
                                ${data.user.payment_status.charAt(0).toUpperCase() + data.user.payment_status.slice(1)}
                            </span>
                        </p>
                    `;
                    if (data.user.payment_status === 'confirmed') {
                        contentHtml += `<p class="access-granted">ACCESS GRANTED</p>`;
                        isSuccess = true;
                    } else {
                        contentHtml += `<p class="access-denied">ACCESS DENIED - Payment not confirmed</p>`;
                        isSuccess = false;
                    }
                } else {
                    contentHtml = `<p>${data.message}</p>`;
                    isSuccess = false;
                }
                showModal(contentHtml, isSuccess);
            })
            .catch(error => {
                showModal('<p>An error occurred. Please try again.</p>', false);
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
