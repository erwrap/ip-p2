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
			<h1>Welcome Back</h1>
			<form action="./login.php" method="POST">
				<label for="email"><b>Email Address</b></label>
				<input type="text" id="email" name="email" placeholder="user@example.com">
				<label for="password"><b>Password</b></label>
				<input type="password" id="password" name="password"><br>
				<input type="submit" value="Login">
			</form>
			<?php
				require_once "dbFuncs.php";
				$emailSet = isset($_POST["email"]);
				$passwordSet = isset($_POST["password"]);
				if (!$emailSet || !$passwordSet) return;
				$email = $_POST["email"];
				$password = $_POST["password"];
				$pdo = connectDB();
				$query = "SELECT * FROM users WHERE email=:email AND password=:password";
				$stmt = $pdo->prepare($query);
				$stmt->execute(array('email' => $email, 'password' => $password));
				$result = $stmt->fetchAll();
				if (count($result) == 0) {
					echo "<h3 class='danger'>Login failed!</h3>";
					return;
				}
				session_start();
				$_SESSION["email"] = $result[0]["email"];
				$semail = $result[0]["email"];
				//echo '$_SESSION["email"] = ' . "$semail";
				$_SESSION["role"] = $result[0]["role"];
				$isAdmin = $result[0]["role"] === "admin";
				//echo "Results: $result";
				$headerString = "Location: " . ($isAdmin ? "adminDashboard.php" : "clientDashboard.php");
				echo "$headerString";
				header($headerString);
			?>
			<a href="./register.html">Don't have an account? Register here</a>
		</div>
	</body>
</html>
