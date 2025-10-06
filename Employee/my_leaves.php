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

// Handle new leave request
if (isset($_POST['submit_leave'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $conn->real_escape_string($_POST['reason']);
    $conn->query("INSERT INTO leaves(employee_id, start_date, end_date, reason, status) VALUES($employee_id,'$start_date','$end_date','$reason','pending')");
    header("Location: my_leaves.php");
    exit();
}

// Handle delete leave
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM leaves WHERE id=$delete_id AND employee_id=$employee_id");
    header("Location: my_leaves.php");
    exit();
}

// Fetch leave requests
$leaves_res = $conn->query("SELECT * FROM leaves WHERE employee_id=$employee_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Leaves - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #121212; color: #f1f1f1; font-size: 15px; }
.sidebar { height: 100vh; background: #1c1c1c; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ccc; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; color: #fff; }
.content { margin-left: 240px; padding: 20px; }
.card { background: #1f1f1f; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 0 15px rgba(0,0,0,0.7); }
table { background: #1f1f1f; border-radius: 10px; width: 100%; }
table th { background: #2c2c2c; color: #fff; font-weight: 600; }
table td { color: #e0e0e0; }
input, textarea { background: #2c2c2c; color: #f1f1f1; border: 1px solid #555; border-radius: 6px; }
input::placeholder, textarea::placeholder { color: #ccc; }
button { border-radius: 6px; }
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
  <h2>📋 My Leaves</h2>

  <!-- New Leave Form -->
  <div class="card">
    <h5 style="color:#fff;">Request New Leave</h5>
    <form method="post" class="row g-3 mt-2">
      <div class="col-md-4">
        <label class="form-label" style="color:#f1f1f1;">Start Date</label>
        <input type="date" name="start_date" class="form-control" placeholder="Start Date" required>
      </div>
      <div class="col-md-4">
        <label class="form-label" style="color:#f1f1f1;">End Date</label>
        <input type="date" name="end_date" class="form-control" placeholder="End Date" required>
      </div>
      <div class="col-md-12">
        <label class="form-label" style="color:#f1f1f1;">Reason</label>
        <textarea name="reason" class="form-control" rows="2" placeholder="Enter leave reason" required></textarea>
      </div>
      <div class="col-md-12">
        <button type="submit" name="submit_leave" class="btn btn-primary mt-2">Submit Leave</button>
      </div>
    </form>
  </div>

  <!-- Leave Table -->
  <div class="table-responsive mt-4">
    <table class="table table-dark table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($leave = $leaves_res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $leave['id']; ?></td>
          <td><?php echo $leave['start_date']; ?></td>
          <td><?php echo $leave['end_date']; ?></td>
          <td><?php echo $leave['reason']; ?></td>
          <td><?php echo ucfirst($leave['status']); ?></td>
          <td>
            <a href="my_leaves.php?delete_id=<?php echo $leave['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this leave request?');" 
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
