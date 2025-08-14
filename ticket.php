<?php
require __DIR__ . '/includes/config.php';

$nickname = $_GET['nickname'] ?? '';

if (empty($nickname)) {
    header("Location: index.php");
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found");
    }
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Generate QR code URL dynamically with a specific color
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?" . http_build_query([
    'size' => '300x300',
    'data' => $nickname,
    'format' => 'png',
    'margin' => 10,
    'color' => 'ff3377', // Lighter Pink
    'bgcolor' => 'ffffff'  // White background
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trio Outsiders - Your Ticket</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--background-dark);
            color: var(--text-color);
            font-family: var(--font-inter);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 100;
            transition: opacity 0.5s;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 8px solid rgba(255, 51, 119, 0.2);
            border-top: 8px solid var(--secondary);
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
            margin-bottom: 30px;
        }

        .loading-text {
            font-size: 20px;
            color: var(--secondary);
            text-align: center;
            font-weight: 600;
            text-shadow: 0 0 10px rgba(255, 51, 119, 0.5);
            font-family: var(--font-anton);
            text-transform: uppercase;
        }

        .ticket {
            max-width: 500px;
            margin: 20px auto;
            padding: 30px;
            background: var(--background-light);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 51, 119, 0.3);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-out;
        }

        .ticket-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 51, 119, 0.2);
        }

        .ticket-header h1 {
            font-family: var(--font-anton);
            font-size: 2.2rem;
            color: var(--secondary);
            margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(255, 51, 119, 0.3);
            text-transform: uppercase;
        }

        .ticket-header p {
            color: #ccc;
            font-size: 1.1rem;
            font-weight: 500;
        }

        .qr-code {
            margin: 20px auto;
            text-align: center;
            padding: 15px;
            border: 2px dashed rgba(255, 51, 119, 0.3);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
            position: relative;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .qr-code img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            transition: opacity 0.3s;
            opacity: 0;
        }

        .qr-code small {
            color: #aaa;
            font-size: 0.8rem;
            display: block;
            margin-top: 15px;
        }

        .ticket-info {
            margin: 20px 0;
        }

        .ticket-info p {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1rem;
        }

        .ticket-info strong {
            color: var(--secondary);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .save-btn {
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
            margin-top: 25px;
            transition: all 0.3s;
            font-size: 1.1rem;
            font-family: var(--font-anton);
            text-transform: uppercase;
        }

        .save-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 51, 119, 0.4);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid rgba(76, 175, 80, 0.5);
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid rgba(255, 152, 0, 0.5);
        }

        .status-rejected {
            background: rgba(255, 0, 0, 0.2);
            color: #ff0000;
            border: 1px solid rgba(255, 0, 0, 0.5);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="loading-container" id="loading">
        <div class="loading-spinner"></div>
        <div class="loading-text">PREPARING YOUR EXCLUSIVE TICKET</div>
    </div>

    <div class="ticket" id="ticket">
        <div class="ticket-header">
            <h1>TRIO OUTSIDERS</h1>
            <p>9PM, 22ND DEC</p>
        </div>

        <div class="qr-code">
            <img id="qrImage" src="<?php echo htmlspecialchars($qrCodeUrl); ?>" alt="QR Code" onload="imageLoaded()">
            <small>Scan this QR code at the entrance</small>
        </div>

        <div class="ticket-info">
            <p><strong>Nickname:</strong> <?php echo htmlspecialchars($user['nickname']); ?></p>
            <p><strong>Plan:</strong> <?php echo strtoupper($user['table_type']); ?></p>
            <p><strong>Number of People:</strong> <?php echo $user['num_people']; ?></p>
            <p><strong>Amount:</strong> ₦<?php echo number_format($user['amount'], 2); ?></p>
            <p><strong>Status:</strong> <span class="status-badge status-<?php echo $user['payment_status']; ?>"><?php echo ucfirst($user['payment_status']); ?></span></p>
        </div>

        <button class="save-btn" onclick="saveTicketImage()">
            <i class="fas fa-download"></i> SAVE TICKET IMAGE
        </button>
    </div>

    <script>
        // Hide loading and show ticket when QR code is loaded
        function imageLoaded() {
            document.getElementById('qrImage').style.opacity = '1';

            // Hide loading screen
            document.getElementById('loading').style.opacity = '0';
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';

                // Show ticket with animation
                document.getElementById('ticket').classList.add('visible');
            }, 500);
        }

        // Fallback in case image fails to load
        setTimeout(() => {
            if (document.getElementById('loading').style.display !== 'none') {
                imageLoaded(); // Force show if still loading after 5 seconds
            }
        }, 5000);

        function saveTicketImage() {
            const ticket = document.getElementById('ticket');
            const filename = 'trio-outsiders-ticket-' + '<?php echo htmlspecialchars($user['nickname']); ?>' + '.png';

            html2canvas(ticket, {
                useCORS: true,
                allowTaint: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = filename;
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    </script>
</body>
</html>
