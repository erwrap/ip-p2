<?php
require "dbFuncs.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if (!$name || !$email || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required fields"
    ]);
    exit;
}

try {
    $pdo = connectDB();

    $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        echo json_encode([
            "status" => "error",
            "message" => "Email already exists"
        ]);
        exit;
    }

    $hashed = sha1($password);

    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, phone, role, created_at)
        VALUES (?, ?, ?, ?, 'client', NOW())
    ");

    $stmt->execute([$name, $email, $hashed, $phone]);

    echo json_encode([
        "status" => "success"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>