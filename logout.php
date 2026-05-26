<?php
session_start();
session_destroy();
$_SESSION['mensaje'] = "Sesión cerrada exitosamente.";
header("Location: login.php");
exit();
?>