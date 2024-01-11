<?php
require "@imports.php";
require "@outer_storage.php";
require "@images_processor.php";
require "@donators_common.php";

auth_verify([$ADMIN_ROLE]);

$_json = file_get_contents("php://input");
$_data = [];
if ($_json) {
    $_data = get_object_vars(json_decode($_json));
}

donatorsCommon_checkRequestTextData($_data);

$_stmt = $db_mysqli->prepare("INSERT INTO donators (
	firstname,
	middlename,
	lastname,
	card,
	link_to_page,
	fullname,
	created
	) VALUES (?, ?, ?, ?, ?, ?, ?)");
$_now = time();

$_link_to_page = isset($_data["link_to_page"])
    ? trim($_data["link_to_page"])
    : "";
$_middlename = isset($_data["middlename"]) ? trim($_data["middlename"]) : "";
$_firstname = isset($_data["firstname"]) ? trim($_data["firstname"]) : "";
$_lastname = isset($_data["lastname"]) ? trim($_data["lastname"]) : "";
$_card = trim($_data["card"]);
$_fullname = trim($_data["fullname"]);

$_stmt->bind_param(
    "ssssssi",
    $_firstname,
    $_middlename,
    $_lastname,
    $_card,
    $_link_to_page,
    $_fullname,
    $_now
);

$_stmt->execute();

$_res = $db_mysqli->query("SELECT LAST_INSERT_ID()");
$_row = $_res->fetch_array();

$_recordId = $_row[0];

if (!$_recordId) {
    functions_errorOutput(
        "Ошибка сохранения данных в базе. " . json_encode($_data),
        500
    );
}

functions_successOutput($_recordId);
?>
