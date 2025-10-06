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

// ================== Handle Add / Delete Payroll ================== //
if (isset($_POST['add_payroll'])) {
    $employee_id = $_POST['employee_id'];
    $month = $_POST['month'];
    $basic_salary = $_POST['basic_salary'];
    $bonus = $_POST['bonus'];
    $deductions = $_POST['deductions'];
    $net_salary = $basic_salary + $bonus - $deductions;
    $payment_date = $_POST['payment_date'];

    $stmt = $conn->prepare("INSERT INTO payroll (employee_id, month, basic_salary, bonus, deductions, net_salary, payment_date) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("isdddds", $employee_id, $month, $basic_salary, $bonus, $deductions, $net_salary, $payment_date);
    $stmt->execute();
    $stmt->close();

    header("Location: payroll.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM payroll WHERE id=$delete_id");
    header("Location: payroll.php");
    exit();
}

// ================== Fetch Payroll ================== //
$payroll_res = $conn->query("
    SELECT p.id, e.id AS emp_id, u.name, p.month, p.basic_salary, p.bonus, p.deductions, p.net_salary, p.payment_date
    FROM payroll p
    JOIN employees e ON p.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    ORDER BY p.payment_date DESC
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
<title>Payroll - HR ERP</title>
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
.btn-delete { margin-left: 5px; }
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
  <h2>💰 Payroll</h2>

  <!-- Add Payroll Form -->
  <button class="btn btn-success btn-add" type="button" data-bs-toggle="collapse" data-bs-target="#addForm">➕ Add Payroll</button>
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
        <div class="col-md-2"><input type="month" name="month" class="form-control" required></div>
        <div class="col-md-2"><input type="number" step="0.01" name="basic_salary" class="form-control" placeholder="Basic Salary" required></div>
        <div class="col-md-2"><input type="number" step="0.01" name="bonus" class="form-control" placeholder="Bonus"></div>
        <div class="col-md-2"><input type="number" step="0.01" name="deductions" class="form-control" placeholder="Deductions"></div>
        <div class="col-md-1"><input type="date" name="payment_date" class="form-control" required></div>
      </div>
      <button type="submit" name="add_payroll" class="btn btn-primary mt-3">Add Payroll</button>
    </form>
  </div>

  <!-- Payroll Table -->
  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Employee</th>
          <th>Month</th>
          <th>Basic Salary</th>
          <th>Bonus</th>
          <th>Deductions</th>
          <th>Net Salary</th>
          <th>Payment Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($p = $payroll_res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $p['id']; ?></td>
          <td><?php echo $p['name']; ?></td>
          <td><?php echo $p['month']; ?></td>
          <td><?php echo number_format($p['basic_salary'],2); ?></td>
          <td><?php echo number_format($p['bonus'],2); ?></td>
          <td><?php echo number_format($p['deductions'],2); ?></td>
          <td><?php echo number_format($p['net_salary'],2); ?></td>
          <td><?php echo $p['payment_date']; ?></td>
          <td>
            <a href="payroll.php?delete_id=<?php echo $p['id']; ?>" 
               onclick="return confirm('Are you sure you want to delete this payroll?');" 
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
