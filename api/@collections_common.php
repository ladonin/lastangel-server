<?php

function collectionsCommon_checkRequestTextData($data) {
	if (
	  !isset($data['name']) || !$data['name'] ||
	  !isset($data['short_description']) || !$data['short_description'] ||
	  !isset($data['description']) || !$data['description'] ||
	  !isset($data['type']) || !$data['type'] || ($data['type'] > 5) || ($data['type'] < 1) ||
	  !isset($data['status']) || !$data['status'] || ($data['status'] > 3) || ($data['status'] < 1) ||
	  (($data['type'] < 3) && (!isset($data['animal_id']) || $data['animal_id'] < 1)) ||
	  !isset($data['target_sum']) || !$data['target_sum']
	) {
		functions_errorOutput('Некорректный запрос. Не все данные переданы. '.json_encode($data), 400);
	}
}

// Возможное закрытие/открытие сбора
function collectionsCommon_updateCollectionStatus($id) {
	global $db_mysqli;
	$_res = $db_mysqli->query("SELECT *, 
		(SELECT SUM(sum) as sum FROM `donations` WHERE type=2 AND target_id = $id) as collected
		FROM collections WHERE id=$id");
	$_row = $_res->fetch_assoc();
var_dump($_row);
	$_status = false;
	// Если сбор открыт/закрыт
	if ($_row['status'] === '1' && ($_row['target_sum'] === '0' || (floatval($_row['target_sum']) <= floatval($_row['collected'])))) {
		$_status = '3';
	} else if ($_row['status'] === '3' && (floatval($_row['collected']) > 0) && (floatval($_row['target_sum']) > floatval($_row['collected']))) {
		$_status = '1';
	}
	var_dump($_status);
	if ($_status) {
		$db_mysqli->query("UPDATE collections SET status = $_status WHERE id=$id");
	}
}
?>
