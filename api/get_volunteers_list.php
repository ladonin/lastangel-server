<?php
require "@imports.php";

$_sql = "SELECT * from volunteers WHERE 1 ";
$_params = [];
$_params_types = "";

if (isset($_GET["id"]) && $_GET["id"]) {
    $_sql .= "AND id = ? ";
    $_params[] = $_GET["id"];
    $_params_types .= "i";
}

if (isset($_GET["fio"]) && $_GET["fio"]) {
    $_sql .= "AND fio = ? ";
    $_params[] = $_GET["fio"];
    $_params_types .= "s";
}

if (isset($_GET["notPublished"]) && $_GET["notPublished"] === "1") {
    $_sql .= "AND is_published = 0 ";
} elseif (
    !isset($_GET["withUnpublished"]) ||
    $_GET["withUnpublished"] === "0"
) {
    $_sql .= "AND is_published = 1 ";
}

if (isset($_GET["orderComplex"]) && $_GET["orderComplex"]) {
    $_orderComplex = $_GET["orderComplex"];
    if ($_orderComplex === "id desc" || $_orderComplex === "id asc") {
        $_sql .= "ORDER BY " . $_orderComplex . " ";
    }
} elseif (
    isset($_GET["order"]) &&
    isset($_GET["order_type"]) &&
    $_GET["order"] &&
    $_GET["order_type"]
) {
    $_order = $_GET["order"];
    $_type = $_GET["order_type"];
    if (
        ($_order === "id" ||
            $_order === "birthdate" ||
            $_order === "fio" ||
            $_order === "created" ||
            $_order === "updated") &&
        (strtolower($_type) === "asc" || strtolower($_type) === "desc")
    ) {
        $_sql .= "ORDER BY " . $_order . " " . $_type . " ";
    }
}

$_limit =
    isset($_GET["limit"]) && $_GET["limit"] ? intval(trim($_GET["limit"])) : 20;
$_limit = $_limit ? $_limit : 20;
$_limit = $_limit > 99 ? 99 : $_limit;
$_sql .= "LIMIT " . $_limit . " ";

$_offset =
    isset($_GET["offset"]) && $_GET["offset"]
        ? intval(trim($_GET["offset"]))
        : 0;
$_offset = $_offset ? $_offset : 0;
$_sql .= "OFFSET " . $_offset;

$_stmt = $db_mysqli->prepare($_sql);
if ($_params_types) {
    $_stmt->bind_param($_params_types, ...$_params);
}

$_stmt->execute();
$_result = $_stmt->get_result();
$_data = $_result->fetch_all(MYSQLI_ASSOC);

functions_successOutput($_data);
?>
