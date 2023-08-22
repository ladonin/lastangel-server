<?php
$VIDEOS_TEMPFOLDER_PATH = 'tempfiles/';
$VIDEOS_MAX_SIZE = 200000000;

function videos_checkExtension($file, $_tempFolder) {

	if ($file['type'] === "video/mp4" || $file['type'] === "video/webm" || $file['type'] === "video/ogg") { 
		return true;
	} else {
		functions_totalRemoveFileOrDir($_tempFolder);
		functions_errorOutput('некорректный формат видео: ' . $file['type'] . ', ' . $file['name'], 400);
	}
}


function videos_getExtension($file, $_tempFolder) {

	if ($file['type'] === "video/mp4") {
		return '.mp4';
	}
	else if ($file['type'] === "video/webm") {
		return '.webm';
	}
	else if ($file['type'] === "video/ogg") {
		return '.ogg';
	}
    else {
		functions_totalRemoveFileOrDir($_tempFolder);
		functions_errorOutput('некорректный формат видео: ' . $file['type'] . ', ' . $file['name'], 400);
	}
}




function videos_checkSize($file, $_tempFolder) {
	global $VIDEOS_MAX_SIZE;
	if ($file['size'] <= $VIDEOS_MAX_SIZE) { 
		return true;
	} else {
		functions_totalRemoveFileOrDir($_tempFolder);
		functions_errorOutput('некорректный размер видео: ' . $file['size'] . ', ' . $file['name'], 400);
	}	
}

?>