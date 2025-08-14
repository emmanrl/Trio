<?php
function generateUniqueCode($pdo, $prefix = 'TR-', $length = 8) {
    $characters = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Removed ambiguous characters
    $maxAttempts = 100;
    $attempt = 0;
    
    do {
        // Generate random code
        $code = $prefix;
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Check if code exists in database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE access_code = ?");
        $stmt->execute([$code]);
        $count = $stmt->fetchColumn();
        
        $attempt++;
        if ($attempt >= $maxAttempts) {
            throw new Exception("Failed to generate unique code after $maxAttempts attempts");
        }
        
    } while ($count > 0);
    
    return $code;
}

function calculatePrice($tableType) {
    return [
        'regular' => 15000,  // ₦15,000 (no table)
        'silver' => 50000,   // ₦50,000 for 4 people
        'gold' => 70000      // ₦70,000 for 6 people
    ][$tableType];
}
?>