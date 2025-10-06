<?php
session_start();
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check role
if ($_SESSION['role'] !== 'admin') {
    echo "❌ Access denied. Only admins can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// ================== FETCH DATA ================== //
// Employees count
$employees_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $employees_count = $row['total'];
}

// Revenue (sum of paid invoices)
$revenue = 0;
$total_sales_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM sales WHERE stage='won'"))['total'] ?? 0;
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $revenue = $row['total'] ?? 0;
}

// Inventory items
$inventory_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $inventory_count = $row['total'];
}

// Sales deals
$sales_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM sales");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $sales_count = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #1e1e1e;
      color: white;
    }
    .sidebar {
      height: 100vh;
      background: #111;
      padding: 20px;
      position: fixed;
      width: 220px;
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
    }
    .card {
      background: #2a2a2a;
      border: none;
      border-radius: 12px;
      color: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.6);
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h4>⚙️ ERP Admin</h4>
    <p>Welcome, <?php echo $user_name; ?></p>
    <hr>
    <a href="admin_dashboard.php" class="bg-dark">🏠 Dashboard</a>
    <a href="users.php">👨 Manage Users</a>
    <a href="view_employees.php">👨‍💼 Employees</a>
    <a href="view_finance.php">💰 Finance</a>
    <a href="view_inventory.php">📦 Inventory</a>
    <a href="view_sales.php">📊 Sales</a>
    <a href="view_products.php">📦 Products</a>
    <a href="view_clients.php">🧾 Clients</a>
    <a href="view_projects.php">📁 Projects</a>
    <a href="../logout.php" class="text-danger">🚪 Logout</a>
  </div>

  <!-- Main Content -->
  <div class="content">
    <h2>📊 Admin Dashboard</h2>
    <p>Here’s a real-time overview of your ERP system.</p>

    <div class="row mt-4">
      <div class="col-md-3">
        <div class="card p-3">
          <h5>👨 Employees</h5>
          <h2><?php echo $employees_count; ?></h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3">
          <h5>💰 Revenue</h5>
          <h2>$<?php echo number_format($total_sales_amount,2); ?></h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3">
          <h5>📦 Inventory</h5>
          <h2><?php echo $inventory_count; ?> Items</h2>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3">
          <h5>📊 Sales</h5>
          <h2><?php echo $sales_count; ?> Deals</h2>
        </div>
      </div>
    </div>
  </div>

</body>
</html> 