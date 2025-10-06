<?php
session_start();
include 'db.php'; // DB connection

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['name'] = $row['name'];

            if ($row['role'] == 'admin') {
                header("Location: Admin/admin_dashboard.php");
            } elseif ($row['role'] == 'hr') {
                header("Location: Hr/hr_dashboard.php");
            } elseif ($row['role'] == 'accountant') {
                header("Location: Accountant/accountant_dashboard.php");
            } elseif ($row['role'] == 'manager') {
                header("Location: Manager/manager_dashboard.php");
            } else {
                header("Location: Employee/employee_dashboard.php");
            }
            exit();
        } else {
            $error = "⚠️ Invalid password!";
        }
    } else {
        $error = "⚠️ No user found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1520607162513-77705c0f0d4a') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(20, 20, 20, 0.9);
            border-radius: 15px;
            padding: 30px;
            color: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.7);
        }
        .form-control {
            background: #222;
            border: none;
            color: #eee;
        }
        .form-control:focus {
            background: #333;
            color: #fff;
            box-shadow: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1f1f1f, #444);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #444, #666);
        }
        h3 {
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="height:100vh;">
    <div class="login-card shadow-lg" style="width:100%; max-width:400px;">
        <h3 class="text-center mb-4">🔑 ERP Login</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter email">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

</body>
</html>
