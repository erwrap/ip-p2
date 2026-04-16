<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="./css/style.css">
		<title>Login</title>
		<!-- magic gfonts imports -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
		<!-- /gfonts -->
	</head>
	<body>
		<div class="login-card">
			<h1>Create New Account</h1>
			<form action="./register.php" method="POST">
				<label for="email"><b>Email Address</b></label>
				<input type="text" id="email" name="email" placeholder="user@example.com">
				<label for="password"><b>Password</b></label>
				<input type="password" id="password" name="password">
				<label for="name"><b>Full Name</b></label>
				<input type="text" id="name" name="name">
				<label for="phone"><b>Phone Number</b></label>
				<input type="phone" name="phone"><br>
				<input type="submit" value="Submit">
			</form>
		</div>
	</body>
	<?php
		require_once "dbFuncs.php";
		if (!isset($_POST["email"]) || !isset($_POST["password"])) return;
		// echo "woa";
		$name = $_POST["name"];
		$email = $_POST["email"];
		$password = $_POST["password"];
		$phone = $_POST["phone"];
		$pdo = connectDB();
		$query = "INSERT INTO users (full_name, email, password, phone, role) VALUES (:name, :email, :password, :phone, :role)";
		$stmt = $pdo->prepare($query);
		$stmt->execute(["name"=>$name,"email"=>$email,"password"=>$password,"phone"=>$phone,"role"=>"client"]);
		
	?>
</html>
