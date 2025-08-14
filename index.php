<?php
require 'includes/config.php';

// Calculate price based on table selection
function calculatePrice($tableType) {
    $prices = [
        'regular' => 10000,  // ₦10,000 (no table)
        'silver' => 50000,   // ₦50,000 for 4 people
        'gold' => 70000      // ₦70,000 for 6 people
    ];
    return $prices[$tableType] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trio Outsyders - Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        body {
            font-family: var(--font-inter);
            background: var(--background-dark);
            color: var(--text-color);
        }

        .hero-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-bottom: 5px solid var(--primary);
        }

        .card {
            background: var(--background-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header-text {
            font-family: var(--font-anton);
            text-transform: uppercase;
        }

        .table-option {
            transition: all 0.2s ease;
            cursor: pointer;
            border: 2px solid transparent;
            background-color: #333333;
        }

        .table-option.selected {
            border-color: var(--secondary);
            background-color: rgba(255, 51, 119, 0.1); /* Lighter pink background */
            box-shadow: 0 0 10px var(--secondary);
        }

        .price-text {
            font-family: var(--font-anton);
            color: var(--secondary);
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

        .input-field {
            background-color: #1a1a1a;
            border: 1px solid #444444;
            color: var(--text-color);
        }

        .input-field:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(255, 51, 119, 0.5);
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
        
        .status-confirmed { color: #4caf50; }
        .status-pending { color: #ff9800; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg animate__animated animate__fadeIn">
        <div class="hero-bg text-center py-10 px-6 rounded-t-xl">
            <h1 class="header-text text-5xl md:text-6xl text-white drop-shadow-md">
                TRIO OUTSYDERS
            </h1>
            <p class="text-sm md:text-base text-gray-200 mt-2">THE NIGHT NEVER SLEEPS</p>
        </div>
        
        <div class="card rounded-b-xl overflow-hidden shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold header-text text-white">SECURE YOUR SPOT</h2>
            </div>
            <?php
$errorMessage = '';
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    switch ($error) {
        case 'missing_fields':
            $errorMessage = "Please fill in all required fields.";
            break;
        case 'nickname_symbols':
            $errorMessage = "Nickname can only contain letters, numbers, and underscores.";
            break;
        case 'nickname_exists':
            $errorMessage = "This nickname is already taken. Please choose another one.";
            break;
        case 'invalid_table':
            $errorMessage = "Invalid table option selected.";
            break;
        case 'database_error':
            $errorMessage = "An internal error occurred. Please try again.";
            break;
        default:
            $errorMessage = "An unknown error occurred.";
            break;
    }
}
if ($errorMessage) {
    echo '<div class="bg-red-500 text-white p-3 rounded-lg text-center mb-4">' . htmlspecialchars($errorMessage) . '</div>';
}
?>

            <form method="post" action="process_registration.php" class="space-y-6">
                <div>
                    <label for="nickname" class="block text-sm font-bold text-gray-300 mb-2">
                        <i class="fas fa-user mr-2 text-secondary"></i>YOUR PARTY ALIAS
                    </label>
                    <input 
                        type="text" 
                        id="nickname" 
                        name="nickname" 
                        required
                        class="input-field w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300"
                        placeholder="e.g. party_king">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-3">
                        <i class="fas fa-chair mr-2 text-secondary"></i>SELECT YOUR OPTION
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div 
                            id="regular-option"
                            class="table-option p-4 rounded-lg"
                            onclick="selectOption('regular')">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-white header-text text-lg">Regular</h3>
                                <span class="text-xs bg-gray-600 text-gray-300 px-2 py-1 rounded-full">1 Person</span>
                            </div>
                            <div class="mt-2 text-3xl font-bold price-text">₦10,000</div>
                        </div>
                        
                        <div 
                            id="silver-option"
                            class="table-option p-4 rounded-lg"
                            onclick="selectOption('silver')">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-white header-text text-lg">Silver</h3>
                                <span class="text-xs bg-gray-600 text-gray-300 px-2 py-1 rounded-full">4 People</span>
                            </div>
                            <div class="mt-2 text-3xl font-bold price-text">₦50,000</div>
                        </div>
                        
                        <div 
                            id="gold-option"
                            class="table-option p-4 rounded-lg"
                            onclick="selectOption('gold')">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-white header-text text-lg">Gold</h3>
                                <span class="text-xs bg-gray-600 text-gray-300 px-2 py-1 rounded-full">6 People</span>
                            </div>
                            <div class="mt-2 text-3xl font-bold price-text">₦70,000</div>
                        </div>
                    </div>
                    <input type="hidden" id="table_type" name="table_type" value="">
                </div>
                
                <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 font-bold">TOTAL AMOUNT:</span>
                        <span id="amount" class="text-4xl price-text font-bold">₦0</span>
                    </div>
                    <input type="hidden" id="amount_value" name="amount" value="">
                    <input type="hidden" id="num_people" name="num_people" value="1">
                </div>
                
                <button 
                    type="submit" 
                    id="submit-btn"
                    class="btn-primary w-full py-4 px-4 rounded-lg font-bold text-white text-xl disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-ticket-alt mr-2"></i>SECURE YOUR SPOT
                </button>
            </form>
            
            <div class="text-center mt-6">
                <button 
                    id="check-payment-btn"
                    class="text-secondary font-bold hover:underline"
                    onclick="openPaymentModal()">
                    Check My Payment Status
                </button>
            </div>
        </div>
    </div>
    
    <div id="payment-modal" class="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" onclick="closePaymentModal()">&times;</button>
            <div class="modal-content">
                <h3>Check Payment Status</h3>
                <form id="payment-check-form" class="space-y-4">
                    <div>
                        <label for="check_nickname" class="block text-sm font-bold text-gray-300 mb-2">
                            Enter your Nickname
                        </label>
                        <input 
                            type="text" 
                            id="check_nickname" 
                            name="nickname" 
                            required
                            class="input-field w-full px-4 py-3 rounded-lg focus:outline-none transition-all duration-300"
                            placeholder="e.g. party_king">
                    </div>
                    <button 
                        type="submit" 
                        class="btn-primary w-full py-3 px-4 rounded-lg font-bold text-white">
                        Check Status
                    </button>
                </form>
                <div id="payment-result" class="mt-4 text-center"></div>
            </div>
        </div>
    </div>

    <script>
        // ... (Your existing JavaScript remains the same) ...

        // Current selection
        let selectedOption = null;
        
        // Select option
        function selectOption(option) {
            selectedOption = option;
            
            // Update UI
            document.getElementById('regular-option').classList.remove('selected');
            document.getElementById('silver-option').classList.remove('selected');
            document.getElementById('gold-option').classList.remove('selected');
            document.getElementById(option + '-option').classList.add('selected');
            
            // Update hidden fields
            document.getElementById('table_type').value = option;
            
            // Set number of people based on selection
            const numPeople = {
                'regular': 1,
                'silver': 4,
                'gold': 6
            }[option];
            document.getElementById('num_people').value = numPeople;
            
            // Calculate amount
            calculateAmount();
            
            // Enable submit button
            document.getElementById('submit-btn').disabled = false;
        }
        
        // Calculate and display amount
        function calculateAmount() {
            if (!selectedOption) return;
            
            const prices = {
                'regular': 10000,
                'silver': 50000,
                'gold': 70000
            };
            
            const amount = prices[selectedOption];
            const amountField = document.getElementById('amount');
            const amountValueField = document.getElementById('amount_value');
            
            // Add animation
            amountField.classList.add('price-update');
            setTimeout(() => {
                amountField.classList.remove('price-update');
            }, 1000);
            
            // Update display
            amountField.textContent = '₦' + amount.toLocaleString('en-NG');
            amountValueField.value = amount;
        }
// ... (The entire HTML and CSS remains the same) ...
    // ... (Your existing JavaScript functions remain the same) ...
    // `selectOption` and `calculateAmount` are unchanged.

    // Modal functions
    function openPaymentModal() {
        document.getElementById('payment-modal').style.display = 'flex';
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').style.display = 'none';
        document.getElementById('payment-check-form').reset();
        document.getElementById('payment-result').innerHTML = '';
    }

    // AJAX Form Submission
    document.getElementById('payment-check-form').addEventListener('submit', function(event) {
        event.preventDefault();
        
        const form = this;
        const nickname = form.elements['nickname'].value;
        const resultDiv = document.getElementById('payment-result');
        
        resultDiv.innerHTML = '<i class="fas fa-spinner fa-spin text-secondary"></i> Checking...';
        
        fetch('check_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `nickname=${encodeURIComponent(nickname)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'found') {
                if (data.payment_status === 'confirmed') {
                    // Redirect to the ticket page with nickname and access code
                    window.location.href = `ticket.php?nickname=${encodeURIComponent(nickname)}&access_code=${encodeURIComponent(data.access_code)}`;
                } else {
                    resultDiv.innerHTML = `
                        <p class="text-lg font-bold status-pending">🕒 Payment Pending</p>
                        <p class="text-sm mt-2">We are still confirming your payment. Please check back later.</p>
                    `;
                }
            } else {
                resultDiv.innerHTML = `
                    <p class="text-lg text-red-500">Nickname not found.</p>
                    <p class="text-sm mt-2">Please ensure you entered the correct nickname.</p>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `<p class="text-red-500">An error occurred. Please try again.</p>`;
            console.error('Error:', error);
        });
    });
</script>
</body>
</html>
