<?php
require_once __DIR__ . '/../includes/auth.php';
logout();
header('Location: /backend/admin/login.php');
exit();
