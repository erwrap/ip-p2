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
  <title>Client Dashboard</title>
  <link rel="stylesheet" href="css/clientNavbar.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

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
    table td { padding: 7px 10px; border: 1px solid #ddd; }
    table a { color: #1d7a70; text-decoration: underline; cursor: pointer; margin-right: 4px; font-size: 12px; }

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
  </style>
</head>
<body>
  <div class="header">
    <h2>K&B Bookkeeping — Client Dashboard</h2>
  </div>
  

  <nav class="navbar">
    <a href="#" class="active">Dashboard</a>
    <a href="requests.php">Service Requests</a>
    <a href="documents.php">Documents</a>
    <a href="#">Invoices</a>
    <a href="messages.php">Messages</a>
    <div class="spacer"></div>
	<?php
		if (isAdmin()) {
			echo "<a href=\"adminDashboard.php\">Switch to Admin View</a>";
		}
	?>
    <a href="logout.php">Logout</a>
  </nav>

  <div class="content">

    <div class="cards">
      <div class="card">
        <div class="num"><?php
			$pdo = connectDB();
			$qry = $pdo->prepare("SELECT count(DISTINCT request_id) FROM service_requests WHERE user_id = ? and status = \"pending\"");
			$qry->execute(array($user_id));
			$result = $qry->fetchAll();
			echo end($result[0]);
		?></div>
        <div class="label">Open Requests</div>
      </div>
      <div class="card">
        <div class="num">2</div>
        <div class="label">Unpaid Invoices</div>
      </div>
      <div class="card">
        <div class="num">5</div>
        <div class="label">Uploaded Docs</div>
      </div>
      <div class="card">
        <div class="num"><?php
			$qry = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE receiver_id = ? AND read_status = 0");
			$qry->execute(array($user_id));
			$result = $qry->fetch(PDO::FETCH_ASSOC);
			echo $result['unread_count'];
		?></div>
        <div class="label">Unread Messages</div>
      </div>
    </div>

    <p class="section-label">Recent Service Requests</p>
	<?php
	$qry = $pdo->query("SELECT request_id, name, notes, priority, status, created_at FROM service_requests JOIN services ON service_requests.service_id = services.service_id WHERE user_id = $user_id ORDER BY service_requests.created_at DESC LIMIT 5 ");
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
<!--
    <table>
      <thead>
        <tr>
          <th>Service Type</th>
          <th>Date Submitted</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Monthly Bookkeeping</td>
          <td>03/01/2026</td>
          <td>In Progress</td>
          <td><a href="#">[ View ]</a> <a href="#">[ Edit ]</a> <a href="#">[ Delete ]</a></td>
        </tr>
        <tr>
          <td>Clean Up</td>
          <td>02/15/2026</td>
          <td>Completed</td>
          <td><a href="#">[ View ]</a></td>
        </tr>
        <tr>
          <td>Payroll</td>
          <td>03/10/2026</td>
          <td>Pending</td>
          <td><a href="#">[ View ]</a> <a href="#">[ Edit ]</a> <a href="#">[ Delete ]</a></td>
        </tr>
      </tbody>
    </table>
-->
    <p class="section-label">Monthly Expense Chart <u>(Chart.js via JS)</u></p>
    <div class="chart-box">
      [ Bar/Line Chart — Monthly Income vs. Expenses — Rendered by chart.js ]
    </div>

  </div>

</body>
</html>