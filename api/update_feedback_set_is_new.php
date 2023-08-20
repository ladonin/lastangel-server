<?php
require('@imports.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_recordId = intval($_GET['id']);

if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $_recordId, 400);
}

$_res = $db_mysqli->query("UPDATE feedbacks SET is_new=1 WHERE id=$_recordId");


functions_successOutput($_recordId);
?>