<?php
require "dbFuncs.php";

header('Content-Type: application/json');

$pdo = connectDB();

$data = json_decode(file_get_contents("php://input"), true);
$doc_id = $data['doc_id'] ?? null;

if (!$doc_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing document ID"
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT filepath FROM documents WHERE doc_id = ?");
$stmt->execute([$doc_id]);
$file = $stmt->fetch();

if (!$file) {
    echo json_encode([
        "status" => "error",
        "message" => "File not found in database"
    ]);
    exit;
}

$fullPath = __DIR__ . "/" . $file['filepath'];

if (file_exists($fullPath)) {
    unlink($fullPath);
}

$stmt = $pdo->prepare("DELETE FROM documents WHERE doc_id = ?");
$stmt->execute([$doc_id]);

echo json_encode([
    "status" => "success",
    "message" => "File deleted successfully"
]);
?>