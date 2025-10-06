<?php
session_start();
include '../db.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only admin
if ($_SESSION['role'] !== 'admin') {
    echo "❌ Access denied. Only admins can manage users.";
    exit();
}

// ================== ADD USER ================== //
if (isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    mysqli_query($conn, $query);
    header("Location: users.php?success=1");
    exit();
}

// ================== DELETE USER ================== //
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id=$delete_id");
    header("Location: users.php?deleted=1");
    exit();
}

// Fetch all users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - ERP Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1e1e1e; color: white; }
    .card { background: #2a2a2a; border: none; border-radius: 12px; color: white; }
    .table { color: white; }
    .sidebar {
      height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px;
    }
    .sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
    .sidebar a:hover { background: #333; }
    .content { margin-left: 240px; padding: 20px; }
    @media(max-width:768px){ .sidebar { position: relative; height: auto; width: 100%; } .content { margin-left: 0; } }
  </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h4>⚙️ ERP Admin</h4>
  <p>Welcome, <?php echo $_SESSION['name']; ?></p>
  <hr>
    <a href="admin_dashboard.php" class="bg-dark">🏠 Dashboard</a>
    <a href="users.php">👨 Manage Users</a>
    <a href="view_employees.php">👨‍💼 Employees</a>
    <a href="view_finance.php">💰 Finance</a>
    <a href="view_inventory.php">📦 Inventory</a>
    <a href="view_sales.php">📊 Sales</a>
    <a href="view_products.php">📦 Products</a>
    <a href="view_clients.php">🧾 Clients</a>
    <a href="view_projects.php">📁 Projects</a>
    <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Content -->
<div class="content">
  <h2>👥 Manage Users</h2>

  <!-- Add User Form -->
  <div class="card p-3 mt-3">
    <h5>Add New User</h5>
    <form method="POST">
      <div class="row">
        <div class="col-md-3 mb-2">
          <input type="text" name="name" class="form-control" placeholder="Full Name" required>
        </div>
        <div class="col-md-3 mb-2">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-md-2 mb-2">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="col-md-2 mb-2">
          <select name="role" class="form-select" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="employee">Employee</option>
            <option value="accountant">Accountant</option>
            <option value="hr">HR</option>
          </select>
        </div>
        <div class="col-md-2 mb-2">
          <button type="submit" name="add_user" class="btn btn-success w-100">Add</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Users List -->
  <div class="card p-3 mt-4">
    <h5>All Users</h5>
    <table class="table table-dark table-striped mt-3">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo ucfirst($row['role']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
              <?php if($row['id'] != $_SESSION['user_id']) { ?>
                <a href="users.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
              <?php } else { ?>
                <span class="text-warning">You</span>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
