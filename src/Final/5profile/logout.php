<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: ../3main/1new.php");
exit();
?>
