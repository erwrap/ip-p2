<?php
require "dbFuncs.php";

header('Content-Type: application/json');

$pdo = connectDB();

if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No file received"
    ]);
    exit;
}
session_start();

$file = $_FILES['file'];
$user_id = $_SESSION['user_id'] ?? 0;
$category = $_POST['category'] ?? '';
$note = $_POST['note'] ?? '';

if (!$user_id || !$category) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$filename = time() . "_" . basename($file["name"]);
$fullPath = $uploadDir . $filename;
$relativePath = "uploads/" . $filename;

if (!move_uploaded_file($file["tmp_name"], $fullPath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to move file"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO documents
    (user_id, filename, filepath, file_size, category, admin_notes, upload_date)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");

$stmt->execute([
    $user_id,
    $filename,
    $relativePath,
    $file["size"],
    $category,
    $note
]);

echo json_encode([
    "status" => "success",
    "doc_id" => $pdo->lastInsertId()
]);
?>
