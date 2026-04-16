<?php
require_once "auth.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Documents — K&B Bookkeeping</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: Arial, sans-serif;
  font-size: 14px;
  background: #fff;
  color: #222;
}

.header {
  display: flex;
  padding: 40px 15px;
  background-color: #2a8a7e;
  color: #fff;
}

.navbar {
  display: flex;
  border-bottom: 2px solid #2a8a7e;
}

.navbar a {
  display: block;
  padding: 10px 16px;
  text-decoration: none;
  color: #333;
  border: 1px solid #ccc;
  border-bottom: none;
  background: #f5f5f5;
  font-size: 13px;
}

.navbar a.active {
  background: #2a8a7e;
  color: #fff;
  border-color: #2a8a7e;
}

.navbar .spacer {
  flex: 1;
}

.content {
  padding: 20px;
}

.upload-box {
  border: 2px dashed #2a8a7e;
  background: #f0faf8;
  padding: 25px;
  text-align: center;
  cursor: pointer;
  margin-bottom: 15px;
}

.upload-box:hover {
  background: #e3f6f3;
}

#fileInput {
  display: none;
}

#fileName {
  margin: 8px 0;
  font-size: 12px;
  color: #1d7a70;
}

.row {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

select, input {
  padding: 7px;
  border: 1px solid #ccc;
  font-size: 13px;
}

button {
  padding: 8px 16px;
  background: #2a8a7e;
  color: white;
  border: none;
  cursor: pointer;
}

button:disabled {
  background: #aaa;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background: #d6f0ec;
  color: #1d7a70;
  padding: 8px;
  border: 1px solid #a8d8d0;
  text-align: left;
}

td {
  border: 1px solid #ddd;
  padding: 8px;
}

a.action {
  color: #1d7a70;
  cursor: pointer;
  margin-right: 8px;
}

a.delete {
  color: #c0392b;
}
</style>
</head>

<body>

<div class="header">
  <h1>K&B Bookkeeping — Documents</h1>
</div>

<nav class="navbar">
  <a href="clientDashboard.php">Dashboard</a>
  <a href="#">Service Requests</a>
  <a href="documents.php" class="active">Documents</a>
  <a href="#">Invoices</a>
  <a href="#">Messages</a>
  <div class="spacer"></div>
  <a href="#">Logout</a>
</nav>

<div class="content">

  <div class="upload-box" onclick="openPicker()">
    <p><b>Click to Upload File</b></p>
    <small>PDF, JPG, PNG, CSV, XLSX</small>

    <input type="file" id="fileInput">
  </div>

  <div id="fileName"></div>

  <div class="row">
    <select id="category">
      <option value="">Select Category</option>
      <option value="bank">Bank Statement</option>
      <option value="invoice">Invoice</option>
      <option value="payroll">Payroll</option>
      <option value="other">Other</option>
    </select>

    <input type="text" id="note" placeholder="Optional note">

    <button onclick="uploadFile()">Upload</button>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>File</th>
        <th>Category</th>
        <th>Date</th>
        <th>Notes</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody id="tableBody"></tbody>
  </table>

</div>

<script>

let selectedFile = null;

function openPicker() {
  document.getElementById("fileInput").click();
}

document.getElementById("fileInput").addEventListener("change", function(e) {
  selectedFile = e.target.files[0];

  document.getElementById("fileName").innerText =
    selectedFile ? "Selected: " + selectedFile.name : "";
});

function uploadFile() {
  const category = document.getElementById("category").value;
  const note = document.getElementById("note").value;

  if (!selectedFile || !category) {
    alert("Select file and category");
    return;
  }

  const formData = new FormData();
  formData.append("file", selectedFile);
  formData.append("category", category);
  formData.append("note", note);
  formData.append("user_id", 1);

  fetch("uploadDoc.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === "success") {
      alert("Upload successful!");
      loadDocs();

      document.getElementById("fileInput").value = "";
      document.getElementById("category").value = "";
      document.getElementById("note").value = "";
      document.getElementById("fileName").innerText = "";
      selectedFile = null;
    } else {
      alert(res.message || "Upload failed");
    }
  });
}

function loadDocs() {
  fetch("getDocuments.php")
    .then(res => res.json())
    .then(data => {
      const body = document.getElementById("tableBody");
      body.innerHTML = "";

      data.forEach((d, i) => {
        body.innerHTML += `
          <tr>
            <td>${i + 1}</td>
            <td>${d.filename}</td>
            <td>${d.category}</td>
            <td>${d.upload_date}</td>
            <td>${d.admin_notes || ""}</td>
            <td>
              <a class="action" onclick="viewDoc(${d.doc_id})">View</a>
              <a class="action delete" onclick="deleteDoc(${d.doc_id})">Delete</a>
            </td>
          </tr>
        `;
      });
    });
}

function viewDoc(id) {
  window.open("viewDoc.php?id=" + id, "_blank");
}

function deleteDoc(id) {
  fetch("deleteDoc.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ doc_id: id })
  })
  .then(() => loadDocs());
}

loadDocs();

</script>

</body>
</html>