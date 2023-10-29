<?php
require('@imports.php');
auth_verify([$ADMIN_ROLE]);

if (!isset($_GET['type']) || !$_GET['type']) {
	functions_errorOutput('Некорректный запрос. Не передан type.', 400);
}

if ($_GET['type'] !== 'txt' && $_GET['type'] !== 'html') {
	functions_errorOutput('Некорректный запрос. Некорректно передан type: ' . $_GET['type'], 400);
}


$_type = $_GET['type'];

$_stmt = $db_mysqli->prepare("SELECT * FROM news order by id DESC");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = '';



function convertStatus($val) {
	 if ($val===1) return 'опубликован';
	 if ($val===2) return 'не опубликован';
}

function prepareText($text) {
	$_text = str_replace(';',',',$text);
	$_text = str_replace(array("\r\n", "\n", "\r"),' ',$text);
	return $_text;
}
function prepareTags($text) {return $text;
	$_text = str_replace('<p>','',$text);
	$_text = str_replace('</p>',"",$_text);
	$_text = str_replace('&nbsp;'," ",$_text);
	return $_text;
}
function prepareTags2($text) {return $text;
	$_text = str_replace('<p>','',$text);
	$_text = str_replace('</p>',"<br/>",$_text);
	$_text = str_replace('&nbsp;'," ",$_text);
	return $_text;
}

if ($_type === 'html') {
	$_data.="<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while($_row = $_result->fetch_array()){
	if ($_type === 'txt') {
		$_data.="####id: ".$_row['id']."\n";
		$_data.="#Заголовок: ".$_row['name']."\n";
		$_data.="#Краткое описание: ". $_row['short_description']."\n";
		$_data.="#Описание: ". $_row['description']."\n\n";
		$_data.="#Описание (мобильная версия): ". $_row['mobile_description']."\n\n";
		$_data.="#Статус: ". convertStatus($_row['status'])."\n";
		$_data.="#Дата создания: ".date('d.m.Y H:i:s',$_row['created'])."\n";
		$_data.="#Дата последнего обновления: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."\n";
		$_data.="\n\n\n\n";
    }
	if ($_type === 'html') {
		$_data.="<b>id</b>: ".$_row['id']."<br/>";
		$_data.="<b>Заголовок</b>: ".$_row['name']."<br/>";
		$_data.="<b>Краткое описание</b>: ".$_row['short_description']."<br/>";
		$_data.="<b>Описание</b>: ".$_row['description']."<br/>";
		$_data.="<b>Описание (мобильная версия)</b>: ".$_row['mobile_description']."<br/>";
		$_data.="<b>Статус</b>: ".convertStatus($_row['status'])."<br/>";
		$_data.="<b>Дата создания</b>: ".date('d.m.Y H:i:s',$_row['created'])."<br/>";
		$_data.="<b>Дата последнего обновления</b>: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."<br/>";
		$_data.="<br/><br/><br/>";
    }
}

if ($_type === 'html') {
	$_data.="</body></html>";
}
header('Content-Description: attachment');
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Expose-Headers: content-disposition,content-length,content-type");
header('Content-Disposition: attachment; filename=news.' . $_type);
header('Content-Length: ' . strlen($_data));
echo($_data);
?>