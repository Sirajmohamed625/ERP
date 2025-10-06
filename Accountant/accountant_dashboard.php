<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Accountant
if ($_SESSION['role'] !== 'accountant') {
    echo "❌ Access denied. Only accountants can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// ================== FETCH FINANCE DATA ================== //
// Revenue (sum of paid invoices)
$total_sales_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM sales WHERE stage='won'"))['total'] ?? 0;

// Pending invoices
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM invoices WHERE status='unpaid'"))['total'] ?? 0;

// Expenses
$expenses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses"))['total'] ?? 0;

// Profit
$profit = $total_sales_amount - $expenses;


// Recent transactions (last 10 invoices)
$transactions = mysqli_query($conn, "SELECT id, client_name, amount, status, created_at FROM invoices ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accountant Dashboard - ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #1e1e1e;
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      height: 100vh;
      background: #111;
      padding: 20px;
      position: fixed;
      width: 220px;
      transition: all 0.3s;
    }
    .sidebar a {
      display: block;
      color: #ddd;
      padding: 10px;
      text-decoration: none;
      margin-bottom: 8px;
      border-radius: 5px;
    }
    .sidebar a:hover {
      background: #333;
    }
    .content {
      margin-left: 240px;
      padding: 20px;
      transition: all 0.3s;
    }
    .card {
      background: linear-gradient(135deg, #2a2a2a, #333);
      border: none;
      border-radius: 15px;
      color: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.6);
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .table {
      background: #2a2a2a;
      border-radius: 10px;
      overflow: hidden;
    }
    .table thead {
      background: #444;
      color: #fff;
    }
    .status-paid { color: #4caf50; font-weight: bold; }
    .status-pending { color: #ff9800; font-weight: bold; }
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      .content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4>💼 ERP Accountant</h4>
    <p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
    <hr>
  <a href="accountant_dashboard.php" class="bg-dark">🏠 Dashboard</a>
  <a href="view_invoices.php">💵 Invoices</a>
  <a href="view_expenses.php">📉 Expenses</a>
  <a href="view_inventory.php">📦 Inventory</a>
  <a href="../logout.php" class="text-danger">🚪 Logout</a>
  </div>

  <!-- Main Content -->
  <div class="content">
    <h2>💰 Finance Overview</h2>
    <p>Track revenue, pending invoices, expenses, and profit.</p>

    <!-- Stats Cards -->
    <div class="row mt-4 g-3">
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>💵 Revenue</h5>
          <h2>$<?php echo number_format($total_sales_amount,2); ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>⏳ Pending</h5>
          <h2>$<?php echo number_format($pending,2); ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>📉 Expenses</h5>
          <h2>$<?php echo number_format($expenses,2); ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>📈 Profit</h5>
          <h2>$<?php echo number_format($profit,2); ?></h2>
        </div>
      </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="mt-4">
      <h4>🧾 Recent Invoices</h4>
      <div class="table-responsive">
        <table class="table table-dark table-striped align-middle text-center">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = mysqli_fetch_assoc($transactions)) { ?>
              <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                <td>$<?php echo number_format($row['amount'],2); ?></td>
                <td class="<?php echo ($row['status']=='paid') ? 'status-paid' : 'status-pending'; ?>">
                  <?php echo ucfirst($row['status']); ?>
                </td>
                <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</body>
</html>
