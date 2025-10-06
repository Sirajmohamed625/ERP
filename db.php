<?php
$host = "localhost";     // Database Host
$user = "root";          // Database Username
$pass = "";              // Database Password
$dbname = "erp_db";      // Database Name

// Create Connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check Connection
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// Optional: set charset
mysqli_set_charset($conn, "utf8mb4");
?>
