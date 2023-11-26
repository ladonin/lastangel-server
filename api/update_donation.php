<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@donations_common.php');
require('@collections_common.php');
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

$_target_print_name = '';
if ($_data['type'] === 1 || $_data['type'] === 2) {
	$_id = isset($_data['target_id']) ? intval($_data['target_id']) : 0;
	$_res = $db_mysqli->query("SELECT name FROM ".($_data['type'] === 1 ? "animals" : "collections")." WHERE id=".$_id);
	$_row = $_res->fetch_assoc();
	$_target_print_name = $_row['name'];
}


// --> Проверяем - на что был донат изначально - для возможного автозакрывания/открывания сбора (если на сбор)***
$_res = $db_mysqli->query("SELECT * FROM donations WHERE id=$_recordId");
$_row = $_res->fetch_assoc();
$_oldDonationData = $_row;
// <--


$_stmt = $db_mysqli->prepare("UPDATE donations
SET 
	donator_firstname=?,
	donator_middlename=?,
	donator_lastname=?,
	donator_card=?,
	sum=?,
	target_id=?,
	target_print_name=?,
	type=?,
	updated=?
WHERE id=$_recordId");
$_now = time();
$_target_id = isset($_data['target_id']) ? $_data['target_id'] : 0;
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

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

// -- > Возможное закрытие/открытие сбора


// --> Проверяем - чем донат стал фактически - для возможного автозакрывания/открывания сбора (если на сбор)***
$_res = $db_mysqli->query("SELECT * FROM donations WHERE id=$_recordId");
$_row = $_res->fetch_assoc();
$_newDonationData = $_row;
// <--


// ***--> Возможное автозакрывание/открывание сбора
if ($_oldDonationData['type'] === '2' && $_newDonationData['type'] === '2' && $_oldDonationData['target_id'] === $_newDonationData['target_id']) {
	// Если не меняли таргет и он направлен на один и тот же сбор, то обновляем только этот таргет
	// Логика ниже делает тоже самое, но сделает это дважды
	collectionsCommon_updateCollectionStatus($_newDonationData['target_id']);
} else {
	// В этом случае проверяем старый и новый таргет, в случае, если они относятся к сборам
	if ($_oldDonationData['type'] === '2') {
		collectionsCommon_updateCollectionStatus($_oldDonationData['target_id']);
	}
	if ($_newDonationData['type'] === '2') {
		collectionsCommon_updateCollectionStatus($_newDonationData['target_id']);
	}
	
}
// <--

//
// <-- Возможное закрытие/открытие сбора

functions_successOutput($_recordId);
?>