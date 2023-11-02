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
?>
