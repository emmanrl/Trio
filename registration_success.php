<?php
require __DIR__ . '/includes/config.php';

$accessCode = $_GET['code'] ?? '';

if (empty($accessCode)) {
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE access_code = ?");
    $stmt->execute([$accessCode]);
    $registration = $stmt->fetch();
    
    if (!$registration) {
        throw new Exception("Invalid access code");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Instructions | Trio Outsyders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;700&display=swap');
        
        :root {
            --primary: #c20c4e; /* Deep Pink/Red */
            --secondary: #ff3377; /* Lighter Pink */
            --text-color: #f7f3f3; /* Off-white */
            --background-dark: #121212;
            --background-light: #2c2c2c;
            --font-anton: 'Anton', sans-serif;
            --font-inter: 'Inter', sans-serif;
        }

        body {
            background-color: var(--background-dark);
            font-family: var(--font-inter);
            color: var(--text-color);
        }
        
        .header-text {
            font-family: var(--font-anton);
            text-transform: uppercase;
        }
        
        .card {
            background: var(--background-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .access-code-box {
            background-color: rgba(255, 51, 119, 0.1);
            border: 1px solid var(--secondary);
        }

        .access-code {
            color: var(--secondary);
            font-family: var(--font-anton);
            text-shadow: 0 0 10px var(--secondary);
            animation: pulse 2s infinite;
        }
        
        .bank-details {
            background: #1a1a1a;
        }

        .btn-primary {
            background: linear-gradient(90deg, #ff3377 0%, #ff0044 100%);
            transition: all 0.3s ease;
            font-family: var(--font-anton);
            text-transform: uppercase;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 119, 0.4);
        }
        
        @keyframes pulse {
            0% { opacity: 0.8; }
            50% { opacity: 1; text-shadow: 0 0 15px var(--secondary); }
            100% { opacity: 0.8; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full card rounded-xl shadow-2xl overflow-hidden animate__animated animate__fadeIn">
        <div class="p-8 text-center">
            <h1 class="header-text text-3xl font-bold text-white mb-2">PAYMENT INSTRUCTIONS</h1>
            <p class="text-gray-400 text-xs mb-6">Complete your reservation by making payment</p>
            
            <div class="access-code-box rounded-lg p-6 mb-6">
                <p class="text-gray-300 mb-2 font-bold header-text">AMOUNT TO PAY:</p>
                <div class="access-code text-5xl font-bold py-2">
                    ₦<?= number_format($registration['amount'], 2) ?>
                </div>
                <div class=" items-center justify-center mt-4">
                    <p class="text-gray-400 mr-2">Access Code:<br></p>
                    <button onclick="copyAccessCode()" class="text-secondary hover:text-white transition-colors duration-200" title="Copy access code">
                        <i class="fas fa-copy text-xl"></i>
                    </button>
                    <span id="access-code-to-copy" class="text-white font-bold text-lg mr-3"><?= htmlspecialchars($registration['access_code']) ?></span>
                    
                </div>
            </div>
            
            <div class="bank-details rounded-lg p-6 mb-6 text-left text-white">
                <h3 class="header-text text-xl font-bold mb-4 text-center text-secondary">Bank Transfer Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-300 text-sm">Bank Name:</p>
                        <p class="font-bold text-lg">First Bank</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-300 text-sm">Account Name:</p>
                        <p class="font-bold text-lg">Trio Outsyders Events</p>
                    </div>
                    
                    <div>
                        <p class="text-gray-300 text-sm">Account Number:</p>
                        <p class="font-bold text-2xl text-secondary">3123456789</p>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-gray-800 rounded">
                    <p class="text-xs text-yellow-300"><i class="fas
                    fa-exclamation-circle mr-2"></i> <b>IMPORTANT:</b> Use your access
                    code as the payment reference to ensure your ticket is
                    processed.</p>
                </div>
            </div>
            
            <div class="text-left space-y-3 text-gray-300 mb-6">
                <p><span class="font-bold text-white">NICKNAME:</span> <?= htmlspecialchars($registration['nickname']) ?></p>
                <p><span class="font-bold text-white">TABLE TYPE:</span> <?= ucfirst($registration['table_type']) ?></p>
                <p><span class="font-bold text-white">STATUS:</span> 
                    <span class="<?= 
                        $registration['payment_status'] === 'confirmed' ? 'text-green-400' : 
                        ($registration['payment_status'] === 'rejected' ? 'text-red-400' : 'text-yellow-400')
                    ?> font-bold">
                        <?= ucfirst($registration['payment_status']) ?>
                    </span>
                </p>
            </div>
            
            <a href="ticket.php?nickname=<?= urlencode($registration['nickname']) ?>" 
               class="btn-primary w-full text-white font-bold py-4 px-4 rounded-lg flex items-center justify-center text-xl">
                <i class="fas fa-ticket-alt mr-2"></i> CHECK TICKET STATUS
            </a>
            
            <div class="mt-4 text-sm text-gray-400">
                <p>After payment, your ticket will be verified and updated automatically.</p>
            </div>
        </div>
    </div>

    <script>
        function copyAccessCode() {
            const accessCodeText = document.getElementById('access-code-to-copy').textContent;
            navigator.clipboard.writeText(accessCodeText)
                .then(() => {
                    alert('Access code copied to clipboard!');
                })
                .catch(err => {
                    console.error('Failed to copy access code: ', err);
                    alert('Failed to copy the access code. Please copy it manually.');
                });
        }
    </script>
</body>
</html>
