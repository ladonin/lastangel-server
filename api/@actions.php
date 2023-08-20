<?php

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
	http_response_code(200);
	echo('fuck you OPTIONS!');
	exit();
}


?>
