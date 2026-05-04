<?php
require_once "auth.php";
requireLogin();
require_once 'dbFuncs.php';

$pdo = connectDB();
$user_id = $_SESSION['user_id'];

// Query 1: Summary cards
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

// Query 2: Invoice history 
$stmtInvoices = $pdo->prepare("
    SELECT
        invoice_id,
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
  <link rel="stylesheet" href="css/clientNavbar.css">
  <link rel="stylesheet" href="css/styles.css" />
  <style>
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

    .pay-btn {
      background: #2a8a7e;
      color: #fff;
      border: none;
      padding: 3px 10px;
      font-size: 12px;
      cursor: pointer;
      border-radius: 3px;
    }

    .pay-btn:hover {
      background: #1d7a70;
    }

    .pay-btn:disabled {
      background: #aaa;
      cursor: default;
    }

    .empty {
      color: #888;
      font-style: italic;
      padding: 12px 0;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }

    .modal-overlay.open {
      display: flex;
    }

    .modal {
      background: #fff;
      border-top: 4px solid #2a8a7e;
      padding: 24px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .modal h3 {
      font-size: 15px;
      color: #1d5a55;
      margin-bottom: 16px;
    }

    .modal label {
      font-size: 12px;
      color: #444;
      display: block;
      margin-bottom: 3px;
      margin-top: 12px;
    }

    .modal input {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      font-size: 13px;
      border-radius: 2px;
    }

    .modal input.error {
      border-color: #c0392b;
    }

    .modal-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .modal-err {
      font-size: 11px;
      color: #c0392b;
      margin-top: 3px;
      display: none;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .modal-actions button {
      flex: 1;
      padding: 9px;
      font-size: 13px;
      border: none;
      cursor: pointer;
    }

    .btn-confirm {
      background: #2a8a7e;
      color: #fff;
      font-weight: bold;
    }

    .btn-confirm:hover {
      background: #1d7a70;
    }

    .btn-cancel {
      background: #f0f0f0;
      color: #444;
    }

    .btn-cancel:hover {
      background: #e0e0e0;
    }

    .modal-success {
      text-align: center;
      padding: 10px 0;
      display: none;
    }

    .modal-success p {
      color: #1d7a70;
      font-size: 14px;
      font-weight: bold;
      margin-top: 8px;
    }
  </style>
</head>
<!-- Payment Modal -->
<div class="modal-overlay" id="payModal">
  <div class="modal">
    <h3>Payment Details</h3>

    <div id="modalForm">
      <label>Name on Card</label>
      <input type="text" id="cardName" placeholder="Jane Smith" maxlength="60">
      <div class="modal-err" id="errName"></div>

      <label>Card Number</label>
      <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
      <div class="modal-err" id="errNumber"></div>

      <div class="modal-row">
        <div>
          <label>Expiry</label>
          <input type="text" id="cardExpiry" placeholder="MM/YY" maxlength="5">
          <div class="modal-err" id="errExpiry"></div>
        </div>
        <div>
          <label>CVV</label>
          <input type="text" id="cardCVV" placeholder="123" maxlength="4">
          <div class="modal-err" id="errCVV"></div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeModal()">Cancel</button>
        <button class="btn-confirm" id="btnConfirm" onclick="validateAndPay()">Confirm Payment</button>
      </div>
    </div>

    <div class="modal-success" id="modalSuccess">
      <p>✔ Payment successful!</p>
      <p style="font-size:12px;color:#888;margin-top:6px;">Updating your invoice...</p>
    </div>
  </div>
</div>

<body>

  <div class="header">
    <h2>K&amp;B Bookkeeping &mdash; My Invoices</h2>
  </div>

  <nav class="navbar">
    <a href="clientDashboard.php">Dashboard</a>
    <a href="requests.php">Service Requests</a>
    <a href="documents.php">Documents</a>
    <a href="invoices.php" class="active">Invoices</a>
    <a href="messages.php">Messages</a>
    <div class="spacer"></div>
    <?php if (isAdmin()): ?>
      <a href="adminDashboard.php" class="switch-btn">Switch to Admin View</a>
    <?php endif; ?>
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
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="invoiceTable">
          <?php foreach ($invoices as $inv): ?>
            <tr id="row-<?= $inv['invoice_id'] ?>">
              <td><?= htmlspecialchars($inv['invoice_num']) ?></td>
              <td><?= htmlspecialchars($inv['description']) ?></td>
              <td>$<?= number_format($inv['amount'], 2) ?></td>
              <td><?= date('m/d/Y', strtotime($inv['due_date'])) ?></td>
              <td id="status-<?= $inv['invoice_id'] ?>">
                <?php if ($inv['status'] === 'paid'): ?>
                  <span class="badge badge-paid">Paid</span>
                <?php else: ?>
                  <span class="badge badge-unpaid">Unpaid</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($inv['status'] === 'unpaid'): ?>
                  <button class="pay-btn" id="btn-<?= $inv['invoice_id'] ?>" onclick="payInvoice(<?= $inv['invoice_id'] ?>)">
                    Pay
                  </button>
                <?php else: ?>
                  &mdash;
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

  </div>

  <script>
    let pendingInvoiceId = null;

    function payInvoice(id) {
      pendingInvoiceId = id;
      // Reset modal
      ['cardName', 'cardNumber', 'cardExpiry', 'cardCVV'].forEach(f => {
        document.getElementById(f).value = '';
        document.getElementById(f).classList.remove('error');
      });
      document.querySelectorAll('.modal-err').forEach(e => e.style.display = 'none');
      document.getElementById('modalForm').style.display = 'block';
      document.getElementById('modalSuccess').style.display = 'none';
      document.getElementById('payModal').classList.add('open');
    }

    function closeModal() {
      document.getElementById('payModal').classList.remove('open');
      pendingInvoiceId = null;
    }

    // Format card number: groups of 4
    document.getElementById('cardNumber').addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').substring(0, 16);
      this.value = v.match(/.{1,4}/g)?.join(' ') || v;
    });

    // Format expiry: MM/YY
    document.getElementById('cardExpiry').addEventListener('input', function () {
      let v = this.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2);
      this.value = v;
    });

    // CVV: digits only, max 4
    document.getElementById('cardCVV').addEventListener('input', function () {
      this.value = this.value.replace(/\D/g, '').substring(0, 4);
    });

    function showErr(fieldId, errId, msg) {
      document.getElementById(fieldId).classList.add('error');
      const e = document.getElementById(errId);
      e.textContent = msg;
      e.style.display = 'block';
      return false;
    }

    function clearErr(fieldId, errId) {
      document.getElementById(fieldId).classList.remove('error');
      document.getElementById(errId).style.display = 'none';
    }

    function validateAndPay() {
      let valid = true;

      const name = document.getElementById('cardName').value.trim();
      const number = document.getElementById('cardNumber').value.replace(/\s/g, '');
      const expiry = document.getElementById('cardExpiry').value.trim();
      const cvv = document.getElementById('cardCVV').value.trim();

      // Name
      if (name.length < 3) {
        valid = showErr('cardName', 'errName', 'Enter the full name on the card.');
      } else { clearErr('cardName', 'errName'); }

      // Card number — 16 digits
      if (!/^\d{16}$/.test(number)) {
        valid = showErr('cardNumber', 'errNumber', 'Enter a valid 16-digit card number.');
      } else { clearErr('cardNumber', 'errNumber'); }

      // Expiry MM/YY and not in the past
      const expiryMatch = expiry.match(/^(\d{2})\/(\d{2})$/);
      if (!expiryMatch) {
        valid = showErr('cardExpiry', 'errExpiry', 'Use MM/YY format.');
      } else {
        const month = parseInt(expiryMatch[1]);
        const year = parseInt('20' + expiryMatch[2]);
        const now = new Date();
        const cardDate = new Date(year, month - 1);
        if (month < 1 || month > 12) {
          valid = showErr('cardExpiry', 'errExpiry', 'Invalid month.');
        } else if (cardDate < new Date(now.getFullYear(), now.getMonth())) {
          valid = showErr('cardExpiry', 'errExpiry', 'This card has expired.');
        } else { clearErr('cardExpiry', 'errExpiry'); }
      }

      // CVV — 3 or 4 digits
      if (!/^\d{3,4}$/.test(cvv)) {
        valid = showErr('cardCVV', 'errCVV', 'Enter a valid 3 or 4 digit CVV.');
      } else { clearErr('cardCVV', 'errCVV'); }

      if (!valid) return;

      // Confirm button → loading
      const btn = document.getElementById('btnConfirm');
      btn.textContent = 'Processing...';
      btn.disabled = true;

      fetch('payInvoice.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ invoice_id: pendingInvoiceId })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Show success inside modal
            document.getElementById('modalForm').style.display = 'none';
            document.getElementById('modalSuccess').style.display = 'block';

            // Update table row
            const id = pendingInvoiceId;
            document.getElementById('status-' + id).innerHTML =
              '<span class="badge badge-paid">Paid</span>';
            const payBtn = document.getElementById('btn-' + id);
            if (payBtn) payBtn.replaceWith(document.createTextNode('—'));

            // Close modal and reload summary after 2s
            setTimeout(() => {
              closeModal();
              location.reload();
            }, 2000);
          } else {
            btn.textContent = 'Confirm Payment';
            btn.disabled = false;
            alert(data.message || 'Payment failed. Please try again.');
          }
        })
        .catch(() => {
          btn.textContent = 'Confirm Payment';
          btn.disabled = false;
          alert('Network error. Please try again.');
        });
    }

    // Close modal if clicking outside
    document.getElementById('payModal').addEventListener('click', function (e) {
      if (e.target === this) closeModal();
    });
  </script>

</body>

</html>
