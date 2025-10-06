<?php
session_start();
include '../db.php';

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: ../index.php");
    exit();
}

// Only Accountant
if($_SESSION['role'] !== 'accountant'){
    echo "❌ Access denied. Only accountants can view this page.";
    exit();
}

$user_name = $_SESSION['name'];

// Handle Add New Inventory
if(isset($_POST['add_inventory'])){
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $status = $_POST['status'];

    $insert = mysqli_query($conn,"INSERT INTO inventory (product_name, sku, category, quantity, price, supplier, status)
    VALUES ('$product_name','$sku','$category','$quantity','$price','$supplier','$status')");

    if($insert){
        header("Location: view_inventory.php");
        exit();
    } else {
        $error = "Failed to add product. SKU might already exist.";
    }
}

// Fetch Inventory
$inventory = mysqli_query($conn, "SELECT * FROM inventory ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inventory - ERP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #1e1e1e; color: white; font-family: 'Segoe UI', sans-serif; }
.sidebar { height: 100vh; background: #111; padding: 20px; position: fixed; width: 220px; transition: all 0.3s; }
.sidebar a { display: block; color: #ddd; padding: 10px; text-decoration: none; margin-bottom: 8px; border-radius: 5px; }
.sidebar a:hover { background: #333; }
.content { margin-left: 240px; padding: 20px; transition: all 0.3s; }
.card { background: linear-gradient(135deg,#2a2a2a,#333); border:none; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.6); margin-bottom:20px; padding:15px; }
.card h5 { color: white; margin-bottom:10px; }
.card form input, .card form select { width:100%; padding:8px; margin-bottom:10px; border-radius:5px; border:none; background:#333; color:white; }
.card form input::placeholder, .card form select::placeholder { color: white; opacity:0.7; }
.btn-add { background:#4caf50; color:white; border:none; padding:8px 15px; border-radius:5px; }
.btn-add:hover { background:#388e3c; }
.btn-delete { background:#f44336; border:none; color:white; padding:5px 10px; border-radius:5px; }
.btn-delete:hover { background:#d32f2f; }
.table { background: #2a2a2a; border-radius: 10px; overflow-x:auto; }
.table thead { background: #444; color: #fff; }
@media (max-width:768px){
  .sidebar{width:100%; height:auto; position:relative;}
  .content{margin-left:0;}
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h4>💰 ERP Accountant</h4>
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
<h2>📦 Inventory Management</h2>

<?php if(isset($error)){ echo "<div class='alert alert-danger'>$error</div>"; } ?>

<!-- Add New Inventory -->
<div class="card">
<h5>➕ Add New Product</h5>
<form method="POST" action="">
<input type="text" name="product_name" placeholder="Product Name" required>
<input type="text" name="sku" placeholder="SKU (Unique)" required>
<input type="text" name="category" placeholder="Category">
<input type="number" name="quantity" placeholder="Quantity" required>
<input type="number" step="0.01" name="price" placeholder="Price" required>
<input type="text" name="supplier" placeholder="Supplier">
<select name="status">
<option value="in_stock">In Stock</option>
<option value="low_stock">Low Stock</option>
<option value="out_of_stock">Out of Stock</option>
</select>
<button type="submit" name="add_inventory" class="btn-add">Add Product</button>
</form>
</div>

<!-- Inventory Table -->
<div class="table-responsive">
<table class="table table-dark table-striped text-center align-middle">
<thead>
<tr>
<th>ID</th>
<th>Product Name</th>
<th>SKU</th>
<th>Category</th>
<th>Quantity</th>
<th>Price</th>
<th>Supplier</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($inventory) > 0){
while($row = mysqli_fetch_assoc($inventory)){ ?>
<tr>
<td>#<?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['product_name']); ?></td>
<td><?php echo htmlspecialchars($row['sku']); ?></td>
<td><?php echo htmlspecialchars($row['category']); ?></td>
<td><?php echo $row['quantity']; ?></td>
<td>$<?php echo number_format($row['price'],2); ?></td>
<td><?php echo htmlspecialchars($row['supplier']); ?></td>
<td><?php echo ucfirst($row['status']); ?></td>
<td>
<a href="delete_inventory.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
</td>
</tr>
<?php }} else { ?>
<tr><td colspan="9">No products found.</td></tr>
<?php } ?>
</tbody>
</table>
</div>

</div>

</body>
</html>
