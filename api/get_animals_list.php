<?php
require('@imports.php');

//$json = file_get_contents('php://input');

//$data = array();

//if ($json) {
//  $data = get_object_vars(json_decode($json));
//}
$_limitTimeCondition = time() - 3600*24*30;
$_sql = "SELECT 
animals.id,
animals.name,
animals.breed,
animals.birthdate,
animals.short_description,
animals.sex,
animals.grafted,
animals.sterilized,
animals.kind,
animals.status,
animals.is_published,
animals.ismajor,
animals.main_image,
animals.created,
animals.updated,

donations.sum as collected, collections.need_medicine as need_medicine FROM `animals` 

LEFT JOIN (SELECT SUM(sum) as sum, target_id, created FROM `donations` WHERE type= 1 AND created >= '".$_limitTimeCondition."' GROUP BY target_id) donations ON donations.target_id = animals.id 

LEFT JOIN (SELECT count(*) as need_medicine, animal_id FROM `collections` WHERE type= 1 AND status=1 GROUP BY animal_id) collections ON collections.animal_id = animals.id 

WHERE 1 ";

$_params = array();
$_params_types = "";

if (isset($_GET['id']) && $_GET['id']) {
	$_sql.="AND id = ? ";
	$_params[] = $_GET['id'];
	$_params_types.='i';
}

if (isset($_GET['name']) && $_GET['name']) {
	$_sql.="AND animals.name = ? ";
	$_params[] = $_GET['name'];
	$_params_types.='s';
}
if (isset($_GET['grafted'])) {
	$_sql.="AND animals.grafted = ? ";
	$_params[] = $_GET['grafted'];
	$_params_types.='i';
}
if (isset($_GET['sterilized'])) {
	$_sql.="AND animals.sterilized  = ? ";
	$_params[] = $_GET['sterilized'];
	$_params_types.='i';
}

if (isset($_GET['kind'])) {
	$_sql.="AND (";
	foreach ($_GET['kind'] as $key => $value) {
		if ($key > 0) {
			$_sql.=" OR ";
		}
		$_sql.="(animals.kind = ? ";
		$_params[] = $_GET['kind'][$key];
		$_params_types.='i';

		if (isset($_GET['minbirthdate']) && $_GET['minbirthdate'][$key]) {
			$_sql.="AND animals.birthdate >= ? ";
			$_params[] = $_GET['minbirthdate'][$key];
			$_params_types.='i';
		}

		if (isset($_GET['maxbirthdate']) && $_GET['maxbirthdate'][$key]) {
			$_sql.="AND animals.birthdate <= ? ";
			$_params[] = $_GET['maxbirthdate'][$key];
			$_params_types.='i';
		}
		$_sql.=") ";
	}
	$_sql.=") ";
}

if (isset($_GET['notPublished']) && $_GET['notPublished'] === '1') {
	$_sql.="AND animals.is_published = 0 ";
} else if (!isset($_GET['withUnpublished']) || $_GET['withUnpublished'] === '0') {
	$_sql.="AND animals.is_published = 1 ";
}



if (isset($_GET['status'])) {
	if ($_GET['status'] === '1') {
		$_sql.="AND animals.status  = ? AND need_medicine IS NULL ";
		$_params[] = $_GET['status'];
		$_params_types.='i';
	} else	if ($_GET['status'] === '2') {
		$_sql.="AND need_medicine IS NOT NULL ";	
	}
	else {
		$_sql.="AND animals.status  = ? ";
		$_params[] = $_GET['status'];
		$_params_types.='i';
	}
}

if (isset($_GET['statusExclude'])) {
	foreach ($_GET['statusExclude'] as $_index => $_status) {
		$_sql.="AND animals.status != ? ";
		$_params[] = $_status;
		$_params_types.='i';
	}
}

if (isset($_GET['ismajor'])) {
	$_sql.="AND animals.ismajor  = ? ";
	$_params[] = $_GET['ismajor'];
	$_params_types.='i';
}

if (isset($_GET['order']) && isset($_GET['order_type']) && $_GET['order'] && $_GET['order_type']) {

	$_order = $_GET['order'];
	$_type = $_GET['order_type'];
	if (($_order === 'id'
	 || $_order === 'birthdate'
	 || $_order === 'name'
	 || $_order === 'grafted'
	 || $_order === 'sterilized'
	 || $_order === 'kind'
	 || $_order === 'status'
	 || $_order === 'ismajor'
	 || $_order === 'month_collect'
	 || $_order === 'created'
	 || $_order === 'updated')
		&& (strtolower($_type) === 'asc' 
		|| strtolower($_type) === 'desc'))	 {
	$_sql.="ORDER BY " . $_order . " " . $_type . " ";
	}
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