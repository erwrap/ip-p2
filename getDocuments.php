<?php
require "dbFuncs.php";

$pdo = connectDB();

$stmt = $pdo->query("SELECT * FROM documents ORDER BY doc_id DESC");
$docs = $stmt->fetchAll();

echo json_encode($docs);
?>