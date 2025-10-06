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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $expense_type = mysqli_real_escape_string($conn, $_POST['expense_type']);
    $amount = floatval($_POST['amount']);
    $expense_date = $_POST['expense_date'] ?: NULL;
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $insert = mysqli_query($conn, "INSERT INTO expenses (expense_type, amount, expense_date, notes)
        VALUES ('$expense_type', '$amount', '$expense_date', '$notes')");

    if (!$insert) {
        $error = "Failed to add expense: " . mysqli_error($conn);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM expenses WHERE id = $id");
    header("Location: view_expenses.php");
    exit();
}

// Fetch all expenses
$expenses = mysqli_query($conn, "SELECT * FROM expenses ORDER BY expense_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Expenses - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; transition: all 0.3s; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; transition: all 0.3s; }
.card { background: #2a2a2a; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
.card h5 { color: white; }
.card input, .card select, .card textarea { background: #1e1e1e; color: white; border: 1px solid #555; }
.card input::placeholder, .card textarea::placeholder { color: #ccc; }
.table { background: #2a2a2a; border-radius: 10px; overflow-x: auto; }
.table thead { background: #444; color: #fff; }
.btn-delete { background: #f44336; border: none; color: white; padding: 5px 10px; border-radius: 5px; }
.btn-delete:hover { background: #d32f2f; }

/* Mobile responsiveness */
@media (max-width: 768px) {
  .sidebar { width: 100%; height: auto; position: relative; }
  .content { margin-left: 0; }
  .card form .col-md-3, .card form .col-md-2, .card form .col-md-4 { width: 100%; }
  .card form .row { flex-direction: column; }
  .btn { width: 100%; margin-top: 10px; }
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
<h2>📉 All Expenses</h2>

<?php if(isset($error)) echo "<p class='text-danger'>$error</p>"; ?>

<!-- Add Expense Form -->
<div class="card">
<h5>➕ Add New Expense</h5>
<form method="POST" class="row g-3 mt-2">
  <input type="hidden" name="add_expense" value="1">
  <div class="col-md-3">
    <input type="text" name="expense_type" class="form-control" placeholder="Expense Type" required>
  </div>
  <div class="col-md-2">
    <input type="number" name="amount" step="0.01" class="form-control" placeholder="Amount ($)" required>
  </div>
  <div class="col-md-3">
    <input type="date" name="expense_date" class="form-control">
  </div>
  <div class="col-md-4">
    <textarea name="notes" class="form-control" placeholder="Notes"></textarea>
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-success w-100">Add Expense</button>
  </div>
</form>
</div>

<!-- Expenses Table -->
<div class="table-responsive">
<table class="table table-dark table-striped align-middle text-center">
<thead>
<tr>
<th>ID</th>
<th>Type</th>
<th>Amount</th>
<th>Date</th>
<th>Notes</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($expenses) > 0) { 
  while($row = mysqli_fetch_assoc($expenses)) { ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['expense_type']); ?></td>
<td>$<?php echo number_format($row['amount'],2); ?></td>
<td><?php echo $row['expense_date'] ? date("M d, Y", strtotime($row['expense_date'])) : '-'; ?></td>
<td><?php echo htmlspecialchars($row['notes']); ?></td>
<td>
<a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
</td>
</tr>
<?php } } else { ?>
<tr><td colspan="6">No expenses found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</body>
</html>
