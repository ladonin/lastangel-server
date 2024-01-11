<?php
require "@imports.php";
require "@outer_storage.php";
require "@images_processor.php";
require "@videos_processor.php";
require "@news_common.php";
require "@seo.php";

auth_verify([$ADMIN_ROLE]);
createSitemap("news");

///////////////////// --> ОСНОВНЫЕ ДАННЫЕ
$_json = $_POST["data"];
$_data = [];
if ($_json) {
    $_data = get_object_vars(json_decode($_json));
}

newsCommon_checkRequestTextData($_data);

$_stmt = $db_mysqli->prepare("INSERT INTO news (
	name,
	short_description, 
	description,
	mobile_description,
	ismajor,
	hide_album,
	use_mobile_description,
	status,
	created
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$_now = time();
$_ismajor = isset($_data["ismajor"]) ? $_data["ismajor"] : 0;
$_hide_album = isset($_data["hide_album"]) ? $_data["hide_album"] : 0;
$_use_mobile_description = isset($_data["use_mobile_description"])
    ? $_data["use_mobile_description"]
    : 0;
$_status = isset($_data["status"]) ? $_data["status"] : 2;
$_mobile_description = isset($_data["mobile_description"])
    ? $_data["mobile_description"]
    : "";
$_stmt->bind_param(
    "ssssiiiii",
    $_data["name"],
    $_data["short_description"],
    $_data["description"],
    $_mobile_description,
    $_ismajor,
    $_hide_album,
    $_use_mobile_description,
    $_status,
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
$_videoTempFolder = $VIDEOS_TEMPFOLDER_PATH . "news/" . $_recordId . "/";
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
            "SELECT $name FROM news WHERE id='$_recordId'"
        );
        $_row = $_res->fetch_assoc();
        $_oldVideo = $_row[$name];

        $_videoFileName =
            $name . videos_getExtension($video, $_videoTempFolder);
        $_pathVideo = $_videoTempFolder . $_videoFileName;

        // Грузим исходник в temp
        if (move_uploaded_file($_FILES[$name]["tmp_name"], $_pathVideo)) {
            // Загружаем на внешнее хранилище
            outerStorage_uploadFile($_pathVideo, "news/" . $_recordId);
            $db_mysqli->query(
                "UPDATE news SET $name = '$_videoFileName' WHERE id = '$_recordId'"
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
$_tempFolder = $IMAGES_TEMPFOLDER_PATH . "news/" . $_recordId . "/";
if (!is_dir($_tempFolder) && !mkdir($_tempFolder, 0700, true)) {
    functions_errorOutput("Не удалось создать директорию:" . $_tempFolder, 500);
}

$_anotherImages = [];
$_anotherImagesDb = [];

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
                    $_tempFolder,
                    false
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
foreach ($_anotherImages as $_path) {
    outerStorage_uploadFile($_tempFolder . $_path, "news/" . $_recordId);
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
    "UPDATE news SET another_images = '" .
        $_another_photosJSON .
        "' WHERE id = '" .
        $_recordId .
        "'"
);
///////////////////// <-- ФОТО В БД

functions_successOutput($_recordId);
?>
