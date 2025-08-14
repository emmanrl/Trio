<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

try {
    // Check if the form was submitted via POST
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method.");
    }

    // 1. Validate required fields
    $required = ['nickname', 'table_type', 'amount'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            header("Location: index.php?error=missing_fields");
            exit();
        }
    }

    // 2. Sanitize and store input
    $nickname = trim($_POST['nickname']);
    $tableType = $_POST['table_type'];
    $submittedAmount = (float)$_POST['amount'];
    
    // 3. Check for symbols in the nickname
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nickname)) {
        header("Location: index.php?error=nickname_symbols");
        exit();
    }

    // 4. Check if user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $userCount = $stmt->fetchColumn();

    if ($userCount > 0) {
        header("Location: index.php?error=nickname_exists");
        exit();
    }
    
    // 5. Validate table type and set number of people
    $validTables = ['regular', 'silver', 'gold'];
    if (!in_array($tableType, $validTables)) {
        header("Location: index.php?error=invalid_table");
        exit();
    }
    
    $numPeople = [
        'regular' => 1,
        'silver' => 4,
        'gold' => 6
    ][$tableType];
    
    // 6. Generate unique access code
    $accessCode = generateUniqueCode($pdo);

    // 7. Insert into database using a prepared statement
    $stmt = $pdo->prepare("INSERT INTO users 
                          (nickname, access_code, table_type, num_people, amount, payment_status, created_at) 
                          VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([
        $nickname,
        $accessCode,
        $tableType,
        $numPeople,
        $submittedAmount
    ]);

    // 8. Redirect to success page
    header("Location: registration_success.php?code=" . urlencode($accessCode));
    exit();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: index.php?error=database_error");
    exit();
} catch (Exception $e) {
    // This is for unexpected errors, like an invalid request method
    header("Location: index.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
