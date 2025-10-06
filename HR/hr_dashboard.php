<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only HR role
if ($_SESSION['role'] !== 'hr') {
    echo "❌ Access denied. Only HR can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// ================== Fetch HR Metrics ================== //
// Total Employees
$employees_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $employees_count = $row['total'];
}

// Total Attendance Today
$attendance_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE DATE(check_in) = CURDATE()");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $attendance_count = $row['total'];
}

// Total Leave Requests
$leave_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM leaves WHERE status='pending'");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $leave_count = $row['total'];
}

// Total Payroll Processed
$payroll_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM payroll");
if ($res) {
    $row = mysqli_fetch_assoc($res);
    $payroll_count = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>HR Dashboard - ERP</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1e1e1e; color: white; }
    .sidebar {
      height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px;
    }
    .sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
    .sidebar a:hover { background: #333; }
    .content { margin-left: 240px; padding: 20px; }
    .card { background: #2a2a2a; border: none; border-radius: 12px; color: white; box-shadow: 0 0 10px rgba(0,0,0,0.6); }
    @media(max-width:768px){ .sidebar { position: relative; height: auto; width: 100%; } .content { margin-left: 0; } }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4>👩‍💼 ERP HR</h4>
  <p>Welcome, <?php echo $user_name; ?></p>
  <hr>
  <a href="hr_dashboard.php">🏠 Dashboard</a>
  <a href="employees.php">👨‍💼 Employees</a>
  <a href="attendance.php">🕒 Attendance</a>
  <a href="leaves.php">📋 Leaves</a>
  <a href="payroll.php">💰 Payroll</a>
  <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
  <h2>📊 HR Dashboard</h2>
  <p>Overview of HR operations.</p>

  <div class="row mt-4">
    <div class="col-md-3 mb-3">
      <div class="card p-3 text-center">
        <h5>👨 Employees</h5>
        <h2><?php echo $employees_count; ?></h2>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card p-3 text-center">
        <h5>🕒 Attendance Today</h5>
        <h2><?php echo $attendance_count; ?></h2>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card p-3 text-center">
        <h5>📋 Pending Leaves</h5>
        <h2><?php echo $leave_count; ?></h2>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card p-3 text-center">
        <h5>💰 Payroll Processed</h5>
        <h2><?php echo $payroll_count; ?></h2>
      </div>
    </div>
  </div>
</div>

</body>
</html>
