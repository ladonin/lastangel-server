<?php

function donatorsCommon_checkRequestTextData($data) {
	if (
	  !isset($data['card']) || !$data['card'] ||
	  !isset($data['fullname']) || !$data['fullname']
	) {
		functions_errorOutput('Некорректный запрос. Не все данные переданы. '.json_encode($data), 400);
	}
}
?>