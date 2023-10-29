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

$_stmt = $db_mysqli->prepare("SELECT * FROM acquaintanceship");
$_stmt->execute();
$_result = $_stmt->get_result();

$_data = '';




if ($_type === 'html') {
	$_data.="<html lang='ru'><head><meta charset='utf-8'/></head><body>";
}
while($_row = $_result->fetch_array()){
	if ($_type === 'txt') {
		$_data.= $_row['description']."\n\n\n\n\n\n";
		$_data.="Мобильная версия\n\n\n";
		$_data.= $_row['mobile_description']."\n\n\n";
		$_data.="#Дата последнего обновления: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-')."\n";
		$_data.="\n";
    }
	if ($_type === 'html') {
		$_data.=$_row['description'];
		$_data.="<br/><br/><br/><br/>Мобильная версия<br/><br/><hr/><br/>";		
		$_data.=$_row['mobile_description'];
		$_data.="<br/><br/><b>Дата последнего обновления</b>: ".($_row['updated'] ? date('d.m.Y H:i:s',$_row['updated']) : '-');

    }
}

if ($_type === 'html') {
	$_data.="</body></html>";
}
header('Content-Description: attachment');
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Expose-Headers: content-disposition,content-length,content-type");
header('Content-Disposition: attachment; filename=o_priyte.' . $_type);
header('Content-Length: ' . strlen($_data));
echo($_data);
?>