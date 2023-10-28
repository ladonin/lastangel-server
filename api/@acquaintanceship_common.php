<?php

function acquaintanceshipCommon_checkRequestTextData($data) {
	if (
	  !isset($data['description']) || !$data['description']
	) {
		functions_errorOutput('Некорректный запрос. Не все данные переданы. '.json_encode($data), 400);
	}
}
?>
