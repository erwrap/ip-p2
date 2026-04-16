<?php
session_start();
require_once "dbFuncs.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

$email = $input["email"] ?? "";
$password = $input["password"] ?? "";

if (!$email || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "Please enter email and password"
    ]);
    exit;
}

try {
    $pdo = connectDB();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email or password"
        ]);
        exit;
    }

    if (sha1($password) !== $user["password"]) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid email or password"
        ]);
        exit;
    }

    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["role"] = $user["role"];
    $_SESSION["email"] = $user["email"];

    $redirect = ($user["role"] === "admin")
        ? "adminDashboard.php"
        : "clientDashboard.php";

    echo json_encode([
        "status" => "success",
        "redirect" => $redirect
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error"
    ]);
}