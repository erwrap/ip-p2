<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.html");
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

function isAdmin() {
	return (isset($_SESSION["role"]) && $_SESSION["role"] === "admin");
}
?>
