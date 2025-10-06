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
    echo "❌ Access denied. Only Admins can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// Fetch all projects
$projects = mysqli_query($conn, "SELECT * FROM projects ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📁 View Projects - ERP Admin</title>
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
.sidebar h4 {
    color: #fff;
    margin-bottom: 15px;
}
.sidebar a {
    display: block;
    color: #ddd;
    padding: 10px;
    text-decoration: none;
    margin-bottom: 8px;
    border-radius: 5px;
    transition: background 0.3s;
}
.sidebar a.bg-dark {
    background: #222;
    color: #fff;
}
.sidebar a:hover {
    background: #333;
}
.sidebar a.text-danger {
    color: #f44336;
}
.content {
    margin-left: 240px;
    padding: 20px;
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
.card {
    background: linear-gradient(135deg, #2a2a2a, #333);
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.6);
    margin-bottom: 20px;
    padding: 15px;
}
.status {
    font-weight: bold;
    text-transform: capitalize;
    padding: 4px 10px;
    border-radius: 5px;
}
.status.pending { background: #856404; color: #fff3cd; }
.status.in-progress { background: #0dcaf0; color: #000; }
.status.completed { background: #198754; color: #fff; }
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
    <h4>⚙️ ERP Admin</h4>
    <p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
    <hr>
    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="users.php">👨 Manage Users</a>
    <a href="view_employees.php">👨‍💼 Employees</a>
    <a href="view_finance.php">💰 Finance</a>
    <a href="view_inventory.php">📦 Inventory</a>
    <a href="view_sales.php">📊 Sales</a>
    <a href="view_products.php">🛠️ Products</a>
    <a href="view_clients.php">🧾 Clients</a>
    <a href="view_projects.php" class="bg-dark">📁 Projects</a>
    <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
    <h2>📁 Project List</h2>
    <p>All projects currently recorded in the ERP system database.</p>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-dark table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Description</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($projects) > 0) {
                        while ($row = mysqli_fetch_assoc($projects)) { ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)); ?>...</td>
                                <td><?php echo $row['start_date']; ?></td>
                                <td><?php echo $row['end_date']; ?></td>
                                <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="6">No projects found.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
