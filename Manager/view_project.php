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

// ===== ADD NEW PROJECT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $name = mysqli_real_escape_string($conn, $_POST['project_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $status = $_POST['status'];

    if (!empty($name)) {
        mysqli_query($conn, "INSERT INTO projects (project_name, description, start_date, end_date, status)
                             VALUES ('$name', '$desc', '$start', '$end', '$status')");
        $success_msg = "✅ Project added successfully!";
        header("Location: view_project.php");
        exit();
    } else {
        $error_msg = "⚠️ Project name is required!";
    }
}

// ===== DELETE PROJECT =====
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM projects WHERE id=$id");
    header("Location: view_project.php");
    exit();
}

// ===== FETCH PROJECTS =====
$projects = mysqli_query($conn, "SELECT * FROM projects ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📁 Projects - ERP Manager</title>
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
.btn-add { background:#4caf50; border:none; color:white; padding:8px 12px; border-radius:5px; }
.btn-add:hover { background:#388e3c; }
.btn-delete { background:#f44336; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-delete:hover { background:#d32f2f; }
.badge-status { padding:6px 10px; border-radius:8px; font-size:0.9em; }
.badge-pending { background:#ffc107; color:black; }
.badge-inprogress { background:#2196f3; }
.badge-completed { background:#4caf50; }
/* Form styles */
.card label { color: white; font-weight:500; }
input, select, textarea { background: #1e1e1e; border:1px solid #555; color:white; border-radius:8px; padding:8px; width:100%; }
input:focus, select:focus, textarea:focus { outline:none; border-color:#2196f3; }
@media (max-width:768px){ .sidebar{width:100%; height:auto; position:relative;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h4>⚙️ ERP Manager</h4>
<p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
<hr>
    <a href="manager_dashboard.php">🏠 Dashboard</a>
    <a href="view_employees.php">👨‍💼 Employees</a>
    <a href="view_sales.php">📊 Sales</a>
    <a href="view_product.php">📦 Products</a>
    <a href="view_client.php">🧾 Clients</a>
    <a href="view_deal.php">💼 Deals</a>
    <a href="view_project.php" class="bg-dark">📁 Projects</a>
    <a href="view_tasks.php">✅ Tasks</a>
    <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
<h2>📁 Project Management</h2>

<?php if(isset($success_msg)) echo "<div class='alert alert-success'>$success_msg</div>"; ?>
<?php if(isset($error_msg)) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

<!-- Add Project Form -->
<div class="card">
<h5>Add New Project</h5>
<form method="POST">
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Project Name</label>
        <input type="text" name="project_name" required>
    </div>
    <div class="col-md-6 mb-3">
        <label>Status</label>
        <select name="status" required>
            <option value="pending">Pending</option>
            <option value="in-progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Start Date</label>
        <input type="date" name="start_date" required>
    </div>
    <div class="col-md-6 mb-3">
        <label>End Date</label>
        <input type="date" name="end_date" required>
    </div>
</div>
<div class="mb-3">
    <label>Description</label>
    <textarea name="description" rows="3" placeholder="Enter project details..."></textarea>
</div>
<button type="submit" name="add_project" class="btn btn-add">+ Add Project</button>
</form>
</div>

<!-- Project Table -->
<div class="table-responsive">
<table class="table table-dark table-striped text-center align-middle">
<thead>
<tr>
<th>ID</th>
<th>Project Name</th>
<th>Description</th>
<th>Start</th>
<th>End</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($projects) > 0) {
    while($p = mysqli_fetch_assoc($projects)) { ?>
<tr>
<td>#<?php echo $p['id']; ?></td>
<td><?php echo htmlspecialchars($p['project_name']); ?></td>
<td><?php echo htmlspecialchars($p['description']); ?></td>
<td><?php echo $p['start_date']; ?></td>
<td><?php echo $p['end_date']; ?></td>
<td>
<?php
if($p['status'] === 'pending') echo "<span class='badge-status badge-pending'>Pending</span>";
elseif($p['status'] === 'in-progress') echo "<span class='badge-status badge-inprogress'>In Progress</span>";
else echo "<span class='badge-status badge-completed'>Completed</span>";
?>
</td>
<td>
<a href="view_project.php?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-delete"
   onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
</td>
</tr>
<?php }} else { ?>
<tr><td colspan="7">No projects found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>

</div>

</body>
</html>
