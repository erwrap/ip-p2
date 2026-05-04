<?php
require_once "auth.php";
requireAdmin();
require_once "dbFuncs.php";

$pdo = connectDB();

// Optional filter by client
$filter_client = isset($_GET['client']) ? (int) $_GET['client'] : 0;

// --- Invoice summary per client (top section) ---
$stmtSummary = $pdo->query("
    SELECT
        u.user_id,
        u.full_name,
        COALESCE(SUM(CASE WHEN i.status = 'unpaid' THEN i.amount ELSE 0 END), 0) AS total_due,
        COALESCE(SUM(CASE WHEN i.status = 'paid'   THEN i.amount ELSE 0 END), 0) AS total_paid,
        COUNT(CASE WHEN i.status = 'unpaid' THEN 1 END)                          AS unpaid_count
    FROM users u
    LEFT JOIN invoices i ON i.user_id = u.user_id
    WHERE u.role = 'client'
    GROUP BY u.user_id, u.full_name
    ORDER BY u.full_name ASC
");
$summary = $stmtSummary->fetchAll();

// --- All invoices (with optional client filter) ---
if ($filter_client) {
    $stmtInv = $pdo->prepare("
        SELECT i.invoice_id,
               CONCAT('INV-', LPAD(i.invoice_id, 3, '0')) AS invoice_num,
               u.full_name,
               i.description, i.amount, i.due_date, i.status, i.created_at
        FROM invoices i
        JOIN users u ON u.user_id = i.user_id
        WHERE i.user_id = ?
        ORDER BY i.due_date DESC
    ");
    $stmtInv->execute([$filter_client]);
} else {
    $stmtInv = $pdo->query("
        SELECT i.invoice_id,
               CONCAT('INV-', LPAD(i.invoice_id, 3, '0')) AS invoice_num,
               u.full_name,
               i.description, i.amount, i.due_date, i.status, i.created_at
        FROM invoices i
        JOIN users u ON u.user_id = i.user_id
        ORDER BY i.due_date DESC
    ");
}
$invoices = $stmtInv->fetchAll();

// --- Clients for dropdown ---
$clients = $pdo->query("SELECT user_id, full_name FROM users WHERE role='client' ORDER BY full_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Invoices — K&amp;B Bookkeeping</title>
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

        .badge-due {
            background: #f5e0d6;
            color: #7a3a1d;
        }

        .badge-clear {
            background: #d6f0ec;
            color: #1d7a70;
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

        .filter-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .filter-row select {
            width: auto;
            min-width: 200px;
        }

        .filter-row a {
            font-size: 12px;
            color: #1d7a70;
            text-decoration: underline;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            max-width: 600px;
            margin-bottom: 10px;
        }

        .form-grid label {
            font-size: 12px;
            color: #444;
            display: block;
            margin-bottom: 3px;
        }

        .form-grid .full {
            grid-column: 1 / -1;
        }

        #createMsg {
            font-size: 12px;
            margin-top: 8px;
            display: none;
        }

        .del-btn {
            background: none;
            border: none;
            color: #c0392b;
            cursor: pointer;
            font-size: 12px;
            text-decoration: underline;
            padding: 0;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>K&amp;B Bookkeeping — Admin Invoices</h2>
    </div>

    <nav class="navbar">
        <a href="adminDashboard.php">Dashboard</a>
        <a href="adminRequests.php">Service Requests</a>
        <a href="adminDocuments.php">Documents</a>
        <a href="adminInvoices.php" class="active">Invoices</a>
        <a href="adminMessages.php">Messages</a>
        <div class="spacer"></div>
        <a href="invoices.php" class="switch-btn">Switch to Client View</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="content">

        <!-- Invoice Summary per Client -->
        <p class="section-label">Invoice Summary by Client</p>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Unpaid</th>
                    <th>Total Due</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary as $row): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($row['full_name']) ?>
                        </td>
                        <td>
                            <?php if ($row['unpaid_count'] > 0): ?>
                                <span class="badge badge-due">
                                    <?= (int) $row['unpaid_count'] ?> unpaid
                                </span>
                            <?php else: ?>
                                <span class="badge badge-clear">All paid</span>
                            <?php endif; ?>
                        </td>
                        <td>$
                            <?= number_format($row['total_due'], 2) ?>
                        </td>
                        <td>$
                            <?= number_format($row['total_paid'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- All Invoices Table -->
        <p class="section-label">All Invoices</p>

        <div class="filter-row">
            <span style="font-size:13px;">Filter by client:</span>
            <select onchange="location.href='adminInvoices.php?client='+this.value">
                <option value="0" <?= !$filter_client ? 'selected' : '' ?>>All Clients</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['user_id'] ?>" <?= $filter_client === (int) $c['user_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['full_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <table id="invoiceTable">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr id="irow-<?= $inv['invoice_id'] ?>">
                        <td>
                            <?= htmlspecialchars($inv['invoice_num']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($inv['full_name']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($inv['description']) ?>
                        </td>
                        <td>$
                            <?= number_format($inv['amount'], 2) ?>
                        </td>
                        <td>
                            <?= date('m/d/Y', strtotime($inv['due_date'])) ?>
                        </td>
                        <td id="istatus-<?= $inv['invoice_id'] ?>">
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="badge badge-paid">Paid</span>
                            <?php else: ?>
                                <span class="badge badge-unpaid">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($inv['status'] === 'unpaid'): ?>
                                <button class="del-btn" onclick="markPaid(<?= $inv['invoice_id'] ?>)">Mark Paid</button>
                                &nbsp;|&nbsp;
                            <?php endif; ?>
                            <button class="del-btn" style="color:#c0392b;"
                                onclick="deleteInvoice(<?= $inv['invoice_id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" style="color:#888;font-style:italic;">No invoices found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Create Invoice Form -->
        <p class="section-label">Create Invoice</p>
        <div class="form-grid">
            <div>
                <label>Select Client</label>
                <select id="inv_client">
                    <option value="">-- Select --</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['user_id'] ?>">
                            <?= htmlspecialchars($c['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Amount ($)</label>
                <input type="number" id="inv_amount" step="0.01" min="0" placeholder="175.00">
            </div>
            <div class="full">
                <label>Description</label>
                <input type="text" id="inv_desc" placeholder="e.g. Monthly Bookkeeping – May 2026">
            </div>
            <div>
                <label>Due Date</label>
                <input type="date" id="inv_due">
            </div>
        </div>
        <button class="btn" onclick="createInvoice()">Create Invoice</button>
        <p id="createMsg"></p>

    </div>

    <script>
        function markPaid(id) {
            if (!confirm('Mark this invoice as paid?')) return;

            fetch('adminMarkPaid.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ invoice_id: id })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('istatus-' + id).innerHTML =
                            '<span class="badge badge-paid">Paid</span>';
                        const row = document.getElementById('irow-' + id);
                        if (row) row.cells[6].innerHTML = '&mdash;';
                        setTimeout(() => location.reload(), 600);
                    }
                });
        }

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
                        document.getElementById('inv_client').value = '';
                        document.getElementById('inv_desc').value = '';
                        document.getElementById('inv_amount').value = '';
                        document.getElementById('inv_due').value = '';
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        msg.style.color = '#c0392b';
                        msg.textContent = data.message || 'Error creating invoice.';
                        msg.style.display = 'block';
                    }
                });
        }
        function deleteInvoice(id) {
            if (!confirm('Permanently delete this invoice? This cannot be undone.')) return;

            fetch('deleteInvoice.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ invoice_id: id })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('irow-' + id).remove();
                    } else {
                        alert(data.message || 'Eror deleting invoice.');
                    }
                });
        }
    </script>

</body>

</html>
