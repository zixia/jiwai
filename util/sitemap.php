#!/usr/bin/php
<?php
require_once(dirname(__FILE__) . "/../jiwai.inc.php");

$sitepath 	= "/vhost/jiwai.de/webroot";
$website	= "http://jiwai.de";

chdir($sitepath);

$sql = <<<_SQL_
SELECT	 idUser
		,UNIX_TIMESTAMP(MAX(timeCreate)) as mtime
FROM 	 Status
WHERE	idUser IS NOT NULL
GROUP BY	idUser
_SQL_;


$result = JWDB::GetQueryResult($sql,true);

if (!$handle = fopen("sitemap.xml", 'w')) 
{
	echo "Cannot open file sitemap";
	exit;
}


$xml = <<<_XML_
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84
	http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">

_XML_;

if (fwrite($handle, $xml) === FALSE) 
{
       echo "Cannot write to file (sitemap)";
       exit;
}

foreach ( $result as $row )
{
//die(var_dump($row));
	$user_id 	= $row['idUser'];
	$mtime		= $row['mtime'];
	$user_name	= JWUser::GetUserInfo($user_id,'nameScreen');


	$mod		= date('c', $mtime);

	$freq		= 'daily';
	$priority	= 0.7;

	$xml = <<<_XML_
<url>
      <loc>$website/$user_name/</loc>
      <lastmod>$mod</lastmod>
      <changefreq>$freq</changefreq>
      <priority>$priority</priority>
</url>

_XML_;

	if (fwrite($handle, $xml) === FALSE) {
       echo "Cannot write to file ($filename)";
       exit;
   }


}

$xml = <<<_XML_
</urlset>
_XML_;

if (fwrite($handle, $xml) === FALSE) 
{
       echo "Cannot write to file (sitemap)";
       exit;
}

fclose($handle);

unlink ( "sitemap.xml.gz" );
system("gzip sitemap.xml");
?>
