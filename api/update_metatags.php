<?php
require "@imports.php";

auth_verify([$ADMIN_ROLE]);

$_json = file_get_contents("php://input");

$_stmt = $db_mysqli->prepare("
	UPDATE metatags
	SET
		data=?,
		updated=?
	WHERE id=1");
$_now = time();

$_stmt->bind_param("si", $_json, $_now);
$_stmt->execute();

functions_successOutput(true);
?>
