<?php
require('@imports.php');

if (!isset($_GET['id']) || !intval($_GET['id'])) {
	functions_errorOutput('Некорректный запрос. Не передан id.', 400);
}

$_recordId = intval($_GET['id']);

$_stmt = $db_mysqli->prepare("SELECT * FROM `collections` WHERE animal_id=$_recordId AND status=1");
$_stmt->execute();
$_result = $_stmt->get_result();
$_data = $_result->fetch_all( MYSQLI_ASSOC );

functions_successOutput($_data);
?>