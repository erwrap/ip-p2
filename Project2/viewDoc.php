<?php
require "dbFuncs.php";

$pdo = connectDB();

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT filename FROM documents WHERE doc_id = ?");
$stmt->execute([$id]);

$row = $stmt->fetch();

if ($row) {
    $file = "uploads/" . $row['filename'];

    if (file_exists($file)) {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: inline; filename=\"" . basename($file) . "\"");
        readfile($file);
        exit;
    }
}

echo "File not found.";
?>