<?php


recdir("d:/work/dumpfiles/media");



function get_transition($filename) {

	$img = imageCreateFromJpeg($filename); 
	$width = ImageSX($img);
	$height = ImageSY($img);
	$thumb = imagecreatetruecolor(1, 1); 
	imagecopyresampled($thumb, $img, 0, 0, 0, 0, 1, 1, $width, $height);
	$color = imagecolorat($thumb, 0, 0);
	imageDestroy($img);
	imageDestroy($thumb);
	$r = ($color >> 16) & 0xFF;
	$g = ($color >> 8) & 0xFF;
	$b = $color & 0xFF;
//echo(' = ' . $r . '=');
//echo(' = ' . $g . '=');
//echo(' = ' . $b . '=');
	if (($g - 10) > $r && ($g - 10) > $b) {
		
		return 20;
		
	}
	//if ($r > 100 && $g > 100 && $b > 100) return 20;
	return 10;
}


function images_createResizedCopy(
    $file,
    $outputImage
) {
	

    list($_srcWidth, $_srcHeight) = getimagesize($file);

    $_thumb = imagecreatetruecolor($_srcWidth, $_srcHeight);
    $_source = imagecreatefromjpeg($file);
    imagecopyresampled(
        $_thumb,
        $_source,
        0,
        0,
        0,
        0,
        $_srcWidth,
        $_srcHeight,
        $_srcWidth,
        $_srcHeight
    );

	// водяные знаки
	$_waterSrc = imagecreatefrompng('d:\work\server\api\water4.png');
	if ($_srcWidth > $_srcHeight) {
		$_number = ceil($_srcHeight/2);
	} else {
		$_number = ceil($_srcWidth/2);
	}

    $_water = imagecreatetruecolor($_number, $_number);
	$_black = imagecolorallocate($_water, 0, 0, 0);

	// Сделаем фон прозрачным
	imagecolortransparent($_water, $_black);
    imagecopyresampled(
        $_water,
        $_waterSrc,
        0,
        0,
        0,
        0,
        $_number,
        $_number,
        900,
        900
    );

    $_transition = get_transition($file);
	$_water_rows = ceil($_srcHeight / $_number);
	$_water_cols = ceil($_srcWidth / $_number);

	for ($i = 0; $i <= $_water_cols; $i++) {
		for ($j = 0; $j <= $_water_rows; $j++) {
			imagecopymerge($_thumb, $_water, $i * $_number, $j * $_number, 0, 0, $_number, $_number, $_transition);	
		}
	}
	// водяные знаки
	
	
	
	
	
	
	

    imagejpeg($_thumb, $outputImage, 100);
    imagedestroy($_source);
}








function recdir($dir, $tab = '') {
    $d = opendir($dir);
    $space = str_repeat('&nbsp;', 4);

    while ($name = readdir($d)) {
        if ($name == '.' || $name == '..' || str_ends_with($name, '.mp4')) continue;

        $temp = $dir . DIRECTORY_SEPARATOR . $name;

        if (is_dir($temp)) {
            echo $tab .'[<b>'. $name .'</b>]<br />';
            recdir($temp, $tab . $space);
        } else {
            echo $temp.'<br />';
			
			
			
			images_createResizedCopy($temp,$temp);
	
        }
    }

    closedir($d);
}