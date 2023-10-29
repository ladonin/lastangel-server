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

$_stmt = $db_mysqli->prepare("SELECT * FROM donators order by id DESC LIMIT 5000");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = '';

function prepareText($text) {
	return str_replace(';',',',$text);
}

if ($_type === 'html') {
	$_data.="<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while($_row = $_result->fetch_array()){
	if ($_type === 'txt') {
		$_data.="#id: ".$_row['id']."\n";
		$_data.="#Имя: ".$_row['firstname']."\n";
		$_data.="#Отчество: ".$_row['middlename']."\n";
		$_data.="#Фамилия: ".$_row['lastname']."\n";
		$_data.="#Карточка/телефон: ".$_row['card']."\n";
		$_data.="#Ссылка на личную страницу: ".$_row['link_to_page']."\n";
		$_data.="#Полное ФИО: ".$_row['fullname']."\n";
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
		$_data.="<b>Ссылка на личную страницу</b>: ".$_row['link_to_page']."<br/>";
		$_data.="<b>Полное ФИО</b>: ".$_row['fullname']."<br/>";
		$_data.="<b>Дата создания</b>: ".date('d.m.Y H:i:s',$_row['created'])."<br/>";
		$_data.="<b>Дата последнего обновления</b>: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."<br/>";
		$_data.="<br/>";
		$_data.="<hr/>";
		$_data.="<br/>";
    }
}

if ($_type === 'html') {
	$_data.="</body></html>";
}
header('Content-Description: attachment');
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Expose-Headers: content-disposition,content-length,content-type");
header('Content-Disposition: attachment; filename=donators.' . $_type);
header('Content-Length: ' . strlen($_data));
echo($_data);
?>