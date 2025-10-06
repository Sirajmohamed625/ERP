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

// ===== ADD NEW TASK =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $project_id = mysqli_real_escape_string($conn, $_POST['project_id']);
    $assigned_to = mysqli_real_escape_string($conn, $_POST['assigned_to']);
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);

    if(!empty($project_id) && !empty($assigned_to) && !empty($task_name)) {
        mysqli_query($conn, "INSERT INTO tasks (project_id, assigned_to, task_name) VALUES ('$project_id','$assigned_to','$task_name')");
        header("Location: view_tasks.php");
        exit();
    } else {
        $error_msg = "⚠️ All fields are required!";
    }
}

// ===== DELETE TASK =====
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tasks WHERE id=$id");
    header("Location: view_tasks.php");
    exit();
}

// ===== FETCH TASKS =====
$tasks = mysqli_query($conn, "
    SELECT t.*, p.project_name, u.name as employee_name
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    LEFT JOIN users u ON t.assigned_to = u.id
    ORDER BY t.id DESC
");

// ===== FETCH EMPLOYEES =====
$employees = mysqli_query($conn, "SELECT * FROM users WHERE role='employee' ORDER BY name ASC");

// ===== FETCH PROJECTS =====
$projects = mysqli_query($conn, "SELECT * FROM projects ORDER BY project_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>✅ Tasks - ERP Manager</title>
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
    <a href="view_project.php">📁 Projects</a>
    <a href="view_tasks.php" class="bg-dark">✅ Tasks</a>
    <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
<h2>✅ Task Management</h2>

<?php if(isset($error_msg)) echo "<div class='alert alert-danger'>$error_msg</div>"; ?>

<!-- Add Task Form -->
<div class="card">
<h5>Assign New Task</h5>
<form method="POST">
<div class="row">
    <div class="col-md-4 mb-3">
        <label>Project</label>
        <select name="project_id" required>
            <option value="">Select Project</option>
            <?php while($p = mysqli_fetch_assoc($projects)) { ?>
            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['project_name']); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label>Assign To (Employee)</label>
        <select name="assigned_to" required>
            <option value="">Select Employee</option>
            <?php while($e = mysqli_fetch_assoc($employees)) { ?>
            <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label>Task Name</label>
        <input type="text" name="task_name" required>
    </div>
</div>
<button type="submit" name="add_task" class="btn btn-add">+ Assign Task</button>
</form>
</div>

<!-- Tasks Table -->
<div class="table-responsive">
<table class="table table-dark table-striped text-center align-middle">
<thead>
<tr>
<th>ID</th>
<th>Task</th>
<th>Project</th>
<th>Assigned To</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($tasks) > 0) {
    while($t = mysqli_fetch_assoc($tasks)) { ?>
<tr>
<td>#<?php echo $t['id']; ?></td>
<td><?php echo htmlspecialchars($t['task_name']); ?></td>
<td><?php echo htmlspecialchars($t['project_name'] ?? 'N/A'); ?></td>
<td><?php echo htmlspecialchars($t['employee_name'] ?? 'N/A'); ?></td>
<td>
<?php
if($t['status'] === 'pending') echo "<span class='badge-status badge-pending'>Pending</span>";
elseif($t['status'] === 'in-progress') echo "<span class='badge-status badge-inprogress'>In Progress</span>";
else echo "<span class='badge-status badge-completed'>Completed</span>";
?>
</td>
<td>
<a href="view_tasks.php?delete=<?php echo $t['id']; ?>" class="btn btn-sm btn-delete"
   onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
</td>
</tr>
<?php }} else { ?>
<tr><td colspan="6">No tasks found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
