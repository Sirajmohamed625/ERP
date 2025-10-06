<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Manager
if ($_SESSION['role'] !== 'manager') {
    echo "❌ Access denied. Only managers can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $deal_name = mysqli_real_escape_string($conn, $_POST['deal_name']);
    $amount = floatval($_POST['amount']);
    $stage = $_POST['stage'];

    $insert = mysqli_query($conn, "INSERT INTO sales (client_name, deal_name, amount, stage)
        VALUES ('$client_name', '$deal_name', '$amount', '$stage')");

    if (!$insert) {
        $error = "Failed to add sale: " . mysqli_error($conn);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM sales WHERE id = $id");
    header("Location: view_sales.php");
    exit();
}

// Fetch all sales
$sales = mysqli_query($conn, "SELECT * FROM sales ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; transition: all 0.3s; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; transition: all 0.3s; }
.card { background: #2a2a2a; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
.card h5 { color: white; }
.card input, .card select { background: #1e1e1e; color: white; border: 1px solid #555; }
.card input::placeholder { color: #ccc; }
.table { background: #2a2a2a; border-radius: 10px; overflow-x: auto; }
.table thead { background: #444; color: #fff; }
.status-won { color: #4caf50; font-weight: bold; }
.status-lost { color: #f44336; font-weight: bold; }
.status-new, .status-negotiation { color: #ff9800; font-weight: bold; }
.btn-delete { background: #f44336; border: none; color: white; padding: 5px 10px; border-radius: 5px; }
.btn-delete:hover { background: #d32f2f; }

/* Mobile responsiveness */
@media (max-width: 768px) {
  .sidebar { width: 100%; height: auto; position: relative; }
  .content { margin-left: 0; }
  .card form .col-md-3, .card form .col-md-2 { width: 100%; }
  .card form .row { flex-direction: column; }
  .btn { width: 100%; margin-top: 10px; }
  .table-responsive { overflow-x: auto; }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h4>📊 ERP Manager</h4>
<p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
<hr>
    <a href="manager_dashboard.php" class="bg-dark">🏠 Dashboard</a>
    <a href="view_employees.php">👨‍💼 Employees</a>
    <a href="view_sales.php">📊 Sales</a>
    <a href="view_product.php">📦 Products</a>
    <a href="view_client.php">🧾 Clients</a>
    <a href="view_deal.php">💼 Deals</a>
    <a href="view_project.php">📁 Projects</a>
    <a href="view_tasks.php">✅ Tasks</a>
    <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
<h2>💹 All Sales</h2>

<?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

<!-- Add Sale Form -->
<div class="card">
<h5>➕ Add New Sale</h5>
<form method="POST" class="row g-3 mt-2">
  <input type="hidden" name="add_sale" value="1">
  <div class="col-md-3">
    <input type="text" name="client_name" class="form-control" placeholder="Client Name" required>
  </div>
  <div class="col-md-3">
    <input type="text" name="deal_name" class="form-control" placeholder="Deal Name" required>
  </div>
  <div class="col-md-2">
    <input type="number" name="amount" step="0.01" class="form-control" placeholder="Amount ($)" required>
  </div>
  <div class="col-md-2">
    <select name="stage" class="form-select">
      <option value="new">New</option>
      <option value="negotiation">Negotiation</option>
      <option value="won">Won</option>
      <option value="lost">Lost</option>
    </select>
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-success w-100">Add Sale</button>
  </div>
</form>
</div>

<!-- Sales Table -->
<div class="table-responsive">
<table class="table table-dark table-striped align-middle text-center">
<thead>
<tr>
<th>ID</th>
<th>Client</th>
<th>Deal</th>
<th>Amount</th>
<th>Stage</th>
<th>Created At</th>
<th>Updated At</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($sales) > 0) { 
  while($row = mysqli_fetch_assoc($sales)) { ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['client_name']); ?></td>
<td><?php echo htmlspecialchars($row['deal_name']); ?></td>
<td>$<?php echo number_format($row['amount'],2); ?></td>
<td class="status-<?php echo $row['stage']; ?>"><?php echo ucfirst($row['stage']); ?></td>
<td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
<td><?php echo date("M d, Y", strtotime($row['updated_at'])); ?></td>
<td>
<a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="8">No sales found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</body>
</html>
