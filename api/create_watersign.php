<?php
require "@imports.php";
require "@images_processor.php";
auth_verify([$ADMIN_ROLE]);

if (
    isset($_FILES["img"]) && isset($_POST["transparent"])
) {
	images_create_water(
		$_FILES["img"]["tmp_name"],
		$_FILES["img"]["tmp_name"],
		$_POST["transparent"]
	);

	header(
		"Access-Control-Expose-Headers: content-disposition,content-length,content-type"
	);
	header("Content-Description: attachment");
	header("Content-Type: " . $_FILES["img"]["type"]);
	header("Cache-Control: no-store, no-cache"); 

	header("Content-Disposition: attachment; filename=" . $_FILES["img"]["name"]);
	header("Content-Length: " . filesize($_FILES["img"]["tmp_name"]));

    readfile($_FILES["img"]["tmp_name"]);
    exit;
}
?>

