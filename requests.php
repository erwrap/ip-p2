<?php
require_once "auth.php";
require_once "dbFuncs.php";
requireLogin();
if (!isset($_SESSION)) { session_start(); }
$user_id = $_SESSION["user_id"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Service Requests</title>
  <link rel="stylesheet" href="css/clientNavbar.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

	input { font-family: Arial, sans-serif; font-size: 13px; padding: 7px; width: 100%; }

    .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 24px; }
    .card {
      background: #d6f0ec;
      border: 1px solid #a8d8d0;
      padding: 14px 16px;
    }
    .card .num { font-size: 24px; color: #1d7a70; }
    .card .label { font-size: 12px; color: #1d7a70; margin-top: 6px; }

    .section-label { font-size: 13px; font-weight: bold; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    table th { background: #d6f0ec; color: #1d7a70; text-align: left; padding: 8px 10px; border: 1px solid #a8d8d0; }
    table td, table td select { padding: 7px 10px; border: 1px solid #ccc; }
	table td select { height: 100%; width: 100%; padding: 7px; }
    table a { color: #1d7a70; text-decoration: underline; cursor: pointer; margin-right: 4px; font-size: 12px; }

	.new-request tr th { width: 25%; }

    .chart-box {
      background: #f5f5f5;
      border: 1px dashed #a8d8d0;
      height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #888;
      font-style: italic;
      font-size: 13px;
    }
	button {
		background: #eee; padding: 8px 10px; border: 1px solid #ddd; margin-bottom: 24px;
	}
	button:hover {
		background: #ccc;
	}
	button:active {
		background: #ded;
	}
  </style>
  <script>
	function submitSR() {
		let notes = document.getElementById("notes").value;
		let type = document.getElementById("stype").value;
		let priority = document.getElementById("priority").value;

		const formData = new FormData();
		formData.append("notes", notes);
		formData.append("type", type);
		formData.append("priority", priority);

		fetch("submitServiceRequest.php", {
			method: "POST",
			body: formData
		})
		.then(res => res.json())
		.then(res => {
			if (res.status === "success") {
				alert("Upload successful!");
      			document.getElementById("notes").value = "";
		    	document.getElementById("stype").value = "-1";
		    	document.getElementById("priority").value = "normal";
    } else {
      alert(res.message || "Upload failed");
    }
  });

	}
  </script>
</head>
<body>
  <div class="header">
    <h2>K&B Bookkeeping — Service Requests</h2>
  </div>
  

  <nav class="navbar">
    <a href="clientDashboard.php">Dashboard</a>
    <a href="#" class="active">Service Requests</a>
    <a href="documents.php">Documents</a>
    <a href="invoices.php">Invoices</a>
    <a href="messages.php">Messages</a>
    <div class="spacer"></div>
    <a href="logout.php">Logout</a>
  </nav>

  <div class="content">
	<p class="section-label">Submit a new Service Request</p>
	<?php
	$pdo = connectDB();
	$qry = $pdo->query("SELECT service_id, name FROM services");
	echo "<table class=\"new-request\">";
	echo "<tr><th>Service Type</th>";
	echo "<td><select name=\"stype\" id=\"stype\">";
	echo "<option value='-1' disabled selected>&lt;Select a service type&gt;</option>";
	while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
		$id = $row['service_id'];
		$name = $row['name'];
		echo "<option value=\"$id\">$name</option>";
	}
	echo "</select></td><tr>";
	echo "<th>Description / Notes</th>";
	echo "<td><input type=\"text\" placeholder=\"Describe your issue...\" name=\"notes\" id=\"notes\"></input>";
	echo "</tr><tr>";
	echo "<th>Priority</th>";
	echo "<td><select name=\"priority\" id=\"priority\">";
	echo "<option value=\"normal\">Normal</option>";
	echo "<option value=\"urgent\">Urgent</option>";
	echo "</select></td></tr></table>";
?>
<button type="button" onclick="submitSR()">Submit</button>
<p class="section-label">Recent Service Requests</p>
<?php
	$qry = $pdo->query("SELECT request_id, name, notes, priority, status, created_at FROM service_requests JOIN services ON service_requests.service_id = services.service_id WHERE user_id = $user_id");
	echo "<table><thead><tr>";
	echo "<th>Service ID</th><th>Service Type</th><th>Notes</th><th>Date Submitted</th><th>Status</th><th>Action</th>";
	echo "</tr></thead><tbody>";
	while ($row = $qry->fetch(PDO::FETCH_ASSOC)) {
		$id = $row['request_id'];
		$status = $row['status'];
		$name = $row['name'];
		$created_at = $row['created_at'];
		$notes = $row['notes'];
		echo "<tr>";
		echo "<td>$id</td>";
		echo "<td>$name</td>";
		echo "<td>$notes</td>";
		echo "<td>$created_at</td>";
		echo "<td>$status</td>";
		echo "<td>:(</td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
?>

    <p class="section-label">Monthly Expense Chart <u>(Chart.js via JS)</u></p>
    <div class="chart-box">
      [ Bar/Line Chart — Monthly Income vs. Expenses — Rendered by chart.js ]
    </div>

  </div>

</body>
</html>
