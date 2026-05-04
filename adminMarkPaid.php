<?php
require_once "auth.php";
requireAdmin();
require_once "dbFuncs.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$invoice_id = isset($data['invoice_id']) ? (int) $data['invoice_id'] : 0;

if (!$invoice_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID.']);
    exit;
}

try {
    $pdo = connectDB();

    $stmt = $pdo->prepare("
        UPDATE invoices SET status = 'paid'
        WHERE invoice_id = ? AND status = 'unpaid'
    ");
    $stmt->execute([$invoice_id]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found or already paid.']);
        exit;
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}