<?php
require('@imports.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_recordId = intval($_GET['id']);


if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $id, 400);
}

// Удаляем из базы
$db_mysqli->query("DELETE FROM donations WHERE id = '".$_recordId."'");

functions_successOutput($_recordId);
?>