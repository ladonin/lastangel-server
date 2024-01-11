<?php
require "@imports.php";

$_sql = "SELECT * FROM news WHERE 1 ";

$_params = [];
$_params_types = "";

if (isset($_GET["title"]) && $_GET["title"]) {
    $_sql .= "AND name LIKE ? ";
    $_params[] = "%" . $_GET["title"] . "%";
    $_params_types .= "s";
}
if (isset($_GET["excludeId"]) && $_GET["excludeId"]) {
    $_sql .= "AND id != ? ";
    $_params[] = $_GET["excludeId"];
    $_params_types .= "i";
}
if (isset($_GET["ismajor"]) && $_GET["ismajor"]) {
    $_sql .= "AND ismajor = ? ";
    $_params[] = 1;
    $_params_types .= "i";
}
if (isset($_GET["excludeStatus"])) {
    $_sql .= "AND status != ? ";
    $_params[] = $_GET["excludeStatus"];
    $_params_types .= "i";
}

if (isset($_GET["orderComplex"]) && $_GET["orderComplex"]) {
    $_orderComplex = $_GET["orderComplex"];
    if ($_orderComplex === "ismajor desc, id desc") {
        $_sql .= "ORDER BY " . $_orderComplex . " ";
    } elseif ($_orderComplex === "id desc") {
        $_sql .= "ORDER BY " . $_orderComplex . " ";
    } elseif ($_orderComplex === "id asc") {
        $_sql .= "ORDER BY " . $_orderComplex . " ";
    }
} else {
    $_order =
        isset($_GET["order"]) && strtolower($_GET["order"]) === "asc"
            ? "ASC"
            : "DESC";
    $_sql .= "ORDER by id " . $_order . " ";
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
