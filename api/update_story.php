<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@videos_processor.php');
require('@stories_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_json = $_POST['data'];

$_data = array();

if ($_json) {
  $_data = get_object_vars(json_decode($_json));
}

storiesCommon_checkRequestTextData($_data);

$_recordId = intval($_GET['id']);

if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $_recordId, 400);
}


$_stmt = $db_mysqli->prepare("UPDATE stories

SET 
	name=?,
	short_description=?,
	description=?,
	mobile_description=?,
	ismajor=?,
	hide_album=?,
	use_mobile_description=?,
	status=?,
	updated=?
WHERE id=$_recordId");
$_now = time();
$_ismajor = isset($_data['ismajor']) ? $_data['ismajor'] : 0;
$_hide_album = isset($_data['hide_album']) ? $_data['hide_album'] : 0;
$_use_mobile_description = isset($_data['use_mobile_description']) ? $_data['use_mobile_description'] : 0;
$_status = isset($_data['status']) ? $_data['status'] : 1;

$_stmt->bind_param("ssssiiiii", 
	$_data['name'],
	$_data['short_description'], 
	$_data['description'], 
	$_data['mobile_description'], 
	$_ismajor,
	$_hide_album,
	$_use_mobile_description,
	$_status,
	$_now
 );

$_stmt->execute();

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

///////////////////// --> ВИДЕО
// Приходит либо текст - не трогаем, либо файл - заменяем (даже, если названия совпадают), либо пусто - удаляем
$_videoTempFolder = $VIDEOS_TEMPFOLDER_PATH.'stories/'.$_recordId.'/';
if (!is_dir($_videoTempFolder) && !mkdir($_videoTempFolder, 0700, true)) {
	functions_errorOutput('Не удалось создать директорию:' . $_videoTempFolder, 500);
}

function processVideo($name) {
	global $db_mysqli;
	global $_recordId;
	global $_videoTempFolder;
	if (isset($_FILES[$name]) && $_FILES[$name]) {
		// Если пришел файл, то надо добавить/поменять
		$video = $_FILES[$name];
		videos_checkExtension($video, $_videoTempFolder);
		videos_checkSize($video, $_videoTempFolder);
		
		// Ищем старый
		$_res = $db_mysqli->query("SELECT $name FROM stories WHERE id='$_recordId'");
		$_row = $_res->fetch_assoc();
		$_oldVideo = $_row[$name];

		$_videoFileName = $name.videos_getExtension($video, $_videoTempFolder);
		$_pathVideo = $_videoTempFolder.$_videoFileName;

		// Грузим исходник в temp
		if(move_uploaded_file($_FILES[$name]['tmp_name'], $_pathVideo)) {
			// Загружаем на внешнее хранилище
			$_oldVideo && outerStorage_removeFile($_oldVideo, 'stories/'.$_recordId);
			outerStorage_uploadFile($_pathVideo, 'stories/'.$_recordId);
			$db_mysqli->query("UPDATE stories SET $name = '$_videoFileName' WHERE id = '$_recordId'");
		} else {
			functions_totalRemoveFileOrDir($_videoTempFolder);
			functions_errorOutput('ошибка загрузки видео: ' . $_FILES[$name]['name'] . ' в ' . $_pathVideo, 500);
		}	
	} else if (isset($_POST[$name]) && $_POST[$name] === '') {
		// Возможно, надо удалить видео из хранилища и базы, т.к. пришел пустой результат в ответе от клиента
		// Ищем старый
		$_res = $db_mysqli->query("SELECT $name FROM stories WHERE id='$_recordId'");
		$_row = $_res->fetch_assoc();
		$_oldVideo = $_row[$name];
		if ($_oldVideo) {
			// Да, видео есть и, следовательно, его надо удалить
			outerStorage_removeFile($_oldVideo, 'stories/'.$_recordId);
			$db_mysqli->query("UPDATE stories SET $name = '' WHERE id = '$_recordId'");
		}
	}
	// Если придет $_POST[$name] с текстом, то, значит, старое видео не тронуто
	
}

processVideo('video1');
processVideo('video2');
processVideo('video3');

///////////////////// <-- ВИДЕО

///////////////////// --> ФОТО ФАЙЛЫ
$_tempFolder = $IMAGES_TEMPFOLDER_PATH.'stories/'.$_recordId.'/';
if (!is_dir($_tempFolder) && !mkdir($_tempFolder, 0700, true)) {
	functions_errorOutput('Не удалось создать директорию:' . $_tempFolder, 500);
}

// Выбираем значение photos до перезаписи
$_res = $db_mysqli->query("SELECT another_images FROM stories WHERE id=$_recordId");
$_row = $_res->fetch_assoc();

$_another_images = json_decode($_row['another_images']);

$_anotherImages = []; 
$_anotherImagesDb = []; 

// Остальные фото -->
// Если надо удалить часть предыдущих

if (isset($_data['another_images_for_delete'])) {
	// Подготавливаем новый массив another
	$_anotherImagesDb = array_values(array_diff($_another_images, $_data['another_images_for_delete']));

	// Удаляем из хранилища
	foreach ($_data['another_images_for_delete'] as $_number) {

		$_filesSizeNames = images_getFileSizeNames('another', $_number, $IMAGES_ANOTHER_SIZES);
		
		foreach ($_filesSizeNames as $_fileName) {
			outerStorage_removeFile($_fileName, 'stories/'.$_recordId);
		}
	}

} else {
	$_anotherImagesDb = $_another_images;
}

// Узнаем максимальный number фото
$_maxNumber = 0;

foreach ($_anotherImagesDb as $_number) {
	if ($_maxNumber < $_number) {
		$_maxNumber = $_number;
	}
}

$_newNumber = $_maxNumber + 1;


if (isset($_FILES['another_images'])) {
	foreach ($_FILES['another_images']['tmp_name'] as $_index => $_path) {

		$_anotherFileName = "another_".($_newNumber + $_index).$IMAGE_EXTENSION;
		$_pathAnother = $_tempFolder.$_anotherFileName;

		// Грузим в temp
		if(move_uploaded_file($_path, $_pathAnother)) {
			$_anotherImages = array_merge($_anotherImages, images_localSave($_anotherFileName, $_tempFolder, $IMAGES_ANOTHER_SIZES, $_tempFolder, false));
			// + Исходник
			$_anotherImages[] = $_anotherFileName;

			// Сохраняем только номер фото (размеры и расширение фронт знает)
			$_anotherImagesDb[] = $_newNumber + $_index;
		} else {
			functions_totalRemoveFileOrDir($_tempFolder);
			functions_errorOutput('ошибка дополнительного фото: ' . $_path . ' в ' . $_pathAnother, 500);
		}
	}
}

// <-- Остальные фото


// --> Загружаем на внешнее хранилище
	foreach ($_anotherImages as $_path) {
		outerStorage_uploadFile($_tempFolder.$_path, 'stories/'.$_recordId);
	}
// <-- Загружаем на внешнее хранилище

// Успех? => удаляем из временного хранилища
functions_totalRemoveFileOrDir($_tempFolder);
///////////////////// <-- ФОТО ФАЙЛЫ



///////////////////// --> ФОТО В БД

// Отлично, удалось загрузить все фото
// Пишем их тогда в базу


$_another_photosJSON= json_encode($_anotherImagesDb);

$db_mysqli->query("UPDATE stories SET another_images = '". $_another_photosJSON ."' WHERE id = '".$_recordId."'");
///////////////////// <-- ФОТО В БД

functions_successOutput($_recordId);
?>