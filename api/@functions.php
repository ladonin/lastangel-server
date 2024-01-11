<?php

function functions_errorOutput($text, $code = null)
{
    global $db_mysqli;
    if ($code) {
        http_response_code($code);
    }
    echo $text;
    $db_mysqli->close();
    exit();
}

function functions_successOutput($data)
{
    global $db_mysqli;
    http_response_code(200);
    echo json_encode($data);
    $db_mysqli->close();
    exit();
}

function functions_totalRemoveFileOrDir($path)
{
    if (is_file($path)) {
        return unlink($path);
    }
    if (is_dir($path)) {
        foreach (scandir($path) as $p) {
            if ($p != "." && $p != "..") {
                functions_totalRemoveFileOrDir(
                    $path . DIRECTORY_SEPARATOR . $p
                );
            }
        }
        return rmdir($path);
    }
    return false;
}
?>
