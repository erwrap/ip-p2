<?php
function connectDB() {
	$host="localhost";
	$db="kbbookkeeping";
	$user="phpUser";
	$pwd="u*d.Lj2umXsCUmAW";
	$attr="mysql:host=$host;dbname=$db";
	$opts=[
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => False
	];
	try {
		$pdo=new PDO($attr,$user,$pwd, $opts);
		return $pdo;
	}
	catch(PDOException $e) {
		throw new Exception($e -> getMessage(), (int) $e -> getCode());
	}
}
?>
