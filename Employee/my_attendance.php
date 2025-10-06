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

// Get employee record
$emp_res = $conn->query("SELECT * FROM employees WHERE user_id=$user_id");
$employee = $emp_res->fetch_assoc();
$employee_id = $employee['id'] ?? 0;

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM attendance WHERE id=$delete_id AND employee_id=$employee_id");
    header("Location: my_attendance.php");
    exit();
}

// Fetch attendance records
$attendance_res = $conn->query("SELECT * FROM attendance WHERE employee_id=$employee_id ORDER BY date DESC");

// Optional: Handle Check-in
if (isset($_POST['check_in'])) {
    $today = date('Y-m-d');
    $time_now = date('H:i:s');
    $conn->query("INSERT INTO attendance(employee_id, date, check_in) VALUES($employee_id,'$today','$time_now')");
    header("Location: my_attendance.php");
    exit();
}

// Optional: Handle Check-out
if (isset($_POST['check_out'])) {
    $today = date('Y-m-d');
    $time_now = date('H:i:s');
    $conn->query("UPDATE attendance SET check_out='$time_now' WHERE employee_id=$employee_id AND date='$today'");
    header("Location: my_attendance.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Attendance - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; }
table { background: #2a2a2a; border-radius: 10px; }
th, td { color: white !important; }
.card { background: #2a2a2a; border-radius: 12px; padding: 15px; margin-bottom: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.6); }
.btn-delete { margin-left: 5px; }
@media(max-width:768px){ .sidebar { position: relative; height: auto; width: 100%; } .content { margin-left: 0; } }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4>👨‍💼 ERP Employee</h4>
  <p>Welcome, <?php echo $user_name; ?></p>
  <hr>
  <a href="employee_dashboard.php">🏠 Dashboard</a>
  <a href="my_attendance.php">🕒 My Attendance</a>
  <a href="my_leaves.php">📋 My Leaves</a>
  <a href="my_payroll.php">💰 My Payroll</a>
  <a href="my_task.php">✅ My Task</a>
  <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
  <h2>🕒 My Attendance</h2>

  <!-- Check-in / Check-out buttons -->
  <div class="mb-3">
    <form method="post">
        <button type="submit" name="check_in" class="btn btn-success">✅ Check-in</button>
        <button type="submit" name="check_out" class="btn btn-warning">🛑 Check-out</button>
    </form>
  </div>

  <!-- Attendance Table -->
  <div class="table-responsive">
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($att = $attendance_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $att['date']; ?></td>
                <td><?php echo $att['check_in'] ?? '-'; ?></td>
                <td><?php echo $att['check_out'] ?? '-'; ?></td>
                <td><?php echo ucfirst($att['status']); ?></td>
                <td>
                    <a href="my_attendance.php?delete_id=<?php echo $att['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this attendance record?');" 
                       class="btn btn-sm btn-danger btn-delete">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
