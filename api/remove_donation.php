<?php
require('@imports.php');
require('@collections_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_recordId = intval($_GET['id']);


if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $id, 400);
}


// -- > Получаем данные удаляемого доната
$_res = $db_mysqli->query("SELECT * FROM donations WHERE id=$_recordId");
$_rowOld = $_res->fetch_assoc();
// <--

// Удаляем из базы
$db_mysqli->query("DELETE FROM donations WHERE id = '".$_recordId."'");

// --> Возможное открытие сбора
if ($_rowOld['type'] === '2') {
	collectionsCommon_updateCollectionStatus($_rowOld['target_id']);
}
// <-- Возможное открытие сбора










functions_successOutput($_recordId);
?>