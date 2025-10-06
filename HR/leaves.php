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

// ================== Handle Approve / Reject / Delete ================== //
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $conn->query("UPDATE leaves SET status='approved' WHERE id=$id");
    } elseif ($action === 'reject') {
        $conn->query("UPDATE leaves SET status='rejected' WHERE id=$id");
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM leaves WHERE id=$id");
    }

    header("Location: leaves.php");
    exit();
}

// ================== Fetch Leaves ================== //
$leaves_res = $conn->query("
    SELECT l.id, e.id AS emp_id, u.name, l.start_date, l.end_date, l.reason, l.status
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    ORDER BY l.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Leaves - HR ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; }
table { background: #2a2a2a; border-radius: 10px; }
th, td { color: white !important; }
.btn-approve { margin-right: 5px; }
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
  <h2>📋 Leave Requests</h2>

  <div class="table-responsive">
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Employee</th>
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
          <td><?php echo $leave['name']; ?></td>
          <td><?php echo $leave['start_date']; ?></td>
          <td><?php echo $leave['end_date']; ?></td>
          <td><?php echo $leave['reason']; ?></td>
          <td><?php echo ucfirst($leave['status']); ?></td>
          <td>
            <?php if($leave['status'] === 'pending'): ?>
              <a href="leaves.php?id=<?php echo $leave['id']; ?>&action=approve" class="btn btn-sm btn-success btn-approve">Approve</a>
              <a href="leaves.php?id=<?php echo $leave['id']; ?>&action=reject" class="btn btn-sm btn-danger">Reject</a>
            <?php endif; ?>
            <a href="leaves.php?id=<?php echo $leave['id']; ?>&action=delete" 
               onclick="return confirm('Are you sure you want to delete this leave request?');" 
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
