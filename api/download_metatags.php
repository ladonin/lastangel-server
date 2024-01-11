<?php
require "@imports.php";

auth_verify([$ADMIN_ROLE]);

if (!isset($_GET["type"]) || !$_GET["type"]) {
    functions_errorOutput("Некорректный запрос. Не передан type.", 400);
}

if ($_GET["type"] !== "txt") {
    functions_errorOutput(
        "Некорректный запрос. Некорректно передан type: " . $_GET["type"],
        400
    );
}

$_type = $_GET["type"];

$_stmt = $db_mysqli->prepare("SELECT data, updated FROM metatags WHERE id=1");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = "";

while ($_row = $_result->fetch_array()) {
    if ($_type === "txt") {
        $_data .= "#Данные: " . $_row["data"] . "\n";
        $_data .=
            "#Дата последнего обновления: " .
            ($_row["updated"] ? date("d.m.Y H:i:s", $_row["updated"]) : "-") .
            "\n";
        $_data .= "\n";
    }
}

header("Content-Description: attachment");
header("Content-Type: text/html; charset=utf-8");
header(
    "Access-Control-Expose-Headers: content-disposition,content-length,content-type"
);
header("Content-Disposition: attachment; filename=metatags." . $_type);
header("Content-Length: " . strlen($_data));
echo $_data;
?>
