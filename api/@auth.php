<?php
require('lib/jwt/JWT.php');
require('lib/jwt/Key.php');
require('configs/auth.php');

$AUTH_SECRET_KEY=$CONFIGS_AUTH_SECRET_KEY;
$ADMIN_ROLE = 1;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;



function auth_createToken($data) {
	global $AUTH_SECRET_KEY;
	$payload = [
		'i'=>$data['id'],
		'r'=>$data['role'],
		'iat'=> time(),
	];
	return JWT::encode($payload, $AUTH_SECRET_KEY, 'HS256');
}


/*
 * Проверка пользователя
 * Проверяется токен и сравнивается полученная роль с допустимой
 */
function auth_verify($roles) {
	global $AUTH_SECRET_KEY;	
	$_headers = apache_request_headers();

	$_token = $_headers['authorization'];

	try {
		$_decoded = JWT::decode($_token, new Key($AUTH_SECRET_KEY, 'HS256'));
	} catch (Exception $e) {
		// echo($e->getMessage());
		functions_errorOutput('Ошибка авторизации.', 403);
	}
	if (in_array($_decoded->r, $roles)) return true;
	functions_errorOutput('Данному пользователю доступ запрещен.', 403);
}

/*
 * Определение роли
 */
function auth_get_role() {
	global $AUTH_SECRET_KEY;	
	$_headers = apache_request_headers();
	if (!isset($_headers['authorization'])) return '';
	$_token = $_headers['authorization'];

	try {
		$_decoded = JWT::decode($_token, new Key($AUTH_SECRET_KEY, 'HS256'));
	} catch (Exception $e) {
		// echo($e->getMessage());
		functions_errorOutput('Ошибка авторизации.', 403);
	}
	return $_decoded->r;
}
?>