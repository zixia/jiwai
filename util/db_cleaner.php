<?php
require_once(dirname(__FILE__) . "/../jiwai.inc.php");

echo date("Y-m-d H:i:s", time()) . " db_cleaner start\n";

JWDB::CleanDiedRows();

$sql = <<<_SQL_
DELETE FROM	Status
WHERE		idUser=1080
			AND (
				status LIKE '%我的博客,欢迎大家'  
				OR status LIKE '%hhhkkkjjj%' 
				OR status LIKE '%41073996%'
			) 
_SQL_;

JWDB::Execute($sql);

echo date("Y-m-d H:i:s", time()) . " db_cleaner done\n";
?>
