<?php

function animalsCommon_checkRequestTextData($data)
{
    if (
        !isset($data["is_published"]) ||
        !isset($data["name"]) ||
        !$data["name"] ||
        !isset($data["birthdate"]) ||
        !$data["birthdate"] ||
        !isset($data["short_description"]) ||
        !$data["short_description"] ||
        !isset($data["description"]) ||
        !$data["description"] ||
        !isset($data["sex"]) ||
        !$data["sex"] ||
        $data["sex"] > 2 ||
        $data["sex"] < 1 ||
        !isset($data["grafted"]) ||
        !$data["grafted"] ||
        $data["grafted"] > 2 ||
        $data["grafted"] < 1 ||
        !isset($data["sterilized"]) ||
        !$data["sterilized"] ||
        $data["sterilized"] > 2 ||
        $data["sterilized"] < 1 ||
        !isset($data["kind"]) ||
        !$data["kind"] ||
        $data["kind"] > 4 ||
        $data["kind"] < 1 ||
        !isset($data["status"]) ||
        !$data["status"] ||
        $data["status"] > 6 ||
        $data["status"] < 1
    ) {
        functions_errorOutput(
            "Некорректный запрос. Не все данные переданы. " .
                json_encode($data),
            400
        );
    }
}
?>
