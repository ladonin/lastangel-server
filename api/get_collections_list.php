<?php
require('@imports.php');

//$json = file_get_contents('php://input');

//$data = array();

//if ($json) {
//  $data = get_object_vars(json_decode($json));
//}

// 5 дней закрытые сборы отображаются
$_closeStatusTimeCondition = time() - 3600*24*5;





$_with_corrupted = 0;
if (isset($_GET['with_corrupted']) && $_GET['with_corrupted']) {
	$_with_corrupted = 1;
}


$_sql = "SELECT collections.*, donations.sum as collected, animals.id as anim_id_real,



CASE
    WHEN animals.id IS NULL AND (collections.type = 1 OR collections.type = 2)
        THEN 1
    ELSE 0
END AS is_corrupted





FROM `collections` 
LEFT JOIN (SELECT SUM(sum) as sum, target_id, type FROM `donations` 
	WHERE type=2  GROUP BY target_id) donations 
ON donations.target_id = collections.id


LEFT JOIN animals 
ON collections.animal_id = animals.id AND (collections.type=1 OR collections.type=2)



 WHERE NOT (".($_with_corrupted ? "0" : "1")." AND animals.id IS NULL AND (collections.type = 1 OR collections.type = 2)) ";

$_params = array();
$_params_types = "";

if (isset($_GET['name']) && $_GET['name']) {
	$_sql.="AND collections.name = ? ";
	$_params[] = $_GET['name'];
	$_params_types.='s';
}
if (isset($_GET['type'])) {
	$_sql.="AND collections.type = ? ";
	$_params[] = $_GET['type'];
	$_params_types.='i';
}

/////////////
if (isset($_GET['status'])) {
	if (isset($_GET['withClosedCollections'])) {
		$_sql.="AND (collections.status = ? OR (collections.status = 3 AND collections.updated > $_closeStatusTimeCondition)) ";
		$_params[] = $_GET['status'];
		$_params_types.='i';	
	} else {
		$_sql.="AND collections.status = ? ";
		$_params[] = $_GET['status'];
		$_params_types.='i';	
	}
} else if (isset($_GET['withClosedCollections'])) {
		$_sql.="AND (collections.status = 1 OR collections.status = 2 OR (collections.status = 3 AND collections.updated > $_closeStatusTimeCondition)) ";	
}
/////////////

if (isset($_GET['statusExclude'])) {
	$_sql.="AND collections.status != ? ";
	$_params[] = $_GET['statusExclude'];
	$_params_types.='i';
}
if (isset($_GET['animal_id'])) {
	$_sql.="AND collections.animal_id = ? ";
	$_params[] = $_GET['animal_id'];
	$_params_types.='i';
}

if (isset($_GET['ismajor'])) {
	$_sql.="AND collections.ismajor  = ? ";
	$_params[] = $_GET['ismajor'];
	$_params_types.='i';
}



if (isset($_GET['orderComplex']) && $_GET['orderComplex']) {
	$_orderComplex = $_GET['orderComplex'];
	if (
		$_orderComplex === 'ismajor desc, status asc'
	) {
		$_sql.="ORDER BY " . $_orderComplex . " ";
	}
} else if (isset($_GET['order']) && isset($_GET['order_type']) && $_GET['order'] && $_GET['order_type']) {

	$_order = $_GET['order'];
	$_type = $_GET['order_type'];
	if (($_order === 'id'
	 || $_order === 'type'
	 || $_order === 'status'
	 || $_order === 'animal_id'
	 || $_order === 'ismajor'
	 || $_order === 'target_sum'
	 || $_order === 'collected'
	 || $_order === 'created'
	 || $_order === 'updated')
		&& (strtolower($_type) === 'asc' 
		|| strtolower($_type) === 'desc'))	 {
	$_sql.="ORDER BY collections." . $_order . " " . $_type . " ";
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