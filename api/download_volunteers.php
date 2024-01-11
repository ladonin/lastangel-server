<?php
require "@imports.php";

auth_verify([$ADMIN_ROLE]);

if (!isset($_GET["type"]) || !$_GET["type"]) {
    functions_errorOutput("Некорректный запрос. Не передан type.", 400);
}

if ($_GET["type"] !== "txt" && $_GET["type"] !== "html") {
    functions_errorOutput(
        "Некорректный запрос. Некорректно передан type: " . $_GET["type"],
        400
    );
}

$_type = $_GET["type"];

$_stmt = $db_mysqli->prepare("SELECT * FROM volunteers order by id DESC");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = "";

function convertIsPublished($val)
{
    return $val === 1 ? "да" : "нет";
}

function prepareText($text)
{
    $_text = str_replace(";", ",", $text);
    $_text = str_replace(["\r\n", "\n", "\r"], " ", $text);
    return $_text;
}

if ($_type === "html") {
    $_data .= "<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while ($_row = $_result->fetch_array()) {
    if ($_type === "txt") {
        $_data .= "#id: " . $_row["id"] . "\n";
        $_data .= "#ФИО: " . $_row["fio"] . "\n";
        $_data .=
            "#Дата рождения: " .
            ($_row["birthdate"] ? date("d.m.Y", $_row["birthdate"]) : "") .
            "\n";
        $_data .= "#Номер телефона: " . $_row["phone"] . "\n";
        $_data .= "#Адрес в VK: " . $_row["vk_link"] . "\n";
        $_data .= "#Адрес в OK: " . $_row["ok_link"] . "\n";
        $_data .= "#Адрес в INSTAGRAM: " . $_row["inst_link"] . "\n";
        $_data .=
            "#Короткое описание: " . trim($_row["short_description"]) . "\n";
        $_data .= "#Полное описание: " . trim($_row["description"]) . "\n";
        $_data .=
            "#Опубликован: " . convertIsPublished($_row["is_published"]) . "\n";
        $_data .=
            "#Дата создания записи: " .
            date("d.m.Y H:i:s", $_row["created"]) .
            "\n";
        $_data .=
            "#Дата последнего обновления записи: " .
            ($_row["updated"] ? date("d.m.Y H:i:s", $_row["updated"]) : "-") .
            "\n";
        $_data .= "\n";
        $_data .= "----------------------------------------------------------";
        $_data .= "\n";
    }
    if ($_type === "html") {
        $_data .= "<b>id</b>: " . $_row["id"] . "<br/>";
        $_data .= "<b>ФИО</b>: " . $_row["fio"] . "<br/>";
        $_data .=
            "<b>Дата рождения</b>: " .
            ($_row["birthdate"] ? date("d.m.Y", $_row["birthdate"]) : "") .
            "<br/>";
        $_data .= "<b>Номер телефона</b>: " . $_row["phone"] . "<br/>";
        $_data .= "<b>Адрес в VK</b>: " . $_row["vk_link"] . "<br/>";
        $_data .= "<b>Адрес в OK</b>: " . $_row["ok_link"] . "<br/>";
        $_data .= "<b>Адрес в INSTAGRAM</b>: " . $_row["inst_link"] . "<br/>";
        $_data .=
            "<b>Короткое описание</b>: " .
            trim($_row["short_description"]) .
            "<br/>";
        $_data .=
            "<b>Полное описание</b>: " . trim($_row["description"]) . "<br/>";
        $_data .=
            "<b>Опубликован</b>: " .
            convertIsPublished($_row["is_published"]) .
            "<br/>";
        $_data .=
            "<b>Дата создания записи</b>: " .
            date("d.m.Y H:i:s", $_row["created"]) .
            "<br/>";
        $_data .=
            "<b>Дата последнего обновления записи</b>: " .
            ($_row["updated"] ? date("d.m.Y H:i:s", $_row["updated"]) : "-") .
            "<br/>";
        $_data .= "<br/>";
        $_data .= "<hr/>";
        $_data .= "<br/>";
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
header("Content-Disposition: attachment; filename=volunteers." . $_type);
header("Content-Length: " . strlen($_data));
echo $_data;
?>
