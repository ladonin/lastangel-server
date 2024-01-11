<?php
require "@imports.php";

auth_verify([$ADMIN_ROLE]);

if (!isset($_GET["type"]) || !$_GET["type"]) {
    functions_errorOutput("Некорректный запрос. Не передан type.", 400);
}

if (
    $_GET["type"] !== "csv" &&
    $_GET["type"] !== "txt" &&
    $_GET["type"] !== "html"
) {
    functions_errorOutput(
        "Некорректный запрос. Некорректно передан type: " . $_GET["type"],
        400
    );
}

$_type = $_GET["type"];

$_stmt = $db_mysqli->prepare("SELECT * FROM collections order by id DESC");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = "";

function convertType($val)
{
    if ($val === 1) {
        return "нужна медпомощь";
    }
    if ($val === 2) {
        return "покупка вещи для питомца";
    }
    if ($val === 3) {
        return "постройка";
    }
    if ($val === 4) {
        return "общие нужды";
    }
}
function convertStatus($val)
{
    if ($val === 1) {
        return "опубликован";
    }
    if ($val === 2) {
        return "не опубликован";
    }
    if ($val === 3) {
        return "закрыт";
    }
}

function prepareText($text)
{
    $_text = str_replace(";", ",", $text);
    $_text = str_replace(["\r\n", "\n", "\r"], " ", $text);
    return $_text;
}
if ($_type === "csv") {
    $_data .=
        "id;Наименование;Тип;Статус;Кому;Краткое описание;Полное описание;Надо собрать;Дата создания;Дата последнего обновления\n";
}
if ($_type === "html") {
    $_data .= "<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while ($_row = $_result->fetch_array()) {
    if ($_type === "txt") {
        $_data .= "#id: " . $_row["id"] . "\n";
        $_data .= "#Наименование: " . $_row["name"] . "\n";
        $_data .= "#Тип: " . convertType($_row["type"]) . "\n";
        $_data .= "#Статус: " . convertStatus($_row["status"]) . "\n";
        $_data .=
            "#Кому: " . ($_row["animal_id"] ? $_row["animal_id"] : "-") . "\n";
        $_data .=
            "#Краткое описание: " . trim($_row["short_description"]) . "\n";
        $_data .= "#Полное описание: " . trim($_row["description"]) . "\n";
        $_data .= "#Надо собрать: " . $_row["target_sum"] . "\n";
        $_data .=
            "#Дата создания: " . date("d.m.Y H:i:s", $_row["created"]) . "\n";
        $_data .=
            "#Дата последнего обновления: " .
            ($_row["updated"] ? date("d.m.Y H:i:s", $_row["updated"]) : "-") .
            "\n";
        $_data .= "\n";
        $_data .= "----------------------------------------------------------";
        $_data .= "\n";
    }
    if ($_type === "html") {
        $_data .= "<b>id</b>: " . $_row["id"] . "<br/>";
        $_data .= "<b>Наименование</b>: " . $_row["name"] . "<br/>";
        $_data .= "<b>Тип</b>: " . convertType($_row["type"]) . "<br/>";
        $_data .= "<b>Статус</b>: " . convertStatus($_row["status"]) . "<br/>";
        $_data .=
            "<b>Кому</b>: " .
            ($_row["animal_id"] ? $_row["animal_id"] : "-") .
            "<br/>";
        $_data .=
            "<b>Краткое описание</b>: " .
            trim($_row["short_description"]) .
            "<br/>";
        $_data .=
            "<b>Полное описание</b>: " . trim($_row["description"]) . "<br/>";
        $_data .= "<b>Надо собрать</b>: " . $_row["target_sum"] . "<br/>";
        $_data .=
            "<b>Дата создания</b>: " .
            date("d.m.Y H:i:s", $_row["created"]) .
            "<br/>";
        $_data .=
            "<b>Дата последнего обновления</b>: " .
            ($_row["updated"] ? date("d.m.Y H:i:s", $_row["updated"]) : "-") .
            "<br/>";
        $_data .= "<br/>";
        $_data .= "<hr/>";
        $_data .= "<br/>";
    }
    if ($_type === "csv") {
        $_data .=
            $_row["id"] .
            ";" .
            prepareText($_row["name"]) .
            ";" .
            convertType($_row["type"]) .
            ";" .
            convertStatus($_row["status"]) .
            ";" .
            ($_row["animal_id"] ? $_row["animal_id"] : "-") .
            ";" .
            trim(prepareText($_row["short_description"])) .
            ";" .
            trim(prepareText($_row["description"])) .
            ";" .
            $_row["target_sum"] .
            ";" .
            date("d.m.Y H:i:s", $_row["created"]) .
            ";" .
            ($_row["updated"] ? date("d.m.Y H:i:s", $_row["updated"]) : "-") .
            "\n";
    }
}

if ($_type === "html") {
    $_data .= "</body></html>";
}
header("Content-Description: attachment");
header("Content-Type: text/html; charset=utf-8");
header(
    "Access-Control-Expose-Headers: content-disposition,content-length,content-type"
);
header("Content-Disposition: attachment; filename=collections." . $_type);
header("Content-Length: " . strlen($_data));
echo $_data;
?>
