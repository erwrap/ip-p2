<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages — K&B Bookkeeping</title>
<link rel="stylesheet" href="css/clientNavbar.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

.chat-box {
  border:1px solid #ccc;
  height:400px;
  overflow-y:auto;
  padding:10px;
  margin-bottom:10px;
  background:#f9fefd;
}

.msg {
  padding:8px 12px;
  margin:6px 0;
  border-radius:4px;
  max-width:70%;
}

.me   { background:#2a8a7e; color:white; margin-left:auto; }
.them { background:#e0f2ef; }

.msg-meta { font-weight: bold; }

.input-row { display:flex; gap:10px; }
.input-row input {
  flex:1;
  padding:10px;
  border:1px solid #ccc;
  outline:none;
}
.input-row button {
  padding:10px 20px;
  background:#2a8a7e;
  color:white;
  border:none;
  cursor:pointer;
}
</style>
</head>

<body>

<div class="header">
  <h1>K&B Bookkeeping — Messages</h1>
</div>

<nav class="navbar">
  <a href="clientDashboard.php">Dashboard</a>
  <a href="requests.php">Service Requests</a>
  <a href="documents.php">Documents</a>
  <a href="#">Invoices</a>
  <a href="#" class="active">Messages</a>
  <div class="spacer"></div>
  <a href="logout.php">Logout</a>
</nav>

<div class="content">
  <div class="chat-box" id="chat"></div>

  <div class="input-row">
    <input id="msgInput" placeholder="Type a message..." onkeydown="if(event.key==='Enter') sendMessage()">
    <button onclick="sendMessage()">Send</button>
  </div>
</div>

<script>
let adminId      = null;
let currentUser  = <?php echo intval($_SESSION["user_id"]); ?>;

/* GET ADMIN ID FIRST, THEN LOAD MESSAGES AND START POLL */
function getAdmin() {
  fetch("messagesAPI.php?action=getAdmin")
    .then(res => res.json())
    .then(data => {
      adminId = data.admin_id;
      if (!adminId) {
        document.getElementById("chat").textContent = "No admin found. Please contact support.";
        return;
      }
      loadMessages();
      setInterval(loadMessages, 10000); // only start polling once adminId is set
    });
}

/* LOAD MESSAGES */
function loadMessages() {
  fetch("messagesAPI.php?action=get&user_id=" + adminId)
    .then(res => res.json())
    .then(data => render(data));
}

/* FORMAT DATE */
function formatDate(sentAt) {
  const d = new Date(sentAt);
  return d.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}

/* RENDER */
function render(messages) {
  const chat = document.getElementById("chat");
  chat.innerHTML = "";

  messages.forEach(m => {
    const isMe = m.sender_id == currentUser;
    const label = isMe ? "You" : "K&B";
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
    chat.appendChild(div);
  });

  chat.scrollTop = chat.scrollHeight;
}

/* SEND */
function sendMessage() {
  const content = document.getElementById("msgInput").value.trim();
  if (!content) return;

  fetch("messagesAPI.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ content: content })
  }).then(() => {
    document.getElementById("msgInput").value = "";
    loadMessages();
  });
}

getAdmin();
</script>

</body>
</html>
