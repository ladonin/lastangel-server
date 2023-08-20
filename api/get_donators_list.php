<?php
require('@imports.php');

//$json = file_get_contents('php://input');

//$data = array();

//if ($json) {
//  $data = get_object_vars(json_decode($json));
//}

$_sql = "SELECT * FROM donators WHERE 1 ";

$_params = array();
$_params_types = "";



if (isset($_GET['fio']) && $_GET['fio']) {
	$_fio = strtolower(trim(preg_replace('/[ ]+/', ' ', $_GET['fio'])));

	$_fioArray = explode(' ', $_fio);
	
	if (count($_fioArray) === 1) {
		$_sql.="AND fullname LIKE ? ";
		$_params[] = '%'.$_fioArray[0].'%';
		$_params_types.='s';
	} else {
		if (isset($_fioArray[0])) {
			$_sql.="AND lastname LIKE ? ";
			$_params[] = $_fioArray[0].'%';
			$_params_types.='s';	
		}
		if (isset($_fioArray[1])) {
			$_sql.="AND firstname LIKE ? ";
			$_params[] = $_fioArray[1].'%';
			$_params_types.='s';	
		}
		if (isset($_fioArray[2])) {
			$_sql.="AND middlename LIKE ? ";
			$_params[] = $_fioArray[2].'%';
			$_params_types.='s';	
		}
	}
}


if (isset($_GET['firstname']) && $_GET['firstname']) {
	$_sql.="AND firstname LIKE ? ";
	$_params[] = $_GET['firstname'].'%';
	$_params_types.='s';
}
if (isset($_GET['middlename'])) {
	$_sql.="AND middlename LIKE ? ";
	$_params[] = $_GET['middlename'].'%';
	$_params_types.='s';
}
if (isset($_GET['lastname'])) {
	$_sql.="AND lastname LIKE ? ";
	$_params[] = $_GET['lastname'].'%';
	$_params_types.='s';
}

if (isset($_GET['card'])) {
	$_sql.="AND card LIKE ? ";
	$_params[] = $_GET['card'].'%';
	$_params_types.='s';
}

if (isset($_GET['link_to_page'])) {
	$_sql.="AND link_to_page LIKE ? ";
	$_params[] = $_GET['link_to_page'].'%';
	$_params_types.='s';
}

if (isset($_GET['fullname'])) {
	$_sql.="AND fullname LIKE ? ";
	$_params[] = $_GET['fullname'].'%';
	$_params_types.='s';
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