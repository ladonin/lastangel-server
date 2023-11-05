<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@donations_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_json = file_get_contents('php://input');
$_data = array();

if ($_json) {
  $_data = get_object_vars(json_decode($_json));
}

donationsCommon_checkRequestTextData($_data);



$_target_print_name = '';
if ($_data['type'] === 1 || $_data['type'] === 2) {
	$_id = isset($_data['target_id']) ? intval($_data['target_id']) : 0;
	$_res = $db_mysqli->query("SELECT name FROM ".($_data['type'] === 1 ? "animals" : "collections")." WHERE id=".$_id);
	$_row = $_res->fetch_assoc();
	$_target_print_name = $_row['name'];
}






$_stmt = $db_mysqli->prepare("INSERT INTO donations (
	donator_firstname,
	donator_middlename,
	donator_lastname,
	donator_card,
	sum, 
	target_id, 
	target_print_name, 
	type,
	created
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$_now = time();
$_target_id = isset($_data['target_id']) ? intval($_data['target_id']) : 0;
$_donator_firstname = isset($_data['donator_firstname']) ? strtolower(trim($_data['donator_firstname'])) : "";
$_donator_middlename = isset($_data['donator_middlename']) ? strtolower(trim($_data['donator_middlename'])) : "";
$_donator_lastname = isset($_data['donator_lastname']) ? strtolower(trim($_data['donator_lastname'])) : "";
$_donator_card = isset($_data['donator_card']) ? strtolower(trim($_data['donator_card'])) : "";
$_sum = trim(str_replace(
    ",",
    ".",
    $_data['sum']
));

$_stmt->bind_param("sssssisii", 
	$_donator_firstname,
	$_donator_middlename,
	$_donator_lastname,
	$_donator_card,
	$_sum,
	$_target_id,
	$_target_print_name,
	$_data['type'],
	$_now
 );

$_stmt->execute();


$_res = $db_mysqli->query('SELECT LAST_INSERT_ID()');
$_row = $_res->fetch_array();

$_recordId = $_row[0];

if (!$_recordId) {
	functions_errorOutput('Ошибка сохранения данных в базе. '.json_encode($_data), 500);
}

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

if ($_data['type'] === 2 && $_target_id) {
	// Если донат на сбор, то, возможно, его надо закрыть
	$_res = $db_mysqli->query("SELECT *, 
		(SELECT SUM(sum) as sum FROM `donations` WHERE type=2 AND target_id = $_target_id) as collected
		FROM collections WHERE id=$_target_id");
		
	$_row = $_res->fetch_assoc();
	if (floatval($_row['collected']) >= floatval($_row['target_sum'] && $_row['status'] !== 3)) {
		$_res = $db_mysqli->query("UPDATE collections SET status=3 WHERE id=$_target_id");
	}
}

functions_successOutput($_recordId);
?>