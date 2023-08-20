<?php

function feedbacksCommon_checkRequestTextData($data) {
	if (
	  !isset($data['fio']) || !$data['fio'] || 
	  !isset($data['phone']) || !$data['phone'] || 
	  !isset($data['text']) || !$data['text']
	) {
		functions_errorOutput('Некорректный запрос. Не все данные переданы. '.json_encode($data), 400);
	}
}
?>