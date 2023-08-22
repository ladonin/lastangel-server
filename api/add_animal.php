<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@videos_processor.php');
require('@animals_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_json = $_POST['data'];

$_data = array();

if ($_json) {
  $_data = get_object_vars(json_decode($_json));
}

animalsCommon_checkRequestTextData($_data);


$_stmt = $db_mysqli->prepare("INSERT INTO animals(
	name,
	breed,
	birthdate, 
	short_description, 
	description, 
	sex,
	grafted,
	sterilized,
	category,
	status,
	is_published,
	ismajor,
	created
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$_now = time();


$_birthdate = isset($_data['birthdate']) ? $_data['birthdate'] : 0;
$_is_published = isset($_data['is_published']) ? $_data['is_published'] : 0;
$_ismajor = isset($_data['ismajor']) ? $_data['ismajor'] : 0;
$_breed = isset($_data['breed']) ? $_data['breed'] : "";

$_stmt->bind_param("ssissiiiiiiii", 
	$_data['name'],  
	$_breed,
	$_birthdate,
	$_data['short_description'], 
	$_data['description'], 
	$_data['sex'],
	$_data['grafted'],
	$_data['sterilized'],
	$_data['category'],
	$_data['status'],
	$_is_published,
	$_ismajor,
	$_now
 );

$_stmt->execute();


$_res = $db_mysqli->query('SELECT LAST_INSERT_ID()');
$_row = $_res->fetch_array();

$_recordId = $_row[0];

if (!$_recordId) {
	functions_errorOutput('Ошибка сохранения данных в базе. '.json_encode($_data), 500);
}

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ







///////////////////// --> ВИДЕО
// Приходит  файл - добавляем
$_videoTempFolder = $VIDEOS_TEMPFOLDER_PATH.'pets/'.$_recordId.'/';
if (!is_dir($_videoTempFolder) && !mkdir($_videoTempFolder, 0700, true)) {
	functions_errorOutput('Не удалось создать директорию:' . $_videoTempFolder, 500);
}

function processVideo($name) {
	global $db_mysqli;
	global $_recordId;
	global $_videoTempFolder;
	if (isset($_FILES[$name]) && $_FILES[$name]) {
		// Если пришел файл, то надо добавить
		$video = $_FILES[$name];
		videos_checkExtension($video, $_videoTempFolder);
		videos_checkSize($video, $_videoTempFolder);
		
		// Ищем старый
		$_res = $db_mysqli->query("SELECT $name FROM animals WHERE id='$_recordId'");
		$_row = $_res->fetch_assoc();
		$_oldVideo = $_row[$name];

		$_videoFileName = $name.videos_getExtension($video, $_videoTempFolder);
		$_pathVideo = $_videoTempFolder.$_videoFileName;

		// Грузим исходник в temp
		if(move_uploaded_file($_FILES[$name]['tmp_name'], $_pathVideo)) {
			// Загружаем на внешнее хранилище
			outerStorage_uploadFile($_pathVideo, 'pets/'.$_recordId);
			$db_mysqli->query("UPDATE animals SET $name = '$_videoFileName' WHERE id = '$_recordId'");
		} else {
			functions_totalRemoveFileOrDir($_videoTempFolder);
			functions_errorOutput('ошибка загрузки видео: ' . $_FILES[$name]['name'] . ' в ' . $_pathVideo, 500);
		}	
	}
}

processVideo('video1');
processVideo('video2');
processVideo('video3');

///////////////////// <-- ВИДЕО











///////////////////// --> ФОТО ФАЙЛЫ
$_tempFolder = $IMAGES_TEMPFOLDER_PATH.'pets/'.$_recordId.'/';
if (!is_dir($_tempFolder) && !mkdir($_tempFolder, 0700, true)) {
	functions_errorOutput('Не удалось создать директорию:' . $_tempFolder, 500);
}


$_mainImages = [];
$_mainImagesDb = [];
$_anotherImages = []; 
$_anotherImagesDb = []; 


// Главное фото -->
if (!isset($_FILES['main_image'])) {
	functions_totalRemoveFileOrDir($_tempFolder);
	functions_errorOutput('не передано главное фото', 400);
}
 
$_mainFileName = "main".$IMAGE_EXTENSION;
$_pathMain = $_tempFolder.$_mainFileName;

// Грузим исходник в temp
if(move_uploaded_file($_FILES['main_image']['tmp_name'],$_pathMain)) {
	images_checkProportions($_pathMain, 1, 1, $_tempFolder);
	$_mainImages = images_localSave($_mainFileName, $_tempFolder, $IMAGES_MAIN_SIZES, $_tempFolder);
	// + Исходник
	$_mainImages[] = $_mainFileName;
	// Размеры и расширение фронт знает. Номер сохранить нужно, потому что может фото отсутствовать.
	$_mainImagesDb[] = 1; 
} else {
	functions_totalRemoveFileOrDir($_tempFolder);
	functions_errorOutput('ошибка загрузки главного фото: ' . $_FILES['main_image']['name'] . ' в ' . $_pathMain, 500);
}
// <-- Главное фото

// Остальные фото -->
if (isset($_FILES['another_images'])) {
	foreach ($_FILES['another_images']['tmp_name'] as $_index => $_path) {

		$_anotherFileName = "another_".($_index+1).$IMAGE_EXTENSION;
		$_pathAnother = $_tempFolder.$_anotherFileName;

		// Грузим в temp
		if(move_uploaded_file($_path, $_pathAnother)) {
			$_anotherImages = array_merge($_anotherImages, images_localSave($_anotherFileName, $_tempFolder, $IMAGES_ANOTHER_SIZES, $_tempFolder));
			// + Исходник
			$_anotherImages[] = $_anotherFileName;

			// Сохраняем только номер фото (размеры и расширение фронт знает)
			$_anotherImagesDb[] = $_index+1;
		} else {
			functions_totalRemoveFileOrDir($_tempFolder);
			functions_errorOutput('ошибка дополнительного фото: ' . $_path . ' в ' . $_pathAnother, 500);
		}
	}
}

// <-- Остальные фото


// --> Загружаем на внешнее хранилище
	foreach ($_mainImages as $_path) {
		outerStorage_uploadFile($_tempFolder.$_path, 'pets/'.$_recordId);
	}

	foreach ($_anotherImages as $_path) {
		outerStorage_uploadFile($_tempFolder.$_path, 'pets/'.$_recordId);
	}
// <-- Загружаем на внешнее хранилище

// Успех? => удаляем из временного хранилища
functions_totalRemoveFileOrDir($_tempFolder);



///////////////////// <-- ФОТО ФАЙЛЫ



///////////////////// --> ФОТО В БД

// Отлично, удалось загрузить все фото
// Пишем их тогда в базу

$_another_photosJSON= json_encode($_anotherImagesDb);

$db_mysqli->query("UPDATE animals SET another_images = '". $_another_photosJSON ."', main_image='".(count($_mainImagesDb) ? 1 : 0)."' WHERE id = '".$_recordId."'");

///////////////////// <-- ФОТО В БД

functions_successOutput($_recordId);
?>