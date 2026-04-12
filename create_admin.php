<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';

echo "<pre>";
try {
    $pdo = db();

    $name = 'System Admin';
    $email = 'admin@gmail.com'; // Change to your preferred admin email
    $password = 'Admin@123';  // Change to your preferred admin password
    $phone = '1234567890';

    // Hash the password securely using PHP standard
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    
    if ($check->fetch()) {
        echo " Admin with this email already exists!\n";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role, phone, status)
            VALUES (?, ?, ?, 'admin', ?, 'approved')
        ");
        $stmt->execute([$name, $email, $passwordHash, $phone]);

        echo " Admin user created successfully!\n";
        echo "Email: {$email}\n";
        echo "Password: {$password}\n";
    }
} catch (Exception $e) {
    echo " Error: " . $e->getMessage() . "\n";
}
echo "</pre>";