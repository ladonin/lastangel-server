<?php
require "@functions.php";
require "@actions.php";
require "@db.php";
require "@auth.php";

try {
    require $_GET["file"];
} catch (Error $e) {
    functions_errorOutput("Ошибка запроса:" . $e->getMessage(), 500);
}
?>
