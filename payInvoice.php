<?php
require_once "auth.php";
requireLogin();
require_once "dbFuncs.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$invoice_id = isset($data['invoice_id']) ? (int) $data['invoice_id'] : 0;
$user_id = $_SESSION['user_id'];

if (!$invoice_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid invoice ID.']);
    exit;
}

try {
    $pdo = connectDB();

    // Verify the invoice belongs to the logged-in user and is unpaid
    $stmt = $pdo->prepare("
        SELECT invoice_id FROM invoices
        WHERE invoice_id = ? AND user_id = ? AND status = 'unpaid'
    ");
    $stmt->execute([$invoice_id, $user_id]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found or already paid.']);
        exit;
    }

    // Mark as paid
    $upd = $pdo->prepare("
        UPDATE invoices SET status = 'paid' WHERE invoice_id = ? AND user_id = ?
    ");
    $upd->execute([$invoice_id, $user_id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}