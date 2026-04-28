<?php
require_once "auth.php";
requireAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin — Messages</title>
<link rel="stylesheet" href="css/clientNavbar.css">
<style>
:root {
	--nav-bg: #1d5a55;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: Arial, sans-serif;
  font-size: 14px;
  background: #fff;
  color: #222;
}

/* HEADER */




/* LAYOUT */
.container {
  display: flex;
  height: calc(100vh - 120px);
}

/* USERS SIDEBAR */
.users {
  width: 260px;
  border-right: 1px solid #ddd;
  overflow-y: auto;
  background: #fff;
}

.user {
  padding: 12px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
}

.user:hover {
  background: #f9fefd;
}

.user.active {
  background: #d6f0ec;
  color: #1d7a70;
  font-weight: bold;
}

/* CHAT AREA */
.chat {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
  background: #fff;
}

/* MESSAGE BUBBLES */
.msg {
  padding: 10px 14px;
  margin-bottom: 10px;
  max-width: 70%;
  border-radius: 6px;
  border: 1px solid #ddd;
}

.me {
  background: #1d5a55;
  color: #fff;
  margin-left: auto;
}

.them {
  background: #f5f5f5;
}

/* META TEXT */
.msg-meta {
  font-weight: bold;
  font-size: 12px;
  color: #1d7a70;
}

/* INPUT BAR */
.input-bar {
  display: flex;
  border-top: 2px solid #1d5a55;
}

.input-bar input {
  flex: 1;
  padding: 10px;
  border: none;
  outline: none;
  font-size: 13px;
}

.input-bar button {
  padding: 10px 20px;
  background: #2a8a7e;
  color: white;
  border: none;
  cursor: pointer;
  font-size: 13px;
  font-weight: bold;
}

.input-bar button:hover {
  background: #1d7a70;
}
</style>
</head>

<body>

<div class="header">
  <h2>K&B Bookkeeping — Admin Messages</h2>
</div>

<nav class="navbar">
  <a href="adminDashboard.php">Dashboard</a>
  <a href="#">Service Requests</a>
  <a href="adminDocuments.php">Documents</a>
  <a href="#">Invoices</a>
  <a href="adminMessages.php" class="active">Messages</a>

  <div class="spacer"></div>

  <a href="messages.php">Switch to Client View</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">

  <div class="users" id="userList"></div>

  <div class="chat">
    <div class="messages" id="messages"></div>

    <div class="input-bar">
      <input id="msgInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter') send()">
      <button onclick="send()">Send</button>
    </div>
  </div>

</div>

<script>
let selectedUser = null;
let selectedUserName = null;
const currentUser = <?php echo intval($_SESSION["user_id"]); ?>;

/* LOAD USERS */
function loadUsers() {
  fetch("messagesAPI.php?action=users")
    .then(res => res.json())
    .then(users => {
      const list = document.getElementById("userList");
      list.innerHTML = "";

      users.forEach(u => {
        const div = document.createElement("div");
        div.className = "user";
        div.textContent = u.full_name;
        div.onclick = () => selectUser(u.user_id, u.full_name, div);
        list.appendChild(div);
      });
    });
}

/* SELECT USER */
function selectUser(id, name, el) {
  selectedUser     = id;
  selectedUserName = name;
  document.querySelectorAll(".user").forEach(u => u.classList.remove("active"));
  el.classList.add("active");
  loadMessages();
}

/* FORMAT DATE */
function formatDate(sentAt) {
  const d = new Date(sentAt);
  return d.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}

/* LOAD MESSAGES */
function loadMessages() {
  if (!selectedUser) return;

  fetch("messagesAPI.php?action=get&user_id=" + selectedUser)
    .then(res => res.json())
    .then(data => {
      const box = document.getElementById("messages");
      box.innerHTML = "";

      data.forEach(m => {
        const isMe  = m.sender_id == currentUser;
        const label = isMe ? "K&B" : selectedUserName;
        const date  = formatDate(m.sent_at);

        const div = document.createElement("div");
        div.className = "msg " + (isMe ? "me" : "them");

        const meta = document.createElement("span");
        meta.className = "msg-meta";
        meta.textContent = `${label} (${date}):`;

        const text = document.createElement("span");
        text.textContent = " " + m.content;

        div.appendChild(meta);
        div.appendChild(text);
        box.appendChild(div);
      });

      box.scrollTop = box.scrollHeight;
    });
}

/* SEND */
function send() {
  const input = document.getElementById("msgInput");
  const text  = input.value.trim();
  if (!text || !selectedUser) return;

  fetch("messagesAPI.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ receiver_id: selectedUser, content: text })
  }).then(() => {
    input.value = "";
    loadMessages();
  });
}

setInterval(loadMessages, 3000);
loadUsers();
</script>

</body>
</html>
