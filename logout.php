<?php
//Unset la date din $_SESSION și resetarea sesiunii curente
session_start();
session_unset();
session_destroy();
header("Location: index.php");
?>
