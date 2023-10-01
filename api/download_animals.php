<?php
require('@imports.php');
auth_verify([$ADMIN_ROLE]);

if (!isset($_GET['type']) || !$_GET['type']) {
	functions_errorOutput('Некорректный запрос. Не передан type.', 400);
}

if ($_GET['type'] !== 'csv' && $_GET['type'] !== 'txt' && $_GET['type'] !== 'html') {
	functions_errorOutput('Некорректный запрос. Некорректно передан type: ' . $_GET['type'], 400);
}


$_type = $_GET['type'];

$_stmt = $db_mysqli->prepare("SELECT * FROM animals order by id DESC");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = '';



function convertSex($val) {
	 return $val===1 ? 'мальчик' : 'девочка';
}
function convertGrafted($val) {
	 return $val===1 ? 'да' : 'нет';
}
function convertSterilized($val) {
	 return $val===1 ? 'да' : 'нет';
}

function convertKind($val) {
	 if ($val===1) return 'маленькая собака';
	 if ($val===2) return 'средняя собака';
	 if ($val===3) return 'крупная собака';
	 if ($val===4) return 'кошка';
}

function convertStatus($val) {
	 if ($val===1) return 'здоровый член приюта';
	 if ($val===3) return 'инвалид';
	 if ($val===4) return 'инвалид-спинальник';
	 if ($val===5) return 'обрел дом';
	 if ($val===6) return 'ушел по радуге';
}


function convertIsPublished($val) {
	 return $val===1 ? 'да' : 'нет';
}

function prepareText($text) {
	$_text = str_replace(';',',',$text);
	$_text = str_replace(array("\r\n", "\n", "\r"),' ',$text);
	return $_text;
}
if ($_type === 'csv') {
	$_data.="id;Имя;Порода;Дата рождения;Короткое описание;Полное описание;Пол;Привит/а;Кастрирован/Стерилизована;Вид;Статус;Опубликован;Дата создания записи;Дата последнего обновления записи\n";
}
if ($_type === 'html') {
	$_data.="<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while($_row = $_result->fetch_array()){
	if ($_type === 'txt') {
		$_data.="#id: ".$_row['id']."\n";
		$_data.="#Имя: ".$_row['name']."\n";
		$_data.="#Порода: ".$_row['breed']."\n";
		$_data.="#Дата рождения: ".($_row['birthdate'] ? date('d.m.Y',$_row['birthdate']) : '')."\n";
		$_data.="#Короткое описание: ".trim($_row['short_description'])."\n";
		$_data.="#Полное описание: ".trim($_row['description'])."\n";
		$_data.="#Пол: ".convertSex($_row['sex'])."\n";
		$_data.="#Привит/а: ".convertGrafted($_row['grafted'])."\n";
		$_data.="#Кастрирован/Стерилизована: ".convertSterilized($_row['sterilized'])."\n";
		$_data.="#Вид: ".convertKind($_row['kind'])."\n";
		$_data.="#Статус: ".convertStatus($_row['status'])."\n";
		$_data.="#Опубликован: ".convertIsPublished($_row['is_published'])."\n";
		$_data.="#Дата создания записи: ".date('d.m.Y H:i:s',$_row['created'])."\n";
		$_data.="#Дата последнего обновления записи: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."\n";
		$_data.="\n";
    }
	if ($_type === 'html') {
		$_data.="<b>id</b>: ".$_row['id']."<br/>";
		$_data.="<b>Имя</b>: ".$_row['name']."<br/>";
		$_data.="<b>Порода</b>: ".$_row['breed']."<br/>";
		$_data.="<b>Дата рождения</b>: ".($_row['birthdate'] ? date('d.m.Y',$_row['birthdate']) : '')."<br/>";
		$_data.="<b>Короткое описание</b>: ".trim($_row['short_description'])."<br/>";
		$_data.="<b>Полное описание</b>: ".trim($_row['description'])."<br/>";
		$_data.="<b>Пол</b>: ".convertSex($_row['sex'])."<br/>";
		$_data.="<b>Привит/а</b>: ".convertGrafted($_row['grafted'])."<br/>";
		$_data.="<b>Кастрирован/Стерилизована</b>: ".convertSterilized($_row['sterilized'])."<br/>";
		$_data.="<b>Вид</b>: ".convertKind($_row['kind'])."<br/>";
		$_data.="<b>Статус</b>: ".convertStatus($_row['status'])."<br/>";
		$_data.="<b>Опубликован</b>: ".convertIsPublished($_row['is_published'])."<br/>";
		$_data.="<b>Дата создания записи</b>: ".date('d.m.Y H:i:s',$_row['created'])."<br/>";
		$_data.="<b>Дата последнего обновления записи</b>: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."<br/>";
		$_data.="<br/>";
    }
	if ($_type === 'csv') {
		$_data.=$_row['id']
		.";".prepareText($_row['name'])
		.";".prepareText($_row['breed'])
		.";".($_row['birthdate'] ? date('d.m.Y',$_row['birthdate']) : '')
		.";".trim(prepareText($_row['short_description']))
		.";".trim(prepareText($_row['description']))
		.";".convertSex($_row['sex'])
		.";".convertGrafted($_row['grafted'])
		.";".convertSterilized($_row['sterilized'])
		.";".convertKind($_row['kind'])
		.";".convertStatus($_row['status'])
		.";".convertIsPublished($_row['is_published'])
		.";".date('d.m.Y H:i:s',$_row['created'])
		.";".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')
		."\n";
    }
}

if ($_type === 'html') {
	$_data.="</body></html>";
}
header('Content-Description: attachment');
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Expose-Headers: content-disposition,content-length,content-type");
header('Content-Disposition: attachment; filename=pets.' . $_type);
header('Content-Length: ' . strlen($_data));
echo($_data);
?>