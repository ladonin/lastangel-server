<?php

function volunteersCommon_checkRequestTextData($data) {
	if (
	  !isset($data['is_published']) ||
	  !isset($data['fio']) || !$data['fio'] ||
	  !isset($data['description']) || !$data['description'] ||
	  !isset($data['short_description']) || !$data['short_description']
	) {
		functions_errorOutput('Некорректный запрос. Не все данные переданы. '.json_encode($data), 400);
	}
}
?>
