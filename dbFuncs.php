<?php
function connectDB() {
    
    $host = 'localhost';
    $db   = "kbbookkeeping";
    $user = "phpUser";
    $pwd  = "wi@[vL-rJ9R92dVz";
    
    /*
    $host = 'localhost';
    $db   = "u671912294_kbbookeeping";
    $user = "u671912294_phpUser2";
    $pwd  = "wi@[vL-rJ9R92dVz";
    */

    $attr = "mysql:host=$host;dbname=$db";
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ];

    try {
        $pdo = new PDO($attr, $user, $pwd, $opts);
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception($e->getMessage(), (int) $e->getCode());
    }
}
?>