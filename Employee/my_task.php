<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Employee
if ($_SESSION['role'] !== 'employee') {
    echo "❌ Access denied. Only Employee can view this page.";
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// ===== UPDATE TASK STATUS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $task_id = intval($_POST['task_id']);
    $status = $_POST['status'];
    mysqli_query($conn, "UPDATE tasks SET status='$status' WHERE id=$task_id AND assigned_to=$user_id");
    header("Location: my_task.php");
    exit();
}

// ===== FETCH EMPLOYEE TASKS =====
$tasks = mysqli_query($conn, "
    SELECT t.*, p.project_name
    FROM tasks t
    LEFT JOIN projects p ON t.project_id = p.id
    WHERE t.assigned_to=$user_id
    ORDER BY t.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>✅ My Tasks - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #121212; color: #f1f1f1; font-size: 15px; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #1c1c1c; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ccc; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; color: #fff; }
.content { margin-left: 240px; padding: 20px; }
.card { background: #1f1f1f; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 0 15px rgba(0,0,0,0.7); }
table { background: #1f1f1f; border-radius: 10px; width: 100%; }
table th { background: #2c2c2c; color: #fff; font-weight: 600; }
table td { color: #e0e0e0; }
.badge-status { padding:6px 10px; border-radius:8px; font-size:0.9em; }
.badge-pending { background:#ffc107; color:black; }
.badge-inprogress { background:#2196f3; }
.badge-completed { background:#4caf50; }
select { background: #1e1e1e; border:1px solid #555; color:white; border-radius:8px; padding:6px; width:100%; }
select:focus { outline:none; border-color:#2196f3; }
@media(max-width:768px){ .sidebar{width:100%; height:auto; position:relative;} .content{margin-left:0;} }
.btn-update { background:#2196f3; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-update:hover { background:#1976d2; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4>👨‍💼 ERP Employee</h4>
  <p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
  <hr>
  <a href="employee_dashboard.php">🏠 Dashboard</a>
  <a href="my_attendance.php">🕒 My Attendance</a>
  <a href="my_leaves.php">📋 My Leaves</a>
  <a href="my_payroll.php">💰 My Payroll</a>
  <a href="my_task.php" class="bg-dark">✅ My Task</a>
  <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
  <h2>✅ My Tasks</h2>
  <p>View all your assigned tasks and update their status.</p>

  <div class="table-responsive mt-4">
    <table class="table table-dark table-striped table-hover text-center">
      <thead>
        <tr>
          <th>ID</th>
          <th>Task</th>
          <th>Project</th>
          <th>Status</th>
          <th>Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if(mysqli_num_rows($tasks) > 0): ?>
          <?php while($t = mysqli_fetch_assoc($tasks)): ?>
          <tr>
            <td>#<?php echo $t['id']; ?></td>
            <td><?php echo htmlspecialchars($t['task_name']); ?></td>
            <td><?php echo htmlspecialchars($t['project_name'] ?? 'N/A'); ?></td>
            <td>
              <?php
                if($t['status']=='pending') echo "<span class='badge-status badge-pending'>Pending</span>";
                elseif($t['status']=='in-progress') echo "<span class='badge-status badge-inprogress'>In Progress</span>";
                else echo "<span class='badge-status badge-completed'>Completed</span>";
              ?>
            </td>
            <td>
              <form method="POST">
                <input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
                <select name="status" onchange="this.form.submit()">
                  <option value="pending" <?php if($t['status']=='pending') echo 'selected'; ?>>Pending</option>
                  <option value="in-progress" <?php if($t['status']=='in-progress') echo 'selected'; ?>>In Progress</option>
                  <option value="completed" <?php if($t['status']=='completed') echo 'selected'; ?>>Completed</option>
                </select>
                <input type="hidden" name="update_status">
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">You have no tasks assigned.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
