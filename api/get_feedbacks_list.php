<?php
require('@imports.php');
auth_verify([$ADMIN_ROLE]);
//$json = file_get_contents('php://input');

//$data = array();

//if ($json) {
//  $data = get_object_vars(json_decode($json));
//}
$_sql = "SELECT * FROM feedbacks WHERE 1 ";

$_params = array();
$_params_types = "";




if (isset($_GET['fio']) && $_GET['fio']) {
	$_sql.="AND fio LIKE ? ";
	$_params[] = '%'.$_GET['fio'].'%';
	$_params_types.='s';
}


if (isset($_GET['phone']) && $_GET['phone']) {
	$_sql.="AND phone LIKE ? ";
	$_params[] = '%'.$_GET['phone'].'%';
	$_params_types.='s';
}

if (isset($_GET['email']) && $_GET['email']) {
	$_sql.="AND email LIKE ? ";
	$_params[] = '%'.$_GET['email'].'%';
	$_params_types.='s';
}




$_sql.="ORDER by is_new DESC, created DESC ";

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