<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Admin role
if ($_SESSION['role'] !== 'admin') {
    echo "❌ Access denied. Only Admin can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// Fetch all employees
$employees_res = $conn->query("
    SELECT e.id, u.name, e.position, e.department, e.salary, e.join_date, e.status
    FROM employees e
    JOIN users u ON e.user_id = u.id
    ORDER BY e.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Employees - Admin ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1e1e1e; color: white; }
    .sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; }
    .sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
    .sidebar a:hover { background: #333; }
    .content { margin-left: 240px; padding: 20px; }
    table { background: #2a2a2a; border-radius: 10px; }
    th, td { color: white !important; }
    @media(max-width:768px){ .sidebar { position: relative; height: auto; width: 100%; } .content { margin-left: 0; } }
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
  <h2>👨‍💼 All Employees</h2>

  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Position</th>
          <th>Department</th>
          <th>Salary</th>
          <th>Join Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while($emp = $employees_res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $emp['id']; ?></td>
          <td><?php echo $emp['name']; ?></td>
          <td><?php echo $emp['position']; ?></td>
          <td><?php echo $emp['department']; ?></td>
          <td><?php echo number_format($emp['salary'],2); ?></td>
          <td><?php echo $emp['join_date']; ?></td>
          <td><?php echo ucfirst($emp['status']); ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
