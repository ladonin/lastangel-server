<?php
require('@imports.php');

$_stmt = $db_mysqli->prepare("SELECT * FROM clinic_photos WHERE id=1");

$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_assoc();

functions_successOutput($_row);
?>