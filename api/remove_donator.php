<?php
require "@imports.php";

auth_verify([$ADMIN_ROLE]);

$_recordId = intval($_GET["id"]);

if (!$_recordId) {
    functions_errorOutput("Некорректный запрос. id:" . $id, 400);
}

// Удаляем из базы
$db_mysqli->query("DELETE FROM donators WHERE id = '" . $_recordId . "'");

functions_successOutput($_recordId);
?>
