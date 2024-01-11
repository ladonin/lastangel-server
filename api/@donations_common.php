<?php

function donationsCommon_checkRequestTextData($data)
{
    if (
        !isset($data["sum"]) ||
        !$data["sum"] ||
        $data["sum"] < 1 ||
        !isset($data["type"]) ||
        !$data["type"] ||
        $data["type"] > 3 ||
        $data["type"] < 1 ||
        ((!isset($data["target_id"]) ||
            !$data["target_id"] ||
            $data["target_id"] < 1) &&
            ($data["type"] === 1 || $data["type"] === 2))
    ) {
        functions_errorOutput(
            "Некорректный запрос. Не все данные переданы. " .
                json_encode($data),
            400
        );
    }
}
?>
