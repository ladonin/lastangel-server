<?php
require('@imports.php');

if (!isset($_GET['id']) || !intval($_GET['id'])) {
	functions_errorOutput('Некорректный запрос. Не передан id.', 400);
}

$_recordId = intval($_GET['id']);

$_limitTimeCondition = time() - 3600*24*30;
$_stmt = $db_mysqli->prepare("SELECT * FROM volunteers WHERE id=$_recordId");
$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_assoc();

functions_successOutput($_row);
?>