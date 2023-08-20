<?php
require('configs/outer_storage.php');

$OUTER_STORAGE_PHOTOS_URL = $CONFIGS_OUTER_STORAGE_URL;

function outerStorage_headersToArray($str)
{
    $_headers = array();
    $_headersTmpArray = explode("\r\n", $str);
    for ($_i = 0 ; $_i < count($_headersTmpArray ); ++$_i)
    {
        if ((strlen($_headersTmpArray[$_i]) > 0) && (strpos($_headersTmpArray[$_i], ":"))) {
			$_headerName = substr($_headersTmpArray[$_i], 0, strpos($_headersTmpArray[$_i], ":"));
			$_headerValue = substr($_headersTmpArray[$_i], strpos($_headersTmpArray[$_i], ":")+1);
			$_headers[$_headerName] = $_headerValue;
        }
    }
    return $_headers;
}

$outerStorage_response = shell_exec("curl -i -XGET https://api.selcdn.ru/auth/v1.0 -H 'X-Auth-User: $CONFIGS_OUTER_STORAGE_X_AUTH_USER' -H 'X-Auth-Key: $CONFIGS_OUTER_STORAGE_X_AUTH_KEY'");
$outerStorage_data = outerStorage_headersToArray($outerStorage_response);
$outerStorage_url = $outerStorage_data['x-storage-url'];
$outerStorage_authToken = $outerStorage_data['x-auth-token'];


function outerStorage_uploadFile($src, $folder) {

	global $outerStorage_url, $outerStorage_authToken, $OUTER_STORAGE_PHOTOS_URL;
	exec("curl -i -XPUT ${outerStorage_url}${OUTER_STORAGE_PHOTOS_URL}${folder}/ -H 'X-Auth-Token: ${outerStorage_authToken}'  -T $src", $_output);
	if (!count($_output) || strpos($_output[0], ' 201') === false) {
		functions_errorOutput('ошибка загрузки файла на внешний сервер: ' . $src, 500);
	}
}



function outerStorage_removeFile($file, $folder) {

	global $outerStorage_url, $outerStorage_authToken, $OUTER_STORAGE_PHOTOS_URL;
	exec("curl -i -XDELETE ${outerStorage_url}${OUTER_STORAGE_PHOTOS_URL}${folder}/${file} -H 'X-Auth-Token: ${outerStorage_authToken}'", $_output);

	if (!count($_output) || strpos($_output[0], ' 204') === false) {
		functions_errorOutput('ошибка удаления файла c внешнего сервера: ' . $folder.'/'.$file, 500);
	}
}

// Может не потребоваться - после удаления последнего файла в директории selectel, она удаляется автоматом...
function outerStorage_removeFolder($folder) {
	global $outerStorage_url, $outerStorage_authToken, $OUTER_STORAGE_PHOTOS_URL;
	exec("curl -i -XDELETE ${outerStorage_url}${OUTER_STORAGE_PHOTOS_URL}${folder} -H 'X-Auth-Token: ${outerStorage_authToken}'", $_output);
	if (!count($_output) || strpos($_output[0], ' 204') === false) {
		// Потому что selectel автоматом удаляет опустевшую папку (бага), когда в ней удалили все файлы.
		// Поэтому, это, всего лишь, попытка удалить папку, если она, вдруг, не удалилась автоматом.
		//functions_errorOutput('ошибка удаления директории с внешнего сервера: ' . $folder, 500);
	}
}
?>