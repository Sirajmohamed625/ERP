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

// ================== FETCH SALES STATS ================== //
// Total deals
$total_deals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sales"))['total'] ?? 0;

// Total revenue from won deals
$total_sales_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM sales WHERE stage='won'"))['total'] ?? 0;

// Deals by stage
$new_deals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sales WHERE stage='new'"))['total'] ?? 0;
$negotiation_deals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sales WHERE stage='negotiation'"))['total'] ?? 0;
$won_deals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sales WHERE stage='won'"))['total'] ?? 0;
$lost_deals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sales WHERE stage='lost'"))['total'] ?? 0;

// Fetch recent sales (last 10)
$sales = mysqli_query($conn, "SELECT * FROM sales ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales - ERP Admin</title>
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
    .stage-new { color: #03a9f4; font-weight: bold; }
    .stage-negotiation { color: #ff9800; font-weight: bold; }
    .stage-won { color: #4caf50; font-weight: bold; }
    .stage-lost { color: #f44336; font-weight: bold; }
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
    <h2>📊 Sales Overview</h2>
    <p>Track sales deals, revenue, and deal stages in real-time.</p>

    <!-- Stats Cards -->
    <div class="row mt-4 g-3">
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>Total Deals</h5>
          <h2><?php echo $total_deals; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>Total Revenue (Won Deals)</h5>
          <h2>$<?php echo number_format($total_sales_amount,2); ?></h2>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="card p-3 text-center">
          <h6>New</h6>
          <h3><?php echo $new_deals; ?></h3>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="card p-3 text-center">
          <h6>Negotiation</h6>
          <h3><?php echo $negotiation_deals; ?></h3>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="card p-3 text-center">
          <h6>Won</h6>
          <h3><?php echo $won_deals; ?></h3>
        </div>
      </div>
      <div class="col-md-2 col-6">
        <div class="card p-3 text-center">
          <h6>Lost</h6>
          <h3><?php echo $lost_deals; ?></h3>
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="card mt-4 p-3">
      <h4>🧾 Recent Sales</h4>
      <div class="table-responsive mt-3">
        <table class="table table-dark table-striped align-middle text-center">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Deal</th>
              <th>Amount ($)</th>
              <th>Stage</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row=mysqli_fetch_assoc($sales)) { ?>
              <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                <td><?php echo htmlspecialchars($row['deal_name']); ?></td>
                <td><?php echo number_format($row['amount'],2); ?></td>
                <td class="stage-<?php echo $row['stage']; ?>">
                  <?php echo ucfirst($row['stage']); ?>
                </td>
                <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
              </tr>
            <?php } ?>
            <?php if(mysqli_num_rows($sales)==0) echo "<tr><td colspan='6'>No sales records found.</td></tr>"; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</body>
</html>
