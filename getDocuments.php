<?php
require "dbFuncs.php";

$pdo = connectDB();

session_start();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM documents WHERE user_id = :user_id ORDER BY doc_id DESC");
$stmt->execute(['user_id' => $user_id]);

$docs = $stmt->fetchAll();

echo json_encode($docs);
?>