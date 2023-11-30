<?php
require('@imports.php');

//$json = file_get_contents('php://input');

//$data = array();

//if ($json) {
//  $data = get_object_vars(json_decode($json));
//}

$_limitTimeCondition = time() - 3600*24*30;
if (isset($_GET['limit']) && $_GET['limit']) {
	// При установленном лимите нет ограничения по времени
	$_limitTimeCondition = 0;
}

// На сборы нет лимита по времени
if (isset($_GET['type']) && $_GET['type'] === '2') {
	$_limitTimeCondition = 0;
}

$_sql = "
SELECT donations.*, 
	donators.fullname as donator_fullname, 
	donators.id as donator_id,
	donators.link_to_page as donator_outer_link,
CASE 
	WHEN donations.type = '1' THEN animals.name
	WHEN donations.type = '2' THEN collections.name
	ELSE '' 
	END as target_name
FROM (SELECT * FROM donations WHERE created > $_limitTimeCondition) donations
	LEFT JOIN animals ON donations.target_id = animals.id 
	LEFT JOIN collections ON donations.target_id = collections.id 
	LEFT JOIN donators ON 
		LOWER(donators.firstname) = LOWER(donations.donator_firstname) 
		AND LOWER(donators.middlename) = LOWER(donations.donator_middlename) 
		AND LOWER(donators.lastname) = LOWER(donations.donator_lastname) 
		AND LOWER(donators.card) = LOWER(donations.donator_card)
WHERE donations.created > $_limitTimeCondition  ";

$_params = array();
$_params_types = "";

$_isAnonym = false;
if (isset($_GET['fio']) && $_GET['fio']) {
	$_fio = strtolower(trim(preg_replace('/[ ]+/', ' ', $_GET['fio'])));
	if ($_fio === 'аноним') {
			$_sql.="AND donations.donator_firstname = '' ";	
			$_sql.="AND donations.donator_middlename = '' ";	
			$_sql.="AND donations.donator_lastname = '' ";	
			$_sql.="AND donations.donator_card = '' ";
			$_isAnonym = true;
	} else {
		$_fioArray = explode(' ', $_fio);
		if (isset($_fioArray[0])) {
			$_sql.="AND donations.donator_lastname LIKE ? ";
			$_params[] = $_fioArray[0].'%';
			$_params_types.='s';	
		}
		if (isset($_fioArray[1])) {
			$_sql.="AND donations.donator_firstname LIKE ? ";
			$_params[] = $_fioArray[1].'%';
			$_params_types.='s';	
		}
		if (isset($_fioArray[2])) {
			$_sql.="AND donations.donator_middlename LIKE ? ";
			$_params[] = $_fioArray[2].'%';
			$_params_types.='s';	
		}
	}
}

if (isset($_GET['donator_firstname']) && $_GET['donator_firstname']) {
	$_sql.="AND donations.donator_firstname LIKE ? ";
	$_params[] = $_GET['donator_firstname'].'%';
	$_params_types.='s';
}
	
if (isset($_GET['donator_middlename']) && $_GET['donator_middlename']) {
	$_sql.="AND donations.donator_middlename LIKE ? ";
	$_params[] = $_GET['donator_middlename'].'%';
	$_params_types.='s';
}
	
if (isset($_GET['donator_lastname']) && $_GET['donator_lastname']) {
	$_sql.="AND donations.donator_lastname LIKE ? ";
	$_params[] = $_GET['donator_lastname'].'%';
	$_params_types.='s';
}
	
if (!$_isAnonym && isset($_GET['card']) && $_GET['card']) {
	$_sql.="AND donations.donator_card LIKE ? ";
	$_params[] = $_GET['card'].'%';
	$_params_types.='s';
}

if (isset($_GET['sum'])) {
	$_sql.="AND donations.sum LIKE ? ";
	$_params[] = $_GET['sum'].'%';
	$_params_types.='i';
}

if (isset($_GET['target_id'])) {
	$_sql.="AND donations.target_id = ? ";
	$_params[] = $_GET['target_id'];
	$_params_types.='i';
}

if (isset($_GET['type'])) {
	$_sql.="AND donations.type = ? ";
	$_params[] = $_GET['type'];
	$_params_types.='i';
}


if (isset($_GET['order']) && isset($_GET['order_type']) && $_GET['order'] && $_GET['order_type']) {

	$_order = $_GET['order'];
	$_type = $_GET['order_type'];
	if (($_order === 'id'
	 || $_order === 'sum'
	 || $_order === 'target_id'
	 || $_order === 'type'
	 || $_order === 'created'
	 || $_order === 'updated')
		&& (strtolower($_type) === 'asc' 
		|| strtolower($_type) === 'desc'))	 {
		$_sql.="ORDER BY donations." . $_order . " " . $_type . " ";
	}
} else {
	$_sql.="ORDER BY donations.id DESC ";
	
}

$_limit = (isset($_GET['limit']) && $_GET['limit']) ? intval(trim($_GET['limit'])) : 20;
$_limit = $_limit ? $_limit : 20;
$_limit = $_limit > 99 ? 99 : $_limit;
$_sql.="LIMIT " . $_limit . " ";

$_offset = (isset($_GET['offset']) && $_GET['offset']) ? intval(trim($_GET['offset'])) : 0;
$_offset = $_offset ? $_offset : 0;
$_sql.="OFFSET " . $_offset;

$_stmt = $db_mysqli->prepare($_sql);
if ($_params_types) {
	$_stmt->bind_param($_params_types, ...$_params);
}


$_stmt->execute();
$_result = $_stmt->get_result();
$_data = $_result->fetch_all( MYSQLI_ASSOC );

functions_successOutput($_data);
?>