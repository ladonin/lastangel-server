<?php

function newsCommon_checkRequestTextData($data) {
	if (
	  !isset($data['name']) || !$data['name'] ||
	  !isset($data['short_description']) || !$data['short_description'] ||
	  !isset($data['description']) || !$data['description']
	) {
		functions_errorOutput('Некорректный запрос. Не все данные переданы. '.json_encode($data), 400);
	}
}
?>
