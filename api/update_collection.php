<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');
require('@collections_common.php');
auth_verify([$ADMIN_ROLE]);
///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_json = $_POST['data'];

$_data = array();

if ($_json) {
  $_data = get_object_vars(json_decode($_json));
}

collectionsCommon_checkRequestTextData($_data);

$_recordId = intval($_GET['id']);

if (!$_recordId) {
	functions_errorOutput('Некорректный запрос. id:' . $_recordId, 400);
}


$_stmt = $db_mysqli->prepare("UPDATE collections

SET 
	name=?,
	type=?,
	status=?,
	animal_id=?,
	short_description=?,
	description=?,
	ismajor=?,
	videoVk1=?,
	videoVk2=?,
	videoVk3=?,
	target_sum=?, 
	updated=?
WHERE id=$_recordId");
$_now = time();
$_animal_id = isset($_data['animal_id']) ? $_data['animal_id'] : 0;
$_ismajor = isset($_data['ismajor']) ? $_data['ismajor'] : 0;
$_videoVk1 = isset($_data['videoVk1']) ? $_data['videoVk1'] : "";
$_videoVk2 = isset($_data['videoVk2']) ? $_data['videoVk2'] : "";
$_videoVk3 = isset($_data['videoVk3']) ? $_data['videoVk3'] : "";

$_stmt->bind_param("siiississsii", 
	$_data['name'],
	$_data['type'],
	$_data['status'],
	$_animal_id,
	$_data['short_description'], 
	$_data['description'], 
	$_ismajor,
	$_videoVk1,
	$_videoVk2,
	$_videoVk3,
	$_data['target_sum'], 
	$_now
 );

$_stmt->execute();

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ




///////////////////// --> ФОТО ФАЙЛЫ
$_tempFolder = $IMAGES_TEMPFOLDER_PATH.'collections/'.$_recordId.'/';
if (!is_dir($_tempFolder) && !mkdir($_tempFolder, 0700, true)) {
	functions_errorOutput('Не удалось создать директорию:' . $_tempFolder, 500);
}



// Выбираем значение photos до перезаписи
$_res = $db_mysqli->query("SELECT main_image, another_images FROM collections WHERE id=$_recordId");
$_row = $_res->fetch_assoc();


$_main_image = $_row['main_image'];
$_another_images = json_decode($_row['another_images']);




$_mainImages = [];
$_mainImagesDb = [1]; // Всегда будет таким - это обновление и главное фото уже есть (его не может не быть)
$_anotherImages = []; 
$_anotherImagesDb = []; 

// Главное фото -->
// Если приложили новый файл - перезаписываем
if (isset($_FILES['main_image'])) {
	$_mainFileName = "main".$IMAGE_EXTENSION;
	$_pathMain = $_tempFolder.$_mainFileName;

	// Грузим исходник в temp
	if(move_uploaded_file($_FILES['main_image']['tmp_name'],$_pathMain)) {
		images_checkProportions($_pathMain, 1, 1);

		$_mainImages = images_localSave($_mainFileName, $_tempFolder, $IMAGES_MAIN_SIZES);
		// + Исходник
		$_mainImages[] = $_mainFileName;
	} else {
		functions_errorOutput('ошибка загрузки главного фото: ' . $_FILES['main_image']['name'] . ' в ' . $_pathMain, 500);
	}
}
// <-- Главное фото



// Остальные фото -->
// Если надо удалить часть предыдущих

if (isset($_data['another_images_for_delete'])) {
	// Подготавливаем новый массив another
	$_anotherImagesDb = array_values(array_diff($_another_images, $_data['another_images_for_delete']));

	// Удаляем из хранилища
	foreach ($_data['another_images_for_delete'] as $_number) {

		$_filesSizeNames = images_getFileSizeNames('another', $_number, $IMAGES_ANOTHER_SIZES);
		
		foreach ($_filesSizeNames as $_fileName) {
			outerStorage_removeFile($_fileName, 'collections/'.$_recordId);
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
			$_anotherImages = array_merge($_anotherImages, images_localSave($_anotherFileName, $_tempFolder, $IMAGES_ANOTHER_SIZES));
			// + Исходник
			$_anotherImages[] = $_anotherFileName;

			// Сохраняем только номер фото (размеры и расширение фронт знает)
			$_anotherImagesDb[] = $_newNumber + $_index;
		} else {
			functions_errorOutput('ошибка дополнительного фото: ' . $_path . ' в ' . $_pathAnother, 500);
		}
	}
}

// <-- Остальные фото


// --> Загружаем на внешнее хранилище
	foreach ($_mainImages as $_path) {
		outerStorage_uploadFile($_tempFolder.$_path, 'collections/'.$_recordId);
	}

	foreach ($_anotherImages as $_path) {
		outerStorage_uploadFile($_tempFolder.$_path, 'collections/'.$_recordId);
	}
// <-- Загружаем на внешнее хранилище

// Успех? => удаляем из временного хранилища
functions_totalRemoveFileOrDir($_tempFolder);
///////////////////// <-- ФОТО ФАЙЛЫ



///////////////////// --> ФОТО В БД

// Отлично, удалось загрузить все фото
// Пишем их тогда в базу


$_another_photosJSON= json_encode($_anotherImagesDb);

$db_mysqli->query("UPDATE collections SET another_images = '". $_another_photosJSON ."', main_image='".(count($_mainImagesDb) ? 1 : 0)."' WHERE id = '".$_recordId."'");
///////////////////// <-- ФОТО В БД

functions_successOutput($_recordId);
?>