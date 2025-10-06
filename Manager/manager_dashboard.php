<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Manager
if ($_SESSION['role'] !== 'manager') {
    echo "❌ Access denied. Only managers can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// ================== FETCH DATA ================== //
// Employees count
$employees_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM employees"))['total'] ?? 0;

// Total Sales Deals
$sales_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sales"))['total'] ?? 0;

// Pending Tasks
$pending_tasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE status='pending'"))['total'] ?? 0;

// Completed Tasks
$completed_tasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE status='completed'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manager Dashboard - ERP</title>
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
    <h4>⚙️ ERP Manager</h4>
    <p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
    <hr>
<a href="manager_dashboard.php" class="bg-dark">🏠 Dashboard</a>
<a href="view_employees.php">👨‍💼 Employees</a>
<a href="view_sales.php">📊 Sales</a>
<a href="view_product.php">📦 Products</a>
<a href="view_client.php">🧾 Clients</a>
<a href="view_deal.php">💼 Deals</a>
<a href="view_project.php">📁 Projects</a>
<a href="view_tasks.php">✅ Tasks</a>
<a href="../logout.php" class="text-danger">🚪 Logout</a>

  </div>

  <!-- Main Content -->
  <div class="content">
    <h2>📊 Manager Dashboard</h2>
    <p>Here’s an overview of your team's performance.</p>

    <div class="row mt-4 g-3">
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>👨 Employees</h5>
          <h2><?php echo $employees_count; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>📊 Sales Deals</h5>
          <h2><?php echo $sales_count; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>⏳ Pending Tasks</h5>
          <h2><?php echo $pending_tasks; ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card p-3 text-center">
          <h5>✅ Completed Tasks</h5>
          <h2><?php echo $completed_tasks; ?></h2>
        </div>
      </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="mt-4">
      <h4>🧾 Recent Sales Deals</h4>
      <div class="table-responsive">
        <table class="table table-dark table-striped align-middle text-center">
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Deal</th>
              <th>Amount</th>
              <th>Stage</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sales = mysqli_query($conn, "SELECT * FROM sales ORDER BY created_at DESC LIMIT 10");
            while($row = mysqli_fetch_assoc($sales)) {
            ?>
            <tr>
              <td>#<?php echo $row['id']; ?></td>
              <td><?php echo htmlspecialchars($row['client_name']); ?></td>
              <td><?php echo htmlspecialchars($row['deal_name']); ?></td>
              <td>$<?php echo number_format($row['amount'],2); ?></td>
              <td><?php echo ucfirst($row['stage']); ?></td>
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
