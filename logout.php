<?php
require_once __DIR__ . '/includes/functions.php';
unset($_SESSION['user_id'], $_SESSION['user_login']);
header('Location: /exam/index.php');
exit;
