<?php
require_once "auth.php";
requireAdmin();
require_once "dbFuncs.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$uid = isset($data['user_id']) ? (int) $data['user_id'] : 0;
$desc = isset($data['description']) ? trim($data['description']) : '';
$amount = isset($data['amount']) ? (float) $data['amount'] : 0;
$due = isset($data['due_date']) ? $data['due_date'] : '';

if (!$uid || !$desc || $amount <= 0 || !$due) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
    exit;
}

try {
    $pdo = connectDB();

    // Verify client exists
    $check = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'client'");
    $check->execute([$uid]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Client not found.']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO invoices (user_id, description, amount, due_date, status)
        VALUES (?, ?, ?, ?, 'unpaid')
    ");
    $stmt->execute([$uid, $desc, $amount, $due]);

    echo json_encode(['success' => true, 'invoice_id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}