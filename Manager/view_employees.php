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

// Fetch employees
$employees = mysqli_query($conn, "SELECT * FROM users WHERE role='employee' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employees - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; transition: all 0.3s; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; transition: all 0.3s; }
.card { background: linear-gradient(135deg,#2a2a2a,#333); border:none; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.6); margin-bottom:20px; padding:15px; }
.card h5 { color: white; margin-bottom:10px; }
.table { background: #2a2a2a; border-radius: 10px; overflow-x:auto; }
.table thead { background: #444; color: #fff; }
.btn-view { background:#4caf50; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-view:hover { background:#388e3c; }
.btn-delete { background:#f44336; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-delete:hover { background:#d32f2f; }
@media (max-width:768px){
  .sidebar{width:100%; height:auto; position:relative;}
  .content{margin-left:0;}
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
<h2>👨‍💼 All Employees</h2>

<div class="table-responsive">
<table class="table table-dark table-striped text-center align-middle">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Created At</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($employees) > 0) {
    while($row = mysqli_fetch_assoc($employees)) { ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo ucfirst($row['role']); ?></td>
<td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
<td>

<a href="delete_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
</td>
</tr>
<?php }} else { ?>
<tr><td colspan="6">No employees found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>

</body>
</html>
