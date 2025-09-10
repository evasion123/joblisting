<?php
// admin/logout.php
require_once __DIR__ . '/../init.php';
unset($_SESSION['admin']);
header('Location: /');
exit;
