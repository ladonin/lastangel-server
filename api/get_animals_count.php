<?php
require "@imports.php";

$_res = $db_mysqli->query(
    "SELECT
		(SELECT COUNT(*) FROM animals WHERE status != 5 AND status != 6 AND is_published=1) as at_shelter,
		(SELECT COUNT(*) FROM animals WHERE status = 5 AND is_published=1) as at_home"
);
$_row = $_res->fetch_assoc();

functions_successOutput($_row);
?>
