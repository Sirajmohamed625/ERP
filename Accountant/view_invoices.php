<?php
session_start();
include '../db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Only Accountant
if ($_SESSION['role'] !== 'accountant') {
    echo "❌ Access denied. Only accountants can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_invoice'])) {
    $client_name = mysqli_real_escape_string($conn, $_POST['client_name']);
    $amount = floatval($_POST['amount']);
    $status = $_POST['status'];
    $due_date = $_POST['due_date'] ?: NULL;

    $insert = mysqli_query($conn, "INSERT INTO invoices (client_name, amount, status, due_date) 
        VALUES ('$client_name', '$amount', '$status', '$due_date')");

    if (!$insert) {
        $error = "Failed to add invoice: " . mysqli_error($conn);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM invoices WHERE id = $id");
    header("Location: view_invoices.php");
    exit();
}

// Fetch all invoices
$invoices = mysqli_query($conn, "SELECT * FROM invoices ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoices - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; }
.card { background: #2a2a2a; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
.card h5 { color: white; } /* Heading white */
.table { background: #2a2a2a; border-radius: 10px; overflow: hidden; }
.table thead { background: #444; color: #fff; }
.status-paid { color: #4caf50; font-weight: bold; }
.status-unpaid { color: #ff9800; font-weight: bold; }
.status-overdue { color: #f44336; font-weight: bold; }
.btn-delete { background: #f44336; border: none; color: white; padding: 5px 10px; border-radius: 5px; }
.btn-delete:hover { background: #d32f2f; }
/* Mobile responsiveness */
@media (max-width: 768px) {
  .sidebar { width: 100%; height: auto; position: relative; }
  .content { margin-left: 0; }
  .card form .col-md-3, .card form .col-md-2 { width: 100%; }
  .card form .row { flex-direction: column; }
  .btn { width: 100%; }
  .table-responsive { overflow-x: auto; }

}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h4>💼 ERP Accountant</h4>
<p>Welcome, <?php echo htmlspecialchars($user_name); ?></p>
<hr>
  <a href="accountant_dashboard.php" class="bg-dark">🏠 Dashboard</a>
  <a href="view_invoices.php">💵 Invoices</a>
  <a href="view_expenses.php">📉 Expenses</a>
  <a href="view_inventory.php">📦 Inventory</a>
  <a href="../logout.php" class="text-danger">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="content">
<h2>💵 All Invoices</h2>

<?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

<!-- Add Invoice Form -->
<div class="card">
<h5>➕ Add New Invoice</h5>
<form method="POST" class="row g-3 mt-2">
  <input type="hidden" name="add_invoice" value="1">
  <div class="col-md-3">
    <input type="text" name="client_name" class="form-control" placeholder="Client Name" required>
  </div>
  <div class="col-md-2">
    <input type="number" name="amount" step="0.01" class="form-control" placeholder="Amount ($)" required>
  </div>
  <div class="col-md-2">
    <select name="status" class="form-select">
      <option value="unpaid">Unpaid</option>
      <option value="paid">Paid</option>
      <option value="overdue">Overdue</option>
    </select>
  </div>
  <div class="col-md-3">
    <input type="date" name="due_date" class="form-control">
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-success w-100">Add Invoice</button>
  </div>
</form>
</div>

<!-- Invoices Table -->
<div class="table-responsive">
<table class="table table-dark table-striped align-middle text-center">
<thead>
<tr>
<th>ID</th>
<th>Client</th>
<th>Amount</th>
<th>Status</th>
<th>Due Date</th>
<th>Created At</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($invoices) > 0) { 
  while($row = mysqli_fetch_assoc($invoices)) { ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['client_name']); ?></td>
<td>$<?php echo number_format($row['amount'],2); ?></td>
<td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
<td><?php echo $row['due_date'] ? date("M d, Y", strtotime($row['due_date'])) : '-'; ?></td>
<td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
<td>
<a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this invoice?');">Delete</a>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="7">No invoices found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</body>
</html>
