<?php
require_once "auth.php";
requireLogin();
require_once "dbFuncs.php";

$pdo = connectDB();
$user_id = $_SESSION['user_id'];

// --- Summary cards ---
$stmtCards = $pdo->prepare("
    SELECT
        (SELECT COUNT(*) FROM service_requests
         WHERE user_id = ? AND status != 'completed')                        AS open_requests,
        (SELECT COUNT(*) FROM invoices
         WHERE user_id = ? AND status = 'unpaid')                            AS unpaid_invoices,
        (SELECT COUNT(*) FROM documents
         WHERE user_id = ?)                                                  AS uploaded_docs,
        (SELECT COUNT(*) FROM messages
         WHERE receiver_id = ? AND read_status = 0)                         AS unread_messages
");
$stmtCards->execute([$user_id, $user_id, $user_id, $user_id]);
$cards = $stmtCards->fetch();

// --- Recent service requests ---
$stmtReqs = $pdo->prepare("
    SELECT s.name AS service_name, sr.created_at, sr.status, sr.request_id
    FROM service_requests sr
    JOIN services s ON s.service_id = sr.service_id
    WHERE sr.user_id = ?
    ORDER BY sr.created_at DESC
    LIMIT 5
");
$stmtReqs->execute([$user_id]);
$requests = $stmtReqs->fetchAll();

// --- Recent invoices (replaces chart) ---
$stmtInv = $pdo->prepare("
    SELECT
        CONCAT('INV-', LPAD(invoice_id, 3, '0')) AS invoice_num,
        description,
        amount,
        due_date,
        status
    FROM invoices
    WHERE user_id = ?
    ORDER BY due_date DESC
    LIMIT 3
");
$stmtInv->execute([$user_id]);
$recent_invoices = $stmtInv->fetchAll();

$status_labels = [
  'pending' => 'Pending',
  'in_progress' => 'In Progress',
  'completed' => 'Completed',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Client Dashboard — K&amp;B Bookkeeping</title>
  <link rel="stylesheet" href="css/clientNavbar.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      margin-bottom: 24px;
    }

    .card {
      background: #d6f0ec;
      border: 1px solid #a8d8d0;
      padding: 14px 16px;
    }

    .card .num {
      font-size: 24px;
      color: #1d7a70;
    }

    .card .label {
      font-size: 12px;
      color: #1d7a70;
      margin-top: 6px;
    }

    .section-label {
      font-size: 13px;
      font-weight: bold;
      margin-bottom: 8px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
    }

    table th {
      background: #d6f0ec;
      color: #1d7a70;
      text-align: left;
      padding: 8px 10px;
      border: 1px solid #a8d8d0;
    }

    table td {
      padding: 7px 10px;
      border: 1px solid #ddd;
    }

    table a {
      color: #1d7a70;
      text-decoration: underline;
      cursor: pointer;
      margin-right: 4px;
      font-size: 12px;
    }

    .badge {
      padding: 2px 8px;
      border-radius: 3px;
      font-size: 11px;
      display: inline-block;
    }

    .badge-paid {
      background: #d6f0ec;
      color: #1d7a70;
    }

    .badge-unpaid {
      background: #f5e0d6;
      color: #7a3a1d;
    }

    .view-all {
      font-size: 12px;
      color: #1d7a70;
      text-decoration: underline;
      float: right;
      margin-top: -22px;
      margin-bottom: 8px;
    }
  </style>
</head>

<body>

  <div class="header">
    <h2>K&amp;B Bookkeeping — Client Dashboard</h2>
  </div>

  <nav class="navbar">
    <a href="clientDashboard.php" class="active">Dashboard</a>
    <a href="requests.php">Service Requests</a>
    <a href="documents.php">Documents</a>
    <a href="invoices.php">Invoices</a>
    <a href="messages.php">Messages</a>
    <div class="spacer"></div>
    <?php if (isAdmin()): ?>
      <a href="adminDashboard.php" class="switch-btn">Switch to Admin View</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
  </nav>

  <div class="content">

    <!-- Summary Cards -->
    <div class="cards">
      <div class="card">
        <div class="num"><?= (int) $cards['open_requests'] ?></div>
        <div class="label">Open Requests</div>
      </div>
      <div class="card">
        <div class="num"><?= (int) $cards['unpaid_invoices'] ?></div>
        <div class="label">Unpaid Invoices</div>
      </div>
      <div class="card">
        <div class="num"><?= (int) $cards['uploaded_docs'] ?></div>
        <div class="label">Uploaded Docs</div>
      </div>
      <div class="card">
        <div class="num"><?= (int) $cards['unread_messages'] ?></div>
        <div class="label">Unread Messages</div>
      </div>
    </div>

    <!-- Recent Service Requests -->
    <p class="section-label">Recent Service Requests</p>
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
        <?php foreach ($requests as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['service_name']) ?></td>
            <td><?= date('m/d/Y', strtotime($r['created_at'])) ?></td>
            <td><?= $status_labels[$r['status']] ?? ucfirst($r['status']) ?></td>
            <td>
              <a href="requests.php?id=<?= $r['request_id'] ?>">[ View ]</a>
              <?php if ($r['status'] !== 'completed'): ?>
                <a href="requests.php?edit=<?= $r['request_id'] ?>">[ Edit ]</a>
                <a href="requests.php?delete=<?= $r['request_id'] ?>" onclick="return confirm('Delete this request?')">[
                  Delete ]</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="4" style="color:#888;font-style:italic;">No requests found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Recent Invoices (replaces chart) -->
    <p class="section-label">Recent Invoices</p>
    <a class="view-all" href="invoices.php">View all →</a>
    <table>
      <thead>
        <tr>
          <th>Invoice #</th>
          <th>Description</th>
          <th>Amount</th>
          <th>Due Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_invoices as $inv): ?>
          <tr>
            <td><?= htmlspecialchars($inv['invoice_num']) ?></td>
            <td><?= htmlspecialchars($inv['description']) ?></td>
            <td>$<?= number_format($inv['amount'], 2) ?></td>
            <td><?= date('m/d/Y', strtotime($inv['due_date'])) ?></td>
            <td>
              <?php if ($inv['status'] === 'paid'): ?>
                <span class="badge badge-paid">Paid</span>
              <?php else: ?>
                <span class="badge badge-unpaid">Unpaid</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($recent_invoices)): ?>
          <tr>
            <td colspan="5" style="color:#888;font-style:italic;">No invoices yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

  </div>

</body>

</html>