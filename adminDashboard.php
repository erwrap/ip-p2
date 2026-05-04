<?php
require_once "auth.php";
requireAdmin();
require_once "dbFuncs.php";

$pdo = connectDB();

// --- Client list ---
$stmtClients = $pdo->query("
    SELECT user_id, full_name, email, phone, created_at
    FROM users
    WHERE role = 'client'
    ORDER BY created_at ASC
");
$clients = $stmtClients->fetchAll();

// --- All service requests with client + service name ---
$stmtReqs = $pdo->query("
    SELECT sr.request_id, u.full_name, s.name AS service_name,
           sr.notes, sr.status, sr.priority
    FROM service_requests sr
    JOIN users    u ON u.user_id    = sr.user_id
    JOIN services s ON s.service_id = sr.service_id
    ORDER BY sr.created_at DESC
");
$requests = $stmtReqs->fetchAll();

// --- Services list (for status dropdown) ---
$status_options = ['pending', 'in_progress', 'completed'];

// --- Invoice summary per client ---
$stmtInvSummary = $pdo->query("
    SELECT
        u.user_id,
        u.full_name,
        COALESCE(SUM(CASE WHEN i.status = 'unpaid' THEN i.amount ELSE 0 END), 0)  AS total_due,
        COALESCE(SUM(CASE WHEN i.status = 'paid'   THEN i.amount ELSE 0 END), 0)  AS total_paid,
        COUNT(CASE WHEN i.status = 'unpaid' THEN 1 END)                           AS unpaid_count,
        MIN(CASE WHEN i.status = 'unpaid' THEN i.due_date ELSE NULL END)          AS next_due
    FROM users u
    LEFT JOIN invoices i ON i.user_id = u.user_id
    WHERE u.role = 'client'
    GROUP BY u.user_id, u.full_name
    ORDER BY u.full_name ASC
");
$inv_summary = $stmtInvSummary->fetchAll();

// --- Client list for Create Invoice dropdown ---
$stmtClientDrop = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'client' ORDER BY full_name");
$client_drop = $stmtClientDrop->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — K&amp;B Bookkeeping</title>
  <link rel="stylesheet" href="css/clientNavbar.css">
  <style>
    :root {
      --nav-bg: #1d5a55;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

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

    tr:hover td {
      background: #f9fefd;
    }

    table a {
      color: #1d7a70;
      text-decoration: underline;
      cursor: pointer;
      font-size: 12px;
    }

    select,
    input[type="text"],
    input[type="date"],
    input[type="number"] {
      padding: 6px 8px;
      border: 1px solid #ccc;
      font-size: 13px;
      width: 100%;
    }

    .btn {
      padding: 8px 16px;
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

    .badge {
      padding: 2px 8px;
      border-radius: 3px;
      font-size: 11px;
      display: inline-block;
    }

    .badge-due {
      background: #f5e0d6;
      color: #7a3a1d;
    }

    .badge-clear {
      background: #d6f0ec;
      color: #1d7a70;
    }

    .form-table td,
    .form-table th {
      vertical-align: middle;
    }

    .form-table th {
      width: 160px;
    }

    #createMsg {
      font-size: 12px;
      color: #1d7a70;
      margin-top: 8px;
      display: none;
    }
  </style>
</head>

<body>

  <div class="header">
    <h2>K&amp;B Bookkeeping — Admin Panel</h2>
  </div>

  <nav class="navbar">
    <a href="adminDashboard.php" class="active">Dashboard</a>
    <a href="adminRequests.php">Service Requests</a>
    <a href="adminDocuments.php">Documents</a>
    <a href="adminInvoices.php">Invoices</a>
    <a href="adminMessages.php">Messages</a>
    <div class="spacer"></div>
    <a href="clientDashboard.php" class="switch-btn">Switch to Client View</a>
    <a href="logout.php">Logout</a>
  </nav>

  <div class="content">

    <!-- Client List -->
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
        <?php foreach ($clients as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['full_name']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
            <td><?= date('m/d/Y', strtotime($c['created_at'])) ?></td>
            <td><a href="adminInvoices.php?client=<?= $c['user_id'] ?>">[ View ]</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Manage Service Requests -->
    <p class="section-label">Manage Service Requests</p>
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Service</th>
          <th>Notes</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['full_name']) ?></td>
            <td><?= htmlspecialchars($r['service_name']) ?></td>
            <td><?= htmlspecialchars($r['notes'] ?? '—') ?></td>
            <td><?= ucfirst($r['priority']) ?></td>
            <td id="req-status-<?= $r['request_id'] ?>"><?= ucfirst(str_replace('_', ' ', $r['status'])) ?></td>
            <td>
              <select onchange="updateReqStatus(<?= $r['request_id'] ?>, this.value)">
                <?php foreach ($status_options as $opt): ?>
                  <option value="<?= $opt ?>" <?= $opt === $r['status'] ? 'selected' : '' ?>>
                    <?= ucfirst(str_replace('_', ' ', $opt)) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="6" style="color:#888;font-style:italic;">No requests found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Invoice Summary per Client -->
    <p class="section-label">Invoice Summary by Client</p>
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Unpaid Count</th>
          <th>Total Due</th>
          <th>Total Paid</th>
          <th>Next Due Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inv_summary as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td>
              <?php if ($row['unpaid_count'] > 0): ?>
                <span class="badge badge-due"><?= (int) $row['unpaid_count'] ?> unpaid</span>
              <?php else: ?>
                <span class="badge badge-clear">All paid</span>
              <?php endif; ?>
            </td>
            <td>$<?= number_format($row['total_due'], 2) ?></td>
            <td>$<?= number_format($row['total_paid'], 2) ?></td>
            <td><?= $row['next_due'] ? date('m/d/Y', strtotime($row['next_due'])) : '—' ?></td>
            <td><a href="adminInvoices.php?client=<?= $row['user_id'] ?>">[ View All ]</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Quick Create Invoice -->
    <p class="section-label">Quick Create Invoice</p>
    <table class="form-table">
      <tbody>
        <tr>
          <th>Select Client</th>
          <td>
            <select id="inv_client">
              <option value="">-- Select --</option>
              <?php foreach ($client_drop as $c): ?>
                <option value="<?= $c['user_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <th>Description</th>
          <td><input type="text" id="inv_desc" placeholder="e.g. Monthly Bookkeeping – April 2026"></td>
        </tr>
        <tr>
          <th>Amount ($)</th>
          <td><input type="number" id="inv_amount" step="0.01" min="0" placeholder="175.00"></td>
        </tr>
        <tr>
          <th>Due Date</th>
          <td><input type="date" id="inv_due"></td>
        </tr>
      </tbody>
    </table>
    <button class="btn" onclick="createInvoice()">Create Invoice</button>
    <p id="createMsg"></p>

  </div>

  <script>
    // --- Update service request status ---
    function updateReqStatus(id, status) {
      fetch('updateRequestStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ request_id: id, status: status })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const cell = document.getElementById('req-status-' + id);
            if (cell) cell.textContent = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
          }
        });
    }

    // --- Create invoice ---
    function createInvoice() {
      const client = document.getElementById('inv_client').value;
      const desc = document.getElementById('inv_desc').value.trim();
      const amount = document.getElementById('inv_amount').value;
      const due = document.getElementById('inv_due').value;
      const msg = document.getElementById('createMsg');

      if (!client || !desc || !amount || !due) {
        msg.style.color = '#c0392b';
        msg.textContent = 'Please fill in all fields correctly.';
        msg.style.display = 'block';
        return;
      }

      fetch('createInvoice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: client, description: desc, amount: amount, due_date: due })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            msg.style.color = '#1d7a70';
            msg.textContent = '✔ Invoice created successfully.';
            msg.style.display = 'block';
            // Reset form
            document.getElementById('inv_client').value = '';
            document.getElementById('inv_desc').value = '';
            document.getElementById('inv_amount').value = '';
            document.getElementById('inv_due').value = '';
            // Reload after 1.5s to update summary table
            setTimeout(() => location.reload(), 1500);
          } else {
            msg.style.color = '#c0392b';
            msg.textContent = data.message || 'Error creating invoice.';
            msg.style.display = 'block';
          }
        });
    }
  </script>

</body>

</html>