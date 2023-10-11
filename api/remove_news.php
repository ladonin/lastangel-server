<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@news_common.php');
require('@seo.php');
auth_verify([$ADMIN_ROLE]);
createSitemap('news');
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_recordId = intval($_GET['id']);


if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $id, 400);
}


$_res = $db_mysqli->query("SELECT another_images, video1, video2, video3 FROM news WHERE id=$_recordId");
$_row = $_res->fetch_assoc();

$_another_images = json_decode($_row['another_images']);

// Удаляем из базы
$db_mysqli->query("DELETE FROM news WHERE id = '".$_recordId."'");

// Удаляем из хранилища
if ($_row['video1']) {	
	outerStorage_removeFile($_row['video1'], 'news/'.$_recordId);
}
if ($_row['video2']) {	
	outerStorage_removeFile($_row['video2'], 'news/'.$_recordId);
}
if ($_row['video3']) {	
	outerStorage_removeFile($_row['video3'], 'news/'.$_recordId);
}
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