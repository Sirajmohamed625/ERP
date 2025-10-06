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

// ================== Handle Add Employee ================== //
if (isset($_POST['add_employee'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $position = $_POST['position'];
    $department = $_POST['department'];
    $salary = $_POST['salary'];
    $join_date = $_POST['join_date'];

    // Step 1: Create user
    $stmt1 = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
    $role = 'employee';
    $stmt1->bind_param("ssss",$name,$email,$password,$role);
    $stmt1->execute();
    $user_id = $stmt1->insert_id;
    $stmt1->close();

    // Step 2: Create employee linked to user
    $stmt2 = $conn->prepare("INSERT INTO employees (user_id,position,department,salary,join_date) VALUES (?,?,?,?,?)");
    $stmt2->bind_param("issds",$user_id,$position,$department,$salary,$join_date);
    $stmt2->execute();
    $stmt2->close();

    header("Location: employees.php");
    exit();
}

// ================== Handle Delete Employee ================== //
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get user_id first
    $res = $conn->query("SELECT user_id FROM employees WHERE id=$id");
    if($res && $row = $res->fetch_assoc()) {
        $user_id = $row['user_id'];
        $conn->query("DELETE FROM employees WHERE id=$id"); // delete employee
        $conn->query("DELETE FROM users WHERE id=$user_id"); // delete user login
    }
    header("Location: employees.php");
    exit();
}

// ================== Fetch Employees ================== //
$employees_res = $conn->query("
    SELECT e.id, u.name, u.email, e.position, e.department, e.salary, e.join_date, e.status
    FROM employees e
    JOIN users u ON e.user_id = u.id
    ORDER BY e.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employees - HR ERP</title>
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
  <h2>👨‍💼 Employees</h2>

  <!-- Add Employee Form -->
  <button class="btn btn-success btn-add" type="button" data-bs-toggle="collapse" data-bs-target="#addForm">➕ Add New Employee</button>
  <div id="addForm" class="collapse mb-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-4"><input type="text" name="name" placeholder="Full Name" class="form-control" required></div>
        <div class="col-md-4"><input type="email" name="email" placeholder="Email" class="form-control" required></div>
        <div class="col-md-4"><input type="password" name="password" placeholder="Password" class="form-control" required></div>
        <div class="col-md-4"><input type="text" name="position" placeholder="Position" class="form-control"></div>
        <div class="col-md-4"><input type="text" name="department" placeholder="Department" class="form-control"></div>
        <div class="col-md-4"><input type="number" step="0.01" name="salary" placeholder="Salary" class="form-control"></div>
        <div class="col-md-4"><input type="date" name="join_date" class="form-control"></div>
      </div>
      <button type="submit" name="add_employee" class="btn btn-primary mt-3">Add Employee</button>
    </form>
  </div>

  <!-- Employees Table -->
  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Position</th>
          <th>Department</th>
          <th>Salary</th>
          <th>Join Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($emp = $employees_res->fetch_assoc()): ?>
        <tr>
          <td><?php echo $emp['id']; ?></td>
          <td><?php echo $emp['name']; ?></td>
          <td><?php echo $emp['email']; ?></td>
          <td><?php echo $emp['position']; ?></td>
          <td><?php echo $emp['department']; ?></td>
          <td><?php echo $emp['salary']; ?></td>
          <td><?php echo $emp['join_date']; ?></td>
          <td><?php echo ucfirst($emp['status']); ?></td>
          <td>
            <a href="employees.php?delete=<?php echo $emp['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this employee?')">Delete</a>
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
