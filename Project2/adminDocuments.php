<?php
require_once "auth.php";
requireAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin — Documents</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body { font-family: Arial; font-size: 14px; }

.header {
  background: #1d5a55;
  color: white;
  padding: 25px;
  display: flex;
  justify-content: space-between;
}

.navbar {
  display: flex;
  border-bottom: 2px solid #1d5a55;
}

.navbar a {
  padding: 10px;
  background: #f5f5f5;
  border: 1px solid #ccc;
  text-decoration: none;
  color: #333;
}

.navbar a.active {
  background: #1d5a55;
  color: white;
}

.spacer { flex: 1; }

.content {
  display: flex;
  gap: 20px;
  padding: 20px;
}

.left { flex: 1; }

.right {
  width: 350px;
  border: 1px solid #ccc;
  padding: 15px;
  background: #f0faf8;
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
}

td {
  border: 1px solid #ddd;
  padding: 8px;
}

tr:hover {
  background: #f9fefd;
  cursor: pointer;
}

tr.selected {
  background: #eaf7f5;
}

textarea {
  width: 100%;
  height: 100px;
  margin-top: 10px;
}

button {
  margin-top: 10px;
  padding: 8px;
  width: 100%;
  background: #2a8a7e;
  color: white;
  border: none;
  cursor: pointer;
}

button:hover {
  background: #1d7a70;
}
</style>
</head>

<body>

<div class="header">
  <h2>Admin Panel — Documents</h2>
  <div>Admin</div>
</div>

<nav class="navbar">
  <a href="adminDashboard.php">Dashboard</a>
  <a href="#" class="active">Documents</a>

  <div class="spacer"></div>

  <a href="clientDashboard.php">Switch to Client View</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="content">

  <div class="left">
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Filename</th>
          <th>Category</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>
  </div>

  <div class="right">
    <div id="noSelect">Select a document</div>

    <div id="form" style="display:none;">
      <p><b id="docName"></b></p>
      <p id="docMeta"></p>

      <textarea id="note"></textarea>

      <button onclick="saveNote()">Save Note</button>
      <button onclick="viewDoc()">View File</button>
    </div>
  </div>

</div>

<script>

let docs = [];
let selected = null;

function loadDocs() {
  fetch("getAdminDocuments.php")
    .then(res => res.json())
    .then(data => {
      docs = data;
      renderTable();
    });
}

function renderTable() {
  const body = document.getElementById("tableBody");
  body.innerHTML = "";

  docs.forEach(d => {
    body.innerHTML += `
      <tr onclick="selectDoc(${d.doc_id})">
        <td>${d.client_name || ("User #" + d.user_id)}</td>
        <td>${d.filename}</td>
        <td>${d.category}</td>
        <td>${d.upload_date}</td>
        <td>${d.admin_notes ? "✔ Annotated" : "Pending"}</td>
      </tr>
    `;
  });
}

function selectDoc(id) {
  selected = docs.find(d => d.doc_id == id);

  document.getElementById("noSelect").style.display = "none";
  document.getElementById("form").style.display = "block";

  document.getElementById("docName").innerText = selected.filename;

  document.getElementById("docMeta").innerText =
    "Client: " + (selected.client_name || selected.user_id) +
    " | Category: " + selected.category;

  document.getElementById("note").value = selected.admin_notes || "";
}

function saveNote() {
  fetch("saveAnnotation.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({
      doc_id: selected.doc_id,
      note: document.getElementById("note").value
    })
  })
  .then(res => res.json())
  .then(() => {
    alert("Saved!");
    loadDocs();
  });
}

function viewDoc() {
  window.open("viewDoc.php?id=" + selected.doc_id);
}

loadDocs();

</script>

</body>
</html>