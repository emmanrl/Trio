<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

requireAdminAuth();

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $userId = $_POST['user_id'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET payment_status = 'confirmed' WHERE id = ?");
        $stmt->execute([$userId]);
        header('Location: ' . ADMIN_BASE . '/index.php');
        exit();
    } catch (PDOException $e) {
        error_log("Payment confirmation error: " . $e->getMessage());
    }
}

// Fetch all users from the database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            --card-bg: #222;
            --font-anton: 'Anton', sans-serif;
            --font-inter: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-inter);
            background-color: var(--background-dark);
            color: var(--text-color);
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 51, 119, 0.3);
            flex-wrap: wrap;
        }
        
        h1 {
            font-family: var(--font-anton);
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            color: var(--secondary);
            text-shadow: 0 0 10px rgba(255, 51, 119, 0.3);
            text-transform: uppercase;
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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--background-light);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-container input, .filter-select {
            padding: 10px;
            background-color: #1a1a1a;
            border: 1px solid #444;
            color: var(--text-color);
            border-radius: 6px;
        }
        
        .search-container input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .user-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .user-card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .user-card:hover {
            background-color: #333;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-family: var(--font-anton);
            font-size: 1.5rem;
            color: var(--text-color);
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .card-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }
        
        .card-label {
            color: #ccc;
        }
        
        .card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        .confirm-btn, .more-info-btn {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 0.9rem;
        }
        
        .more-info-btn {
            background-color: #007bff;
        }

        .confirm-btn:hover { background-color: #45a049; }
        .more-info-btn:hover { background-color: #0056b3; }

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
            max-width: 500px;
            width: 90%;
            color: var(--text-color);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            color: #ccc;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .modal-content h3 {
            font-family: var(--font-anton);
            font-size: 1.8rem;
            color: var(--secondary);
            border-bottom: 2px solid rgba(255, 51, 119, 0.3);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .modal-content p {
            margin-bottom: 10px;
        }
        
        .modal-content strong {
            color: var(--secondary);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="controls">
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search by nickname...">
            </div>
            <div class="filter-container">
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                </select>
            </div>
        </div>

        <div class="user-list">
            <?php if (empty($users)): ?>
                <p style="text-align: center;">No users have registered yet.</p>
            <?php else: ?>
                <?php $sn = 1; ?>
                <?php foreach ($users as $user): ?>
                <div class="user-card" data-status="<?= htmlspecialchars($user['payment_status']) ?>" 
                     data-search="<?= strtolower(htmlspecialchars($user['nickname'])) ?>"
                     data-user-details="<?= htmlspecialchars(json_encode($user)) ?>">
                    <div class="card-header">
                        <span class="card-title"><?= htmlspecialchars($user['nickname']) ?></span>
                        <span class="status-badge status-<?= htmlspecialchars($user['payment_status']) ?>">
                            <?= htmlspecialchars(ucfirst($user['payment_status'])) ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="card-row">
                            <span class="card-label">S/N:</span>
                            <span><?= $sn++ ?></span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Plan:</span>
                            <span><?= htmlspecialchars(ucfirst($user['table_type'])) ?></span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Amount:</span>
                            <span>₦<?= htmlspecialchars(number_format($user['amount'], 2)) ?></span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button type="button" class="more-info-btn" onclick="openModal(this)">More Info</button>
                        <?php if ($user['payment_status'] === 'pending'): ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="confirm_payment" class="confirm-btn">Confirm Payment</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div id="infoModal" class="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <div class="modal-content">
                <h3 id="modalNickname"></h3>
                <p><strong>Access Code:</strong> <span id="modalAccessCode"></span></p>
                <p><strong>Plan:</strong> <span id="modalPlan"></span></p>
                <p><strong>Number of People:</strong> <span id="modalNumPeople"></span></p>
                <p><strong>Amount:</strong> <span id="modalAmount"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Registered On:</strong> <span id="modalCreatedAt"></span></p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById("searchInput").addEventListener("keyup", searchList);
        document.getElementById("statusFilter").addEventListener("change", filterList);

        function searchList() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const cards = document.querySelectorAll(".user-card");

            cards.forEach(card => {
                const nickname = card.dataset.search;
                const isFiltered = card.style.display === 'none';
                
                if (nickname.includes(input) && !isFiltered) {
                    card.style.display = 'flex';
                } else if (!nickname.includes(input) && !isFiltered) {
                    card.style.display = 'none';
                }
            });
        }
        
        function filterList() {
            const filter = document.getElementById("statusFilter").value;
            const cards = document.querySelectorAll(".user-card");

            cards.forEach(card => {
                const status = card.dataset.status;
                if (filter === "" || status === filter) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Re-apply search after filtering
            searchList();
        }

        function openModal(button) {
            const card = button.closest('.user-card');
            const userDetails = JSON.parse(card.dataset.userDetails);
            
            document.getElementById('modalNickname').textContent = userDetails.nickname;
            document.getElementById('modalAccessCode').textContent = userDetails.access_code;
            document.getElementById('modalPlan').textContent = userDetails.table_type;
            document.getElementById('modalNumPeople').textContent = userDetails.num_people;
            document.getElementById('modalAmount').textContent = '₦' + parseFloat(userDetails.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('modalStatus').textContent = userDetails.payment_status;
            document.getElementById('modalCreatedAt').textContent = userDetails.created_at;
            
            document.getElementById('infoModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('infoModal').style.display = 'none';
        }
    </script>
</body>
</html>
