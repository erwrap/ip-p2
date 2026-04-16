<?php
require "dbFuncs.php";

header('Content-Type: application/json');

$pdo = connectDB();

$data = json_decode(file_get_contents("php://input"), true);

$doc_id = $data['doc_id'] ?? null;
$note = $data['note'] ?? '';

if (!$doc_id) {
    echo json_encode(["status" => "error", "message" => "Missing doc_id"]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE documents
    SET admin_notes = ?
    WHERE doc_id = ?
");

$stmt->execute([$note, $doc_id]);

echo json_encode(["status" => "success"]);
?>