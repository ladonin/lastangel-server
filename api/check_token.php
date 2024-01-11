<?php
require "@imports.php";

$_json = file_get_contents("php://input");
$_data = get_object_vars(json_decode($_json));
$_type = $_data["type"];

if ($_type === "admin") {
    $_role = $ADMIN_ROLE;

    try {
        auth_verify([$_role]);
        functions_successOutput(true);
    } catch (Exception $e) {
        functions_successOutput(false);
    }
} else {
    functions_successOutput(false);
}

exit();

$db_mysqli->close();

?>
