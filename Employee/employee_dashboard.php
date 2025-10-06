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

// ================== Fetch Employee Data ================== //
// Get employee record
$emp_res = $conn->query("SELECT * FROM employees WHERE user_id=$user_id");
$employee = $emp_res->fetch_assoc();
$employee_id = $employee['id'] ?? 0;

// Attendance Today
$attendance_count = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE employee_id=$employee_id AND DATE(date)=CURDATE()");
if ($res) { $row = $res->fetch_assoc(); $attendance_count = $row['total']; }

// Pending Leaves
$leave_count = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM leaves WHERE employee_id=$employee_id AND status='pending'");
if ($res) { $row = $res->fetch_assoc(); $leave_count = $row['total']; }

// Last Payroll
$last_payroll = $conn->query("SELECT * FROM payroll WHERE employee_id=$employee_id ORDER BY id DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Dashboard - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; }
.card { background: #2a2a2a; border: none; border-radius: 12px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.6); text-align: center; }
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
  <h2>📊 Employee Dashboard</h2>
  <p>Overview of your personal metrics.</p>

  <div class="row mt-4 g-3">
    <div class="col-md-4 col-6">
      <div class="card p-3">
        <h5>🕒 Attendance Today</h5>
        <h2><?php echo $attendance_count; ?></h2>
      </div>
    </div>
    <div class="col-md-4 col-6">
      <div class="card p-3">
        <h5>📋 Pending Leaves</h5>
        <h2><?php echo $leave_count; ?></h2>
      </div>
    </div>
    <div class="col-md-4 col-12">
      <div class="card p-3">
        <h5>💰 Last Payroll</h5>
        <?php if($last_payroll): ?>
          <p>Month: <?php echo $last_payroll['month']; ?></p>
          <p>Net Salary: $<?php echo number_format($last_payroll['net_salary'],2); ?></p>
        <?php else: ?>
          <p>No payroll processed yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
