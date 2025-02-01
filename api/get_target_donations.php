<?php
require('@imports.php');

if (!isset($_GET['target_id']) || !intval($_GET['target_id']) || !isset($_GET['type']) || !intval($_GET['type'])) {
	functions_errorOutput('Некорректный запрос. Не передан id.', 400);
}

$_targetId = intval($_GET['target_id']);
$_type = intval($_GET['type']);
$_limitTimeCondition = $_type == 2 ? 0 : time() - 3600*24*30; //для сборов донаты за все время

$_stmt = $db_mysqli->prepare("
SELECT 
	donations.*, 
	donators.fullname as donator_fullname, 
	donators.id as donator_id,
	donators.link_to_page as donator_outer_link
FROM (SELECT * FROM donations WHERE donations.target_id=$_targetId AND donations.type=$_type AND created > $_limitTimeCondition) donations
LEFT JOIN donators ON 
	LOWER(donators.firstname) = LOWER(donations.donator_firstname) 
	AND LOWER(donators.middlename) = LOWER(donations.donator_middlename) 
	AND LOWER(donators.lastname) = LOWER(donations.donator_lastname) 
	AND LOWER(donators.card) = LOWER(donations.donator_card)
ORDER by donations.id DESC
");

$_stmt->execute();
$_result = $_stmt->get_result();
$_row = $_result->fetch_all( MYSQLI_ASSOC );

functions_successOutput($_row);
?>