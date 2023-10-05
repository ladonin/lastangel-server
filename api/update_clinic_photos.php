<?php
require('@imports.php');
require('@outer_storage.php');
require('@images_processor.php');

///////////////////// --> ОСНОВНЫЕ ДАННЫЕ

$_json = $_POST['data'];

$_data = array();

if ($_json) {
  $_data = get_object_vars(json_decode($_json));
}


$_stmt = $db_mysqli->prepare("UPDATE clinic_photos
SET 
	updated=? 
WHERE id=1");
$_now = time();


$_stmt->bind_param("i", 
	$_now
 );

$_stmt->execute();

///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

///////////////////// --> ФОТО ФАЙЛЫ
$_tempFolder = $IMAGES_TEMPFOLDER_PATH.'clinicPhotos/';
if (!is_dir($_tempFolder) && !mkdir($_tempFolder, 0700, true)) {
	functions_errorOutput('Не удалось создать директорию:' . $_tempFolder, 500);
}

// Выбираем значение photos до перезаписи
$_res = $db_mysqli->query("SELECT another_images FROM clinic_photos WHERE id=1");
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
			outerStorage_removeFile($_fileName, 'clinicPhotos');
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
			$_anotherImages = array_merge($_anotherImages, images_localSave($_anotherFileName, $_tempFolder, $IMAGES_ANOTHER_SIZES, $_tempFolder));
			// + Исходник

			$_anotherImages[]=$_anotherFileName;
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
		outerStorage_uploadFile($_tempFolder.$_path, 'clinicPhotos');
	}
// <-- Загружаем на внешнее хранилище

// Успех? => удаляем из временного хранилища
functions_totalRemoveFileOrDir($_tempFolder);
///////////////////// <-- ФОТО ФАЙЛЫ



///////////////////// --> ФОТО В БД

// Отлично, удалось загрузить все фото
// Пишем их тогда в базу


$_another_photosJSON= json_encode($_anotherImagesDb);

$db_mysqli->query("UPDATE clinic_photos SET another_images = '". $_another_photosJSON ."' WHERE id = 1");
///////////////////// <-- ФОТО В БД

functions_successOutput(1);
?>