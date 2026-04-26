<?php
require_once "dbFuncs.php";
require_once "auth.php";
requireLogin();


header('Content-Type: application/json');

$pdo = connectDB();

$userid = $_SESSION["user_id"];
$notes = $_POST['notes'] ?? "";
$type = $_POST['type'] ?? -1;
$priority = $_POST['priority'] ?? "";

if ($type == -1) {
    echo json_encode(["status" => "error", "message" => "Missing service type"]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO service_requests
    (user_id, service_id, notes, priority, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, NOW(), NOW())
");

$stmt->execute([
    $userid,
	$type,
	$notes,
	$priority,
	"pending"
]);

echo json_encode([
    "status" => "success",
    "doc_id" => $pdo->lastInsertId()
]);
?>
