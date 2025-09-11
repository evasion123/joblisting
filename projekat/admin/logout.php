<?php
// admin/logout.php
require_once __DIR__ . '/../init.php';
unset($_SESSION['admin']);
header('Location: https://studcp.vts.su.ac.rs:10000/virtual-server/link.cgi/147.91.199.133/http://www.evasion.stud.vts.su.ac.rs/');
exit;
