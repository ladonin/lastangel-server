<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@donators_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ
$_json = file_get_contents('php://input');

$_data = array();

if ($_json) {
  $_data = get_object_vars(json_decode($_json));
}

donatorsCommon_checkRequestTextData($_data);

$_recordId = intval($_GET['id']);

if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $_recordId, 400);
}


$_stmt = $db_mysqli->prepare("UPDATE donators
SET 
	firstname=?,
	middlename=?,
	lastname=?,
	card=?,
	link_to_page=?,
	fullname=?,
	updated=?
WHERE id=$_recordId");
$_now = time();
$_link_to_page = isset($_data['link_to_page']) ? trim($_data['link_to_page']) : '';
$_middlename = isset($_data['middlename']) ? $_data['middlename'] : '';
$_firstname = isset($_data['firstname']) ? trim($_data['firstname']) : '';
$_lastname = isset($_data['lastname']) ? trim($_data['lastname']) : '';
$_card = trim($_data['card']);
$_fullname = trim($_data['fullname']);

$_stmt->bind_param("ssssssi", 
	$_firstname,
	$_middlename,
	$_lastname,
	$_card,
	$_link_to_page,
	$_fullname,
	$_now
 );

$_stmt->execute();

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

functions_successOutput($_recordId);
?>