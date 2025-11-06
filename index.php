<?php
session_start();

// Redirect to login if not authenticated, otherwise to appropriate dashboard
if(isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if($_SESSION['role'] == 'superadmin') {
        header("Location: superadmin/dashboard.php");
    } else {
        header("Location: admin/dashboard.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>