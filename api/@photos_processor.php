<?php
$IMAGES_MIN_WIDTH = 1200;
$IMAGES_MIN_HEIGHT = 800;
$IMAGES_MAX_WIDTH = 20000;
$IMAGES_MAX_HEIGHT = 20000;

$IMAGE_EXTENSION = '.jpeg';
$IMAGES_TEMPFOLDER_PATH = 'tempfiles/';


function images_checkProportions($file, $w, $h, $die = true) {
  list($_width, $_height, $_type, $_attr) = getimagesize($file);
  $_result = $_width/$_height === $w/$h;

  if (!$_result) {
	  if ($die) { 
		functions_errorOutput('некорректные пропорции изображения: ' . $file . ', ' . $_width . 'x' . $_height . ', ' . $w . 'x' . $h, 400);
	  } else {
		 return $_result;
	  }
  } 
}



function images_isPNG($file) {
	list($_width, $_height, $_type) = getimagesize($file);
	return $_type === IMAGETYPE_PNG;
}

function images_isJPEG($file) {
	list($_width, $_height, $_type) = getimagesize($file);
	return $_type === IMAGETYPE_JPEG;
}

function images_isGIF($file) {
	list($_width, $_height, $_type) = getimagesize($file);
	return $_type === IMAGETYPE_GIF;
}

function images_isBMP($file) {
	list($_width, $_height, $_type) = getimagesize($file);
	return $_type === IMAGETYPE_BMP;
}


function images_checkType($file, $die = true) {
  list($_width, $_height, $_type) = getimagesize($file);
  $_result = $_type === IMAGETYPE_PNG || $_type === IMAGETYPE_JPEG || $_type === IMAGETYPE_GIF || $_type === IMAGETYPE_BMP;
  
  if (!$_result) {
	  if ($die) { 
		functions_errorOutput('некорректный формат изображения: ' . $file, 400);
	  } else {
		 return $_result;
	  }
  }
}


function images_checkSizes($file, $die = true) {
  list($_width, $_height, $_type) = getimagesize($file);
  $_result = $_width > $IMAGES_MIN_WIDTH && $_width < $IMAGES_MAX_WIDTH && $_height > $IMAGES_MIN_HEIGHT && $_height < $IMAGES_MAX_HEIGHT;

  if (!$_result) {
	  if ($die) { 
		functions_errorOutput('#1 фото слишком мелкое: ' . $file . ', ' . $_width . 'x' . $_height, 400);
	  } else {
		 return $_result;
	  }
  }
}

function images_calculateRealSizes($file, $width, $height) {

	list($_width, $_height) = getimagesize($file);

	$_newWidth = null;
	$_newHeight = null;


	if ($_width < $width && $_height < $height) {
		// Слишком мелкая
		functions_errorOutput('#2 фото слишком мелкое: ' . $file . ', ' . $_width . 'x' . $_height, 400);
	} else if ($_width >= $width && $_height < $height) {
		// Годная по ширине
		$_newWidth = $width;
		$_newHeight = intval(($_newWidth/$_width)*$_height);
	} else if ($_height >= $height && $_width < $width) {
		// Годная по высоте
		$_newHeight = $height;
		$_newWidth = intval(($_newHeight/$_height)*$_width);
	} else {
		// Годная по обоим габаритам
		
		// Смотрим, что будем уменьшать
		// Цель - чтобы при уменьшении одной стороны, другая сторона не осталась больше допустимого размера
		$_widthProportion = ($_width - $width)/$width;
		$_heightProportion = ($_height - $height)/$height;
		
		if ($_widthProportion > $_heightProportion) {
			$_newWidth = $width;
			$_newHeight = intval(($_newWidth/$_width)*$_height);
		} else {
			$_newHeight = $height;
			$_newWidth = intval(($_newHeight/$_height)*$_width);
		}
	}
	
	if ($_newWidth && $_newHeight) {
		return array(
			'width'=> $_newWidth,
			'height'=> $_newHeight
		);
	}
	
	functions_errorOutput('ошибка расчета итоговых габаритов изображения: ' . $file . ', ' . $_width . 'x' . $_height . ', ' . $width . 'x' . $height, 500);
}



	
	
	
	
function images_convertToJPEG($file) {
	// Преобразуем в jpeg
	$_source = null;
	
	if (images_isPNG($file)) {
		$_source = imagecreatefrompng($file);
	} else if (images_isJPEG($file)) {
		$_source = imagecreatefromjpeg($file);
	} else if (images_isGIF($file)) {
		$_source = imagecreatefromgif($file);
	} else if (images_isBMP($file)) {
		$_source = imagecreatefrombmp($file);
	}
	
	if (!$_source) {
		// functions_errorOutput('некорректный формат изображения: ' . $file, 400);
		return false;
	}
	
	
	imagejpeg($_source, $file, 100);
    imagedestroy($_source);
	
}




function images_createResizedCopy($file, $outputImage, $width, $height) {
	
	list($_srcWidth, $_srcHeight) = getimagesize($file);
	$_sizes = images_calculateRealSizes($file, $width, $height);
	$_thumb = imagecreatetruecolor($_sizes['width'], $_sizes['height']);
	$_source = imagecreatefromjpeg($file);
	imagecopyresized($_thumb, $_source, 0, 0, 0, 0, $_sizes['width'], $_sizes['height'], $_srcWidth, $_srcHeight);
	imagejpeg($_thumb, $outputImage, 100);
	imagedestroy($_source);
}

/*
 * Сохраняем локально изображения в соответствии с переданным массивом их размеров
 * @param string $file - путь до локально загруженного исходника
 * @param $outputImages - список размеров {code => {width, height}}[]
 * @return список путей до файлов, которые создали на основе исходника
 */
function images_localSave($fileName, $folder, $outputImages) {

	$_fileNames = array();

	global $IMAGE_EXTENSION;
	if (images_convertToJPEG($folder.$fileName) === false) {
		// functions_errorOutput('некорректный формат изображения: ' . $$folder.$fileName, 400);
		return false;
	}
	
	$_newFileNamePart = mb_strimwidth($fileName, 0, strrpos($fileName, $IMAGE_EXTENSION));
	
	foreach ($outputImages as $_code => $_sizes) {
		$_newFileName = $_newFileNamePart.'_'.$_code.$IMAGE_EXTENSION;
		$_fileNames[]=$_newFileName;
		images_createResizedCopy($folder.$fileName, $folder.$_newFileName, $_sizes['width'], $_sizes['height']);
	}

	return $_fileNames;
}

?>
