<?php
require "dbFuncs.php";

header('Content-Type: application/json');

$pdo = connectDB();

$stmt = $pdo->query("
    SELECT 
        d.doc_id,
        d.user_id,
        d.filename,
        d.category,
        d.upload_date,
        d.admin_notes,
        u.full_name AS client_name
    FROM documents d
    LEFT JOIN users u ON d.user_id = u.user_id
    ORDER BY d.upload_date DESC
");

$docs = $stmt->fetchAll();

echo json_encode($docs);
?>