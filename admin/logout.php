<?php
require_once __DIR__ . '/../includes/functions.php';
unset($_SESSION['is_admin'], $_SESSION['admin_login']);
header('Location: /exam/admin/login.php');
exit;
