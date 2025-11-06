<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$current_file = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'];

// Check if user is accessing correct directory based on role
if($user_role == 'superadmin' && strpos($_SERVER['REQUEST_URI'], 'superadmin/') === false) {
    header("Location: dashboard.php");
    exit();
}

if($user_role == 'admin' && strpos($_SERVER['REQUEST_URI'], 'admin/') === false) {
    header("Location: dashboard.php");
    exit();
}
?>