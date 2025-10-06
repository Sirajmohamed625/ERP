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

// Fetch deals with client names
$deals = mysqli_query($conn, "SELECT deals.*, clients.name as client_name 
                             FROM deals 
                             LEFT JOIN clients ON deals.client_id = clients.id 
                             ORDER BY deals.created_at DESC");

// Handle Add Deal form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_deal'])) {
    $client_id = mysqli_real_escape_string($conn, $_POST['client_id']);
    $deal_name = mysqli_real_escape_string($conn, $_POST['deal_name']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $stage = mysqli_real_escape_string($conn, $_POST['stage']);

    if(!empty($deal_name) && !empty($client_id)) {
        mysqli_query($conn, "INSERT INTO deals (client_id, deal_name, amount, stage) VALUES ('$client_id', '$deal_name', '$amount', '$stage')");
        $success_msg = "Deal added successfully!";
        header("Location: view_deal.php"); // refresh to avoid duplicate insert
        exit();
    } else {
        $error_msg = "Deal name and client are required!";
    }
}

// Fetch clients for dropdown
$clients = mysqli_query($conn, "SELECT * FROM clients ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Deals - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; transition: all 0.3s; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; transition: all 0.3s; }
.card { background: linear-gradient(135deg,#2a2a2a,#333); border:none; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.6); margin-bottom:20px; padding:15px; }
.card h5 { color: white; margin-bottom:10px; }
.table { background: #2a2a2a; border-radius: 10px; overflow-x:auto; }
.table thead { background: #444; color: #fff; }
.btn-view { background:#4caf50; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-view:hover { background:#388e3c; }
.btn-delete { background:#f44336; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-delete:hover { background:#d32f2f; }
/* Form styles */
.card label { color: white; font-weight:500; }
input, select { background: #1e1e1e; border:1px solid #555; color:white; border-radius:8px; padding:8px; width:100%; }
input:focus, select:focus { outline:none; border-color:#2196f3; }
@media (max-width:768px){ .sidebar{width:100%; height:auto; position:relative;} .content{margin-left:0;} }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h4>⚙️ ERP Manager</h4>
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
<h2>💼 Deals Management</h2>

<?php if(isset($success_msg)) { echo "<div class='alert alert-success'>$success_msg</div>"; } ?>
<?php if(isset($error_msg)) { echo "<div class='alert alert-danger'>$error_msg</div>"; } ?>

<!-- Add Deal Form -->
<div class="card">
<h5>Add New Deal</h5>
<form method="POST">
<div class="mb-3">
<label>Client</label>
<select name="client_id" required>
<option value="">Select Client</option>
<?php while($c = mysqli_fetch_assoc($clients)) { ?>
<option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
<?php } ?>
</select>
</div>
<div class="mb-3">
<label>Deal Name</label>
<input type="text" name="deal_name" required>
</div>
<div class="mb-3">
<label>Amount</label>
<input type="number" step="0.01" name="amount">
</div>
<div class="mb-3">
<label>Stage</label>
<select name="stage">
<option value="new">New</option>
<option value="negotiation">Negotiation</option>
<option value="won">Won</option>
<option value="lost">Lost</option>
</select>
</div>
<button type="submit" name="add_deal" class="btn btn-view">Add Deal</button>
</form>
</div>

<!-- Deals Table -->
<div class="table-responsive">
<table class="table table-dark table-striped text-center align-middle">
<thead>
<tr>
<th>ID</th>
<th>Client</th>
<th>Deal Name</th>
<th>Amount</th>
<th>Stage</th>
<th>Created At</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($deals) > 0) {
    while($row = mysqli_fetch_assoc($deals)) { ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['client_name']); ?></td>
<td><?php echo htmlspecialchars($row['deal_name']); ?></td>
<td>$<?php echo number_format($row['amount'],2); ?></td>
<td><?php echo ucfirst($row['stage']); ?></td>
<td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
<td>
<a href="delete_deal.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this deal?');">Delete</a>
</td>
</tr>
<?php }} else { ?>
<tr><td colspan="7">No deals found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>

</div>

</body>
</html>
