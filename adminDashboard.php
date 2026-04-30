<?php
require_once "auth.php";
requireAdmin();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard</title>
<link rel="stylesheet" href="css/clientNavbar.css">

<style>
:root {
	--nav-bg: #1d5a55;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.section-label {
  font-size: 13px;
  font-weight: bold;
  margin: 18px 0 8px;
  color: #1d5a55;
}


table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

th {
  background: #d6f0ec;
  color: #1d7a70;
  text-align: left;
  padding: 8px 10px;
  border: 1px solid #a8d8d0;
}

td {
  padding: 7px 10px;
  border: 1px solid #ddd;
  vertical-align: middle;
}

table tr:hover td {
  background: #f9fefd;
}

table a {
  color: #1d7a70;
  text-decoration: underline;
  cursor: pointer;
  font-size: 12px;
}

.btn {
  padding: 9px 16px;
  background: #2a8a7e;
  color: #fff;
  border: none;
  cursor: pointer;
  font-size: 13px;
  font-weight: bold;
  margin-top: 10px;
}

.btn:hover {
  background: #1d7a70;
}

input, select {
  padding: 7px;
  border: 1px solid #ccc;
  font-size: 13px;
  width: 100%;
}

.form-table td, .form-table th {
  vertical-align: middle;
}
</style>

</head>

<body>

<div class="header">
  <h2>K&B Bookkeeping — Admin Panel</h2>
</div>

<nav class="navbar">
  <a href="#" class="active">Dashboard</a>
  <a href="adminRequests.php">Service Requests</a>
  <a href="adminDocuments.php">Documents</a>
  <a href="adminInvoices.php">Invoices</a>
  <a href="adminMessages.php">Messages</a>

  <div class="spacer"></div>

  <a href="clientDashboard.php">Switch to Client View</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="content">

  <p class="section-label">Client List</p>

  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Joined</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Jane Smith</td>
        <td>jane@example.com</td>
        <td>(770) 555-1234</td>
        <td>01/15/2026</td>
        <td><a>[ View ]</a></td>
      </tr>
      <tr>
        <td>Bob Turner</td>
        <td>bob@acme.com</td>
        <td>(404) 555-9876</td>
        <td>02/03/2026</td>
        <td><a>[ View ]</a></td>
      </tr>
    </tbody>
  </table>

  <p class="section-label">Manage Service Requests</p>

  <table>
    <thead>
      <tr>
        <th>Client</th>
        <th>Service</th>
        <th>Notes</th>
        <th>Status</th>
        <th>Update</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Jane Smith</td>
        <td>Monthly Bookkeeping</td>
        <td>Uploaded bank statement</td>
        <td>In Progress</td>
        <td><a>[ Update ▼ ]</a></td>
      </tr>
    </tbody>
  </table>

  <p class="section-label">Create Invoice</p>

  <table class="form-table">
    <tbody>
      <tr>
        <th style="width:200px;">Select Client</th>
        <td>
          <select>
            <option>Jane Smith</option>
            <option>Bob Turner</option>
          </select>
        </td>
      </tr>

      <tr>
        <th>Description</th>
        <td><input type="text" placeholder="e.g. Monthly Bookkeeping"></td>
      </tr>

      <tr>
        <th>Amount ($)</th>
        <td><input type="text" value="175.00"></td>
      </tr>

      <tr>
        <th>Due Date</th>
        <td><input type="date"></td>
      </tr>
    </tbody>
  </table>

  <button class="btn">Create Invoice</button>

</div>

</body>
</html>
