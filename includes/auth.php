<?php
// includes/auth.php
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function require_admin() {
    if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php"); // Assuming this is called from admin/
        exit;
    }
}
?>
