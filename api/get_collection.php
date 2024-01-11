<?php
require "@imports.php";

if (!isset($_GET["id"]) || !intval($_GET["id"])) {
    functions_errorOutput("Некорректный запрос. Не передан id.", 400);
}

$_recordId = intval($_GET["id"]);

$_stmt = $db_mysqli->prepare("
	SELECT
		*,
		(SELECT SUM(sum) as sum FROM `donations` WHERE type=2 AND target_id = $_recordId) as collected,
		CASE
			WHEN collections.type = '1' OR collections.type = '2' THEN (SELECT name FROM `animals` WHERE id = collections.animal_id)
			ELSE ''
			END as animal_name
	FROM collections
	WHERE id=$_recordId
	");

$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_assoc();

functions_successOutput($_row);
?>
