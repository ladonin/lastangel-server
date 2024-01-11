<?php
require "@imports.php";

$_limitTimeCondition = time() - 3600 * 24 * 30;
$_stmt = $db_mysqli->prepare("
	SELECT *
	FROM
		(
			SELECT
				*,
				(
					SELECT
						SUM(sum) as sum
					FROM `donations`
					WHERE
						type= 1
						AND created >= $_limitTimeCondition
						AND target_id = animals.id
				) as collected
			FROM animals
			WHERE
				status != 5
				AND status != 6
				AND is_published=1
			ORDER by collected ASC, status DESC
			LIMIT 10
		) t1
	ORDER BY RAND()
	LIMIT 1
");
$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_assoc();

functions_successOutput($_row);
?>
