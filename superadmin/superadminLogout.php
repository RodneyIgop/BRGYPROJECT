<?php
session_start();
session_destroy();
header('Location: superadminLogin.php');
exit;
?>
