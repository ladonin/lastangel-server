<?php
require "@imports.php";

if (!isset($_GET["id"]) || !intval($_GET["id"])) {
    functions_errorOutput("Некорректный запрос. Не передан id.", 400);
}

$_recordId = intval($_GET["id"]);

$_limitTimeCondition = time() - 3600 * 24 * 30;
$_stmt = $db_mysqli->prepare("
	SELECT
		*,
		(SELECT SUM(sum) as sum FROM `donations` WHERE type= 1 AND created >= $_limitTimeCondition AND target_id = $_recordId) as collected,
		(SELECT count(*) as need_medicine FROM `collections` WHERE type= 1 AND animal_id=animals.id AND status=1) as need_medicine
	FROM animals
	WHERE id=$_recordId");
$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_assoc();

functions_successOutput($_row);
?>
