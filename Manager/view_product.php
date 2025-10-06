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

// Handle Add Product form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);

    if(!empty($product_name)) {
        mysqli_query($conn, "INSERT INTO products (product_name, stock, price, supplier) VALUES ('$product_name', $stock, $price, '$supplier')");
        $success_msg = "Product added successfully!";
    } else {
        $error_msg = "Product name is required!";
    }
}

// Fetch all products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products - ERP</title>
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
input, textarea { background: #1e1e1e; border:1px solid #555; color:white; border-radius:8px; padding:8px; width:100%; }
input:focus, textarea:focus { outline:none; border-color:#2196f3; }
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
<h2>📦 Products Management</h2>

<?php if(isset($success_msg)) { echo "<div class='alert alert-success'>$success_msg</div>"; } ?>
<?php if(isset($error_msg)) { echo "<div class='alert alert-danger'>$error_msg</div>"; } ?>

<!-- Add Product Form -->
<div class="card">
<h5>Add New Product</h5>
<form method="POST">
<div class="mb-3">
<label>Product Name</label>
<input type="text" name="product_name" required>
</div>
<div class="mb-3">
<label>Stock Quantity</label>
<input type="number" name="stock" min="0" required>
</div>
<div class="mb-3">
<label>Price ($)</label>
<input type="number" step="0.01" min="0" name="price" required>
</div>
<div class="mb-3">
<label>Supplier</label>
<input type="text" name="supplier">
</div>
<button type="submit" name="add_product" class="btn btn-view">Add Product</button>
</form>
</div>

<!-- Products Table -->
<div class="table-responsive">
<table class="table table-dark table-striped text-center align-middle">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Stock</th>
<th>Price ($)</th>
<th>Supplier</th>
<th>Created At</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($products) > 0) {
    while($row = mysqli_fetch_assoc($products)) { ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['product_name']); ?></td>
<td><?php echo $row['stock']; ?></td>
<td><?php echo number_format($row['price'],2); ?></td>
<td><?php echo htmlspecialchars($row['supplier']); ?></td>
<td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
<td>
<a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
</td>
</tr>
<?php }} else { ?>
<tr><td colspan="7">No products found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>

</div>

</body>
</html>
