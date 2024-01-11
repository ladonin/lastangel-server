<?php
require "@imports.php";
require "@outer_storage.php";
require "@images_processor.php";
require "@videos_processor.php";
require "@volunteers_common.php";
require "@seo.php";

auth_verify([$ADMIN_ROLE]);
createSitemap("volunteers");

///////////////////// --> ОСНОВНЫЕ ДАННЫЕ
$_json = $_POST["data"];
$_data = [];
if ($_json) {
    $_data = get_object_vars(json_decode($_json));
}

volunteersCommon_checkRequestTextData($_data);

$_stmt = $db_mysqli->prepare("INSERT INTO volunteers(
	fio,
	birthdate,
	short_description,
	description,
	is_published,
	vk_link,
	ok_link,
	inst_link,
	phone,
	created
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$_now = time();

//$_birthdate = isset($_data['birthdate']) ? $_data['birthdate'] : 0;
$_is_published = isset($_data["is_published"]) ? $_data["is_published"] : 0;

$_stmt->bind_param(
    "sississssi",
    $_data["fio"],
    $_data["birthdate"],
    $_data["short_description"],
    $_data["description"],
    $_is_published,
    $_data["vk_link"],
    $_data["ok_link"],
    $_data["inst_link"],
    $_data["phone"],
    $_now
);

$_stmt->execute();

$_res = $db_mysqli->query("SELECT LAST_INSERT_ID()");
$_row = $_res->fetch_array();

$_recordId = $_row[0];

if (!$_recordId) {
    functions_errorOutput(
        "Ошибка сохранения данных в базе. " . json_encode($_data),
        500
    );
}
///////////////////// <-- ОСНОВНЫЕ ДАННЫЕ

///////////////////// --> ВИДЕО
// Приходит  файл - добавляем
$_videoTempFolder = $VIDEOS_TEMPFOLDER_PATH . "volunteers/" . $_recordId . "/";
if (!is_dir($_videoTempFolder) && !mkdir($_videoTempFolder, 0700, true)) {
    functions_errorOutput(
        "Не удалось создать директорию:" . $_videoTempFolder,
        500
    );
}

function processVideo($name)
{
    global $db_mysqli;
    global $_recordId;
    global $_videoTempFolder;
    if (isset($_FILES[$name]) && $_FILES[$name]) {
        // Если пришел файл, то надо добавить
        $video = $_FILES[$name];
        videos_checkExtension($video, $_videoTempFolder);
        videos_checkSize($video, $_videoTempFolder);

        // Ищем старый
        $_res = $db_mysqli->query(
            "SELECT $name FROM volunteers WHERE id='$_recordId'"
        );
        $_row = $_res->fetch_assoc();
        $_oldVideo = $_row[$name];

        $_videoFileName =
            $name . videos_getExtension($video, $_videoTempFolder);
        $_pathVideo = $_videoTempFolder . $_videoFileName;

        // Грузим исходник в temp
        if (move_uploaded_file($_FILES[$name]["tmp_name"], $_pathVideo)) {
            // Загружаем на внешнее хранилище
            outerStorage_uploadFile($_pathVideo, "volunteers/" . $_recordId);
            $db_mysqli->query(
                "UPDATE volunteers SET $name = '$_videoFileName' WHERE id = '$_recordId'"
            );
        } else {
            functions_totalRemoveFileOrDir($_videoTempFolder);
            functions_errorOutput(
                "ошибка загрузки видео: " .
                    $_FILES[$name]["name"] .
                    " в " .
                    $_pathVideo,
                500
            );
        }
    }
}

processVideo("video1");
processVideo("video2");
processVideo("video3");
///////////////////// <-- ВИДЕО

///////////////////// --> ФОТО ФАЙЛЫ
$_tempFolder = $IMAGES_TEMPFOLDER_PATH . "volunteers/" . $_recordId . "/";
if (!is_dir($_tempFolder) && !mkdir($_tempFolder, 0700, true)) {
    functions_errorOutput("Не удалось создать директорию:" . $_tempFolder, 500);
}

$_mainImages = [];
$_mainImagesDb = [];
$_anotherImages = [];
$_anotherImagesDb = [];

// Главное фото -->
if (
    !isset($_FILES["main_image_cropped"]) ||
    !isset($_FILES["main_image_original"])
) {
    functions_totalRemoveFileOrDir($_tempFolder);
    functions_errorOutput("не передано главное фото", 400);
}

$_mainCroppedFileName = "main_cropped" . $IMAGE_EXTENSION;
$_pathCroppedMain = $_tempFolder . $_mainCroppedFileName;
$_mainOriginalFileName = "main" . $IMAGE_EXTENSION;
$_pathMainOriginal = $_tempFolder . $_mainOriginalFileName;

// Грузим исходник в temp
if (
    move_uploaded_file(
        $_FILES["main_image_cropped"]["tmp_name"],
        $_pathCroppedMain
    ) &&
    move_uploaded_file(
        $_FILES["main_image_original"]["tmp_name"],
        $_pathMainOriginal
    )
) {
    images_checkProportions($_pathCroppedMain, 1, 1, $_tempFolder);
    $_mainImages = images_localSave(
        $_mainCroppedFileName,
        $_tempFolder,
        $IMAGES_MAIN_SQUARE_SIZES,
        $_tempFolder,
        false,
        "main"
    );

    $_mainImages = array_merge(
        $_mainImages,
        images_localSave(
            $_mainOriginalFileName,
            $_tempFolder,
            $IMAGES_MAIN_SIZES,
            $_tempFolder,
            false,
            "main"
        )
    );
    // + Исходник
    $_mainImages[] = $_mainOriginalFileName;
    // Размеры и расширение фронт знает. Номер сохранить нужно, потому что может фото отсутствовать.
    $_mainImagesDb[] = 1;
} else {
    functions_totalRemoveFileOrDir($_tempFolder);
    functions_errorOutput(
        "ошибка загрузки главного фото: " .
            $_FILES["main_image_cropped"]["name"] .
            " в " .
            $_pathCroppedMain,
        500
    );
}
// <-- Главное фото

// Остальные фото -->
if (isset($_FILES["another_images"])) {
    foreach ($_FILES["another_images"]["tmp_name"] as $_index => $_path) {
        $_anotherFileName = "another_" . ($_index + 1) . $IMAGE_EXTENSION;
        $_pathAnother = $_tempFolder . $_anotherFileName;

        // Грузим в temp
        if (move_uploaded_file($_path, $_pathAnother)) {
            $_anotherImages = array_merge(
                $_anotherImages,
                images_localSave(
                    $_anotherFileName,
                    $_tempFolder,
                    $IMAGES_ANOTHER_SIZES,
                    $_tempFolder
                )
            );
            // + Исходник
            $_anotherImages[] = $_anotherFileName;

            // Сохраняем только номер фото (размеры и расширение фронт знает)
            $_anotherImagesDb[] = $_index + 1;
        } else {
            functions_totalRemoveFileOrDir($_tempFolder);
            functions_errorOutput(
                "ошибка дополнительного фото: " .
                    $_path .
                    " в " .
                    $_pathAnother,
                500
            );
        }
    }
}
// <-- Остальные фото

// --> Загружаем на внешнее хранилище
foreach ($_mainImages as $_path) {
    outerStorage_uploadFile($_tempFolder . $_path, "volunteers/" . $_recordId);
}

foreach ($_anotherImages as $_path) {
    outerStorage_uploadFile($_tempFolder . $_path, "volunteers/" . $_recordId);
}
// <-- Загружаем на внешнее хранилище

// Успех? => удаляем из временного хранилища
functions_totalRemoveFileOrDir($_tempFolder);
///////////////////// <-- ФОТО ФАЙЛЫ

///////////////////// --> ФОТО В БД
// Отлично, удалось загрузить все фото
// Пишем их тогда в базу
$_another_photosJSON = json_encode($_anotherImagesDb);

$db_mysqli->query(
    "UPDATE volunteers SET another_images = '" .
        $_another_photosJSON .
        "', main_image='" .
        (count($_mainImagesDb) ? 1 : 0) .
        "' WHERE id = '" .
        $_recordId .
        "'"
);
///////////////////// <-- ФОТО В БД

functions_successOutput($_recordId);
?>
