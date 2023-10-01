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

$_stmt = $db_mysqli->prepare("SELECT * FROM donations order by id DESC LIMIT 5000");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = '';



function convertType($val) {
	 if ($val===1) return 'содержание животного';
	 if ($val===2) return 'на сбор';
	 if ($val===3) return 'общие нужды';
}

function prepareText($text) {
	return str_replace(';',',',$text);
}
if ($_type === 'csv') {
	$_data.="id;Имя;Отчество;Фамилия;Карточка/телефон;Сумма;Тип доната;Для кого/чего;Дата создания;Дата последнего обновления\n";
}
if ($_type === 'html') {
	$_data.="<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while($_row = $_result->fetch_array()){
	if ($_type === 'txt') {
		$_data.="#id: ".$_row['id']."\n";
		$_data.="#Имя: ".$_row['donator_firstname']."\n";
		$_data.="#Отчество: ".$_row['donator_middlename']."\n";
		$_data.="#Фамилия: ".$_row['donator_lastname']."\n";
		$_data.="#Карточка/телефон: ". $_row['donator_card']."\n";
		$_data.="#Сумма: ". $_row['sum']."\n";
		$_data.="#Тип доната: ". convertType($_row['type'])."\n";
		$_data.="#Для кого/чего: ". ($_row['target_id'] ? $_row['target_id'] : '-')."\n";
		$_data.="#Дата создания: ".date('d.m.Y H:i:s',$_row['created'])."\n";
		$_data.="#Дата последнего обновления: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."\n";
		$_data.="\n";
    }
	if ($_type === 'html') {
		$_data.="<b>id</b>: ".$_row['id']."<br/>";
		$_data.="<b>Имя</b>: ".$_row['donator_firstname']."<br/>";
		$_data.="<b>Отчество</b>: ".$_row['donator_middlename']."<br/>";
		$_data.="<b>Фамилия</b>: ".$_row['donator_lastname']."<br/>";
		$_data.="<b>Карточка/телефон</b>: ". $_row['donator_card']."<br/>";
		$_data.="<b>Сумма</b>: ".$_row['sum']."<br/>";
		$_data.="<b>Тип доната</b>: ".convertType($_row['type'])."<br/>";
		$_data.="<b>Для кого/чего</b>: ".($_row['target_id'] ? $_row['target_id'] : '-')."<br/>";
		$_data.="<b>Дата создания</b>: ".date('d.m.Y H:i:s',$_row['created'])."<br/>";
		$_data.="<b>Дата последнего обновления</b>: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."<br/>";
		$_data.="<br/>";
    }
	if ($_type === 'csv') {
		$_data.=$_row['id']
		.";".prepareText($_row['donator_firstname'])
		.";".prepareText($_row['donator_middlename'])
		.";".prepareText($_row['donator_lastname'])
		.";".prepareText($_row['donator_card'])
		.";".prepareText($_row['sum'])
		.";".convertType($_row['type'])
		.";".($_row['target_id'] ? $_row['target_id'] : '-')
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
header('Content-Disposition: attachment; filename=donations.' . $_type);
header('Content-Length: ' . strlen($_data));
echo($_data);
?>