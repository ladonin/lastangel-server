<?php
require "@imports.php";
require "@feedbacks_common.php";

$_json = file_get_contents("php://input");
$_data = [];
if ($_json) {
    $_data = get_object_vars(json_decode($_json));
}

feedbacksCommon_checkRequestTextData($_data);

$_ip = $_SERVER["REMOTE_ADDR"];
$_now = time();

// Защита от спама - не более 3 сообщений в час
$_hour_earlier = $_now - 3600;
$_res = $db_mysqli->query(
    "SELECT COUNT(*) as count FROM feedbacks WHERE ip='$_ip' AND created > $_hour_earlier"
);
$_row = $_res->fetch_assoc();
if ($_row["count"] > 3) {
    functions_errorOutput("Извините. Слишком частая отправка собщений.", 400);
}

$_stmt = $db_mysqli->prepare("INSERT INTO feedbacks (
	fio,
	phone,
	email,
	text,
	ip,
	is_new,
	created
	) VALUES (?, ?, ?, ?, ?, ?, ?)");

$_fio = isset($_data["fio"]) ? strtolower(trim($_data["fio"])) : "";
$_phone = isset($_data["phone"]) ? strtolower(trim($_data["phone"])) : "";
$_email = isset($_data["email"]) ? strtolower(trim($_data["email"])) : "";
$_text = isset($_data["text"]) ? strtolower(trim($_data["text"])) : "";
$_is_new = 1;

$_stmt->bind_param(
    "sssssii",
    $_fio,
    $_phone,
    $_email,
    $_text,
    $_ip,
    $_is_new,
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
