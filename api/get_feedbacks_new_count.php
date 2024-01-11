<?php
require "@imports.php";

$_res = $db_mysqli->query(
    "SELECT COUNT(*) as count FROM feedbacks WHERE is_new=1"
);
$_row = $_res->fetch_assoc();

functions_successOutput($_row["count"]);
?>
