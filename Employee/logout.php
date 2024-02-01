<?php
session_start();
session_unset();
session_destroy();
// header('location:login.php');
header('Location: ../admin/login.php');

?>