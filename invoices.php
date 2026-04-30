<?php
session_start();

// Requires session to be initiaded
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once 'dbFuncs.php';

$pdo = connectDB();
$user_id = $_SESSION['user_id'];

// --- CONSULTA 1: Summary cards ---
$stmtSummary = $pdo->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN status = 'unpaid' THEN amount ELSE 0 END), 0)               AS total_due,
        COALESCE(SUM(CASE WHEN status = 'paid'
                          AND YEAR(due_date) = YEAR(CURDATE()) THEN amount ELSE 0 END), 0) AS paid_year,
        MIN(CASE WHEN status = 'unpaid' THEN due_date ELSE NULL END)                       AS next_due_date
    FROM invoices
    WHERE user_id = ?
");
$stmtSummary->execute([$user_id]);
$summary = $stmtSummary->fetch();

// --- CONSULTA 2: Historial de facturas ---
$stmtInvoices = $pdo->prepare("
    SELECT
        CONCAT('INV-', LPAD(invoice_id, 3, '0')) AS invoice_num,
        description,
        amount,
        due_date,
        status
    FROM invoices
    WHERE user_id = ?
    ORDER BY due_date DESC
");
$stmtInvoices->execute([$user_id]);
$invoices = $stmtInvoices->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>K&amp;B Bookkeeping — My Invoices</title>
  <link rel="stylesheet" href="css/styles.css" />
  <style>
    /* Summary cards — Invoice page specific */
    .summary {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      margin-bottom: 24px;
      border: 1px solid #a8d8d0;
    }

    .summary-card {
      background: #d6f0ec;
      padding: 16px 18px;
      border-right: 1px solid #a8d8d0;
    }

    .summary-card:last-child {
      border-right: none;
    }

    .summary-card .slabel {
      font-size: 12px;
      color: #1d7a70;
      margin-bottom: 8px;
    }

    .summary-card .sval {
      font-size: 22px;
      font-weight: bold;
      color: #1d7a70;
    }

    /* Status badges */
    .badge {
      padding: 2px 10px;
      border-radius: 3px;
      font-size: 12px;
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

    .empty {
      color: #888;
      font-style: italic;
      padding: 12px 0;
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <div class="header">
    <h1>K&amp;B Bookkeeping &mdash; My Invoices</h1>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar">
    <a href="clientDashboard.php">Dashboard</a>
    <a href="requests.php">Service Requests</a>
    <a href="documents.php">Documents</a>
    <a href="invoices.php" class="active">Invoices</a>
    <a href="messages.php">Messages</a>
    <div class="spacer"></div>
    <a href="logout.php">Logout</a>
  </nav>

  <div class="content">

    <!-- Invoice Summary -->
    <p class="section-label">Invoice Summary</p>
    <div class="summary">
      <div class="summary-card">
        <div class="slabel">Total Due</div>
        <div class="sval">$<?= number_format($summary['total_due'], 2) ?></div>
      </div>
      <div class="summary-card">
        <div class="slabel">Paid This Year</div>
        <div class="sval">$<?= number_format($summary['paid_year'], 2) ?></div>
      </div>
      <div class="summary-card">
        <div class="slabel">Next Due Date</div>
        <div class="sval">
          <?= $summary['next_due_date']
            ? date('m/d/Y', strtotime($summary['next_due_date']))
            : '&mdash;' ?>
        </div>
      </div>
    </div>

    <!-- Invoice History -->
    <p class="section-label">Invoice History</p>

    <?php if (empty($invoices)): ?>
      <p class="empty">No invoices found.</p>
    <?php else: ?>
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
          <?php foreach ($invoices as $inv): ?>
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
        </tbody>
      </table>
    <?php endif; ?>

  </div>

</body>

</html>