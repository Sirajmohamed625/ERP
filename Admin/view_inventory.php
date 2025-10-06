<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Admin
if ($_SESSION['role'] !== 'admin') {
    echo "❌ Access denied. Only admins can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// ================== FETCH INVENTORY STATS ================== //
$total_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory"))['total'] ?? 0;
$low_stock   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory WHERE quantity < 10 AND quantity > 0"))['total'] ?? 0;
$out_stock   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM inventory WHERE quantity = 0"))['total'] ?? 0;
$total_qty   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total FROM inventory"))['total'] ?? 0;

// Fetch inventory records
$items = mysqli_query($conn, "SELECT * FROM inventory ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory - ERP Admin</title>
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
    .sidebar a:hover, .sidebar a.active {
      background: #333;
      color: #fff;
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
    .card:hover { transform: translateY(-5px); }
    .table {
      background: #2a2a2a;
      border-radius: 10px;
      overflow: hidden;
    }
    .table thead { background: #444; color: #fff; }
    .status-in_stock { color: #4caf50; font-weight: bold; }
    .status-low_stock { color: #ff9800; font-weight: bold; }
    .status-out_of_stock { color: #f44336; font-weight: bold; }
    @media (max-width: 768px) {
      .sidebar { width: 100%; height: auto; position: relative; }
      .content { margin-left: 0; }
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
    <h2>📦 Inventory Management</h2>
    <p>Track your stock and product availability in real-time.</p>

    <!-- Stats Cards -->
    <div class="row mt-4 g-3">
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>Total Items</h5>
          <h2><?php echo $total_items; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>Low Stock</h5>
          <h2><?php echo $low_stock; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>Out of Stock</h5>
          <h2><?php echo $out_stock; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>Total Quantity</h5>
          <h2><?php echo $total_qty ?? 0; ?></h2>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="card mt-4">
      <h4>🧾 Inventory List</h4>
      <div class="table-responsive mt-3">
        <table class="table table-dark table-striped align-middle text-center">
          <thead>
            <tr>
              <th>ID</th>
              <th>Product Name</th>
              <th>SKU</th>
              <th>Category</th>
              <th>Quantity</th>
              <th>Price ($)</th>
              <th>Supplier</th>
              <th>Status</th>
              <th>Created</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($items)) { ?>
              <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                <td><?php echo htmlspecialchars($row['sku']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['supplier']); ?></td>
                <td class="status-<?php echo $row['status']; ?>">
                  <?php echo ucfirst(str_replace("_"," ",$row['status'])); ?>
                </td>
                <td><?php echo $row['created_at']; ?></td>
                <td><?php echo $row['updated_at']; ?></td>
              </tr>
            <?php } ?>
            <?php if(mysqli_num_rows($items) == 0) echo "<tr><td colspan='10'>No inventory records found.</td></tr>"; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</body>
</html>
