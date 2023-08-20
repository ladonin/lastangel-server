<?php
require('configs/db.php');

$db_mysqli = new mysqli('localhost', $CONFIGS_DB_DATABASE, $CONFIGS_DB_PASSWORD, $CONFIGS_DB_DATABASE);
$db_mysqli->set_charset("utf8");

if ($db_mysqli->connect_error){
    die("Ошибка: " . $db_mysqli->connect_error);
}
?>