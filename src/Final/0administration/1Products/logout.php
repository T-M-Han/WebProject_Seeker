<?php
session_start();
session_destroy();
header("Location: ../0login/adminlogin.php");
exit();
?>
