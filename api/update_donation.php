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

$_recordId = intval($_GET['id']);

if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $_recordId, 400);
}


$_stmt = $db_mysqli->prepare("UPDATE donations
SET 
	donator_firstname=?,
	donator_middlename=?,
	donator_lastname=?,
	donator_card=?,
	sum=?,
	target_id=?,
	type=?,
	updated=?
WHERE id=$_recordId");
$_now = time();
$_target_id = isset($_data['target_id']) ? $_data['target_id'] : 0;
$_donator_firstname = isset($_data['donator_firstname']) ? strtolower(trim($_data['donator_firstname'])) : "";
$_donator_middlename = isset($_data['donator_middlename']) ? strtolower(trim($_data['donator_middlename'])) : "";
$_donator_lastname = isset($_data['donator_lastname']) ? strtolower(trim($_data['donator_lastname'])) : "";
$_donator_card = isset($_data['donator_card']) ? strtolower(trim($_data['donator_card'])) : "";

$_sum = trim($_data['sum']);


$_stmt->bind_param("ssssiiii", 
	$_donator_firstname,
	$_donator_middlename,
	$_donator_lastname,
	$_donator_card,
	$_sum,
	$_target_id,
	$_data['type'],
	$_now
 );

$_stmt->execute();

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

functions_successOutput($_recordId);
?>