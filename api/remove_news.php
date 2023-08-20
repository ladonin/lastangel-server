<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@news_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_recordId = intval($_GET['id']);


if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $id, 400);
}


$_res = $db_mysqli->query("SELECT another_images FROM news WHERE id=$_recordId");
$_row = $_res->fetch_assoc();

$_another_images = json_decode($_row['another_images']);

// Удаляем из базы
$db_mysqli->query("DELETE FROM news WHERE id = '".$_recordId."'");

// Удаляем из хранилища
foreach ($_another_images as $_number) {
	$_filesSizeNames = images_getFileSizeNames('another', $_number, $IMAGES_ANOTHER_SIZES);

	foreach ($_filesSizeNames as $_fileName) {
		outerStorage_removeFile($_fileName, 'news/'.$_recordId);
	}
}

// Директория /$_recordId автоматом удаляется после удаления последнего файла...
// Но на всякий пожарный попытаемся удалить, если автоматического удаления не произошло
outerStorage_removeFolder('news/'.$_recordId);


functions_successOutput($_recordId);
?>