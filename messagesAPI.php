<?php
session_start();
require_once "dbFuncs.php";

$pdo = connectDB();

$user_id = $_SESSION["user_id"];
$role    = $_SESSION["role"];

$action = $_GET['action'] ?? '';

/* =========================
   GET ADMIN ID (CLIENT ONLY)
========================= */
if ($action === "getAdmin") {
    $stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1");
    $row  = $stmt->fetch();
    echo json_encode(["admin_id" => $row["user_id"]]);
    exit;
}

/* =========================
   GET ALL USERS (ADMIN ONLY)
========================= */
if ($action === "users") {
    if ($role !== "admin") {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden"]);
        exit;
    }

    $stmt = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'client'");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

/* =========================
   GET MESSAGES
========================= */
if ($action === "get" || $action === "getMessages") {
    if ($role === "admin") {
        // Admin fetches conversation with a specific client
        $other = intval($_GET['user_id']);

        $stmt = $pdo->prepare("
            SELECT *,
                   (sender_id = ?) AS is_me
            FROM messages
            WHERE (sender_id = ? AND receiver_id = ?)
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY sent_at ASC
        ");
        $stmt->execute([$user_id, $user_id, $other, $other, $user_id]);

    } else {
        // Client fetches their conversation with a specific admin
        $other = intval($_GET['user_id']);

        $stmt = $pdo->prepare("
            SELECT *,
                   (sender_id = ?) AS is_me
            FROM messages
            WHERE (sender_id = ? AND receiver_id = ?)
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY sent_at ASC
        ");
        $stmt->execute([$user_id, $user_id, $other, $other, $user_id]);
    }

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

/* =========================
   SEND MESSAGE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data    = json_decode(file_get_contents("php://input"), true);
    $content = trim($data["content"] ?? "");

    if ($content === "") {
        http_response_code(400);
        echo json_encode(["error" => "Empty message"]);
        exit;
    }

    if ($role !== "admin") {
        // Clients always send to the admin — ignore any receiver_id from the request
        $stmt     = $pdo->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1");
        $receiver = intval($stmt->fetch()["user_id"]);
    } else {
        $receiver = intval($data["receiver_id"] ?? 0);
        if ($receiver === 0) {
            http_response_code(400);
            echo json_encode(["error" => "No receiver specified"]);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, content, sent_at, read_status)
        VALUES (?, ?, ?, NOW(), 0)
    ");
    $stmt->execute([$user_id, $receiver, $content]);

    echo json_encode(["status" => "success"]);
    exit;
}

http_response_code(400);
echo json_encode(["error" => "Invalid request"]);