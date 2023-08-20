<?php
// Создание пароля
// echo password_hash(12312, PASSWORD_DEFAULT);
require('@imports.php');


$_json = file_get_contents('php://input');
$_data = get_object_vars(json_decode($_json));


$_login = $_data['login'];
$_password = $_data['password'];





$_stmt = $db_mysqli->prepare("SELECT * FROM users WHERE login = ?");
$_stmt->bind_param("s", $_login);
$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_assoc();

if (!$_row || !$_row['password_hash']) {
	functions_successOutput(['status'=>'error', 'data'=>'Пользователь не найден']);
	// errorOutput('Пользователь не найден', $mysqli, 400);
} else {
	$_passwordHash = $_row['password_hash'];
	$isPasswordCorrect = password_verify($_password, $_passwordHash);
	if (!$isPasswordCorrect) {
		functions_successOutput(['status'=>'error', 'data'=>'Пароль не верный']);
		// errorOutput('Пароль не верный', 400);
	} else {
		functions_successOutput(['status'=>'success', 'data'=>auth_createToken($_row)]);
	}
}





$db_mysqli->close();




?>
