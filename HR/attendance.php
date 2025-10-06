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

// ================== Handle Add Attendance ================== //
if (isset($_POST['add_attendance'])) {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, check_in, check_out, status) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss", $employee_id, $date, $check_in, $check_out, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: attendance.php");
    exit();
}

// ================== Handle Delete Attendance ================== //
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM attendance WHERE id=$delete_id");
    header("Location: attendance.php");
    exit();
}

// ================== Fetch Attendance ================== //
$attendance_res = $conn->query("
    SELECT a.id, e.id AS emp_id, u.name, a.date, a.check_in, a.check_out, a.status
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    ORDER BY a.date DESC, a.id DESC
");

// Fetch all employees for add form
$employees_list = $conn->query("
    SELECT e.id, u.name FROM employees e
    JOIN users u ON e.user_id = u.id
    ORDER BY u.name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance - HR ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; }
table { background: #2a2a2a; border-radius: 10px; }
th, td { color: white !important; }
.form-control { background: #1e1e1e; color: white; border: 1px solid #444; }
.btn-add { margin-bottom: 15px; }
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
  <h2>🕒 Attendance</h2>

  <!-- Add Attendance Form -->
  <button class="btn btn-success btn-add" type="button" data-bs-toggle="collapse" data-bs-target="#addForm">➕ Add Attendance</button>
  <div id="addForm" class="collapse mb-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-3">
          <select name="employee_id" class="form-control" required>
            <option value="">Select Employee</option>
            <?php while($emp = $employees_list->fetch_assoc()): ?>
              <option value="<?php echo $emp['id']; ?>"><?php echo $emp['name']; ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-2"><input type="date" name="date" class="form-control" required></div>
        <div class="col-md-2"><input type="time" name="check_in" class="form-control" required></div>
        <div class="col-md-2"><input type="time" name="check_out" class="form-control"></div>
        <div class="col-md-3">
          <select name="status" class="form-control">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="leave">Leave</option>
          </select>
        </div>
      </div>
      <button type="submit" name="add_attendance" class="btn btn-primary mt-3">Add Attendance</button>
    </form>
  </div>

  <!-- Attendance Table -->
  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Employee</th>
          <th>Date</th>
          <th>Check In</th>
          <th>Check Out</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($att = $attendance_res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $att['id']; ?></td>
          <td><?php echo $att['name']; ?></td>
          <td><?php echo $att['date']; ?></td>
          <td><?php echo $att['check_in']; ?></td>
          <td><?php echo $att['check_out'] ?: '-'; ?></td>
          <td><?php echo ucfirst($att['status']); ?></td>
          <td>
            <a href="attendance.php?delete_id=<?php echo $att['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this record?');" 
               class="btn btn-sm btn-danger">Delete</a>
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
