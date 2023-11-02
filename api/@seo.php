<?php
require('@imports.php');
// createSitemap('collections'); - пернесчет сборов
// createSitemap(); - пересчет всего


function prepareDBName($name) {
	if ($name === 'pets') return 'animals';
	return $name;
}


function createSitemapCommonSection() {
	
	$_sitemapText = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<url>
		<loc>https://lastangel.ru</loc>
		<changefreq>daily</changefreq>
		<priority>0.9</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/o_priyte</loc>
		<changefreq>weekly</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/pets</loc>
		<changefreq>daily</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/collections</loc>
		<changefreq>weekly</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/news</loc>
		<changefreq>daily</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/stories</loc>
		<changefreq>daily</changefreq>
		<priority>0.8</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/clinic</loc>
		<changefreq>monthly</changefreq>
		<priority>0.5</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/documents</loc>
		<changefreq>monthly</changefreq>
		<priority>0.5</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/help</loc>
		<changefreq>monthly</changefreq>
		<priority>0.7</priority>
	</url>
	<url>
		<loc>https://lastangel.ru/contacts</loc>
		<changefreq>monthly</changefreq>
		<priority>0.8</priority>
	</url>
</urlset>';
			
			
	$_fp = fopen("../../sitemap-common.xml", "w");
	fwrite($_fp, $_sitemapText);
	fclose($_fp);
}


function prepareSinglePageName($section) {
	if ($section === 'pets') return 'pet';
	if ($section === 'collections') return 'collection';
	if ($section === 'news') return 'news';
	if ($section === 'stories') return 'story';
	
	
}

function createSitemapSection($section) {
		global $db_mysqli;
			$_sitemapText = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

			// --> Последние добавленные
			$_res = $db_mysqli->query('SELECT * from '.prepareDBName($section).' ORDER by id DESC LIMIT 10 OFFSET 0');
			$_pets = [];

			while ($_row = $_res->fetch_assoc()) {
				$_sitemapText .= 
'	<url>
		<loc>https://lastangel.ru/'.prepareSinglePageName($section).'/'.$_row['id'].'</loc>
		<changefreq>monthly</changefreq>
		<priority>0.8</priority>
	</url>
';

			}
			// <-- Последние добавленные
			
			// --> Остальные
			
			
			$_res = $db_mysqli->query('SELECT * from '.prepareDBName($section).' ORDER by id DESC LIMIT 10000 OFFSET 10');
			$_pets = [];

			while ($_row = $_res->fetch_assoc()) {
				$_sitemapText .= 
'	<url>
		<loc>https://lastangel.ru/'.prepareSinglePageName($section).'/'.$_row['id'].'</loc>
		<changefreq>monthly</changefreq>
		<priority>0.7</priority>
	</url>
';

			}
			// <-- Остальные
			$_sitemapText .="</urlset>";
			
			
	$_fp = fopen("../../sitemap-".$section.".xml", "w");
	fwrite($_fp, $_sitemapText);
	fclose($_fp);
	
}







function createSitemap($section = false) {
	global $ADMIN_ROLE, $db_mysqli;
	auth_verify([$ADMIN_ROLE]);
	

	// --> Общий файл карты сайта
	$_res = $db_mysqli->query('SELECT * from sitemaps');
	$_sections = [];

	while ($_row = $_res->fetch_assoc()) {
		$_sections[$_row['section']]=1;//$_row['lastmodified'];
	}

	if ($section !== false) {
		//$_sections[$section] = time();
	}
	
	$_sitemapText = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
	
	
$_sitemapText .= 
'	<sitemap>
		<loc>https://lastangel.ru/sitemap-common.xml</loc>
	</sitemap>
';
	
	
	
	foreach ($_sections as $_section => $_time) {
		$_sitemapText .= 
'	<sitemap>
		<loc>https://lastangel.ru/sitemap-'.$_section.'.xml</loc>
	</sitemap>
';
	}
	
	$_sitemapText .= '</sitemapindex>';
	$_fp = fopen("../../sitemap.xml", "w");
	fwrite($_fp, $_sitemapText);
	fclose($_fp);
	// <-- Общий файл карты сайта
	
	
	
	
	
	
	
	
	
	
	createSitemapCommonSection();
	
	
	// --> Карты сайта каждой секции
	if ($section !== false) {
		createSitemapSection($section);
	} else {
		createSitemapSection('pets');
		createSitemapSection('collections');
		createSitemapSection('news');
		createSitemapSection('stories');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	// <-- Карты сайта каждой секции
}


?>

