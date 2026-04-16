<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();

    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        header("Location: clientDashboard.php");
        exit;
    }
}
?>