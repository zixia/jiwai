<?php
require_once(dirname(__FILE__) . "/../jiwai.inc.php");

$pic_root = "/opt/storage/jiwai/picture/0/";
// 750/reiserfs/jiwai/picture/0/8/9

list_file($pic_root);

function list_file($dir)
{
//echo "-> $dir\n";
	if ( ! is_dir($dir) )
	{
		echo $dir . "\n";
		return;
	}

	$handle = opendir($dir);

	while ( false !== ($file=readdir($handle)) )
	{
		if ( $file=='.' || $file=='..' )
			continue;

		if ( is_dir("$dir/$file") )
		{
			list_file("$dir/$file");
		}
		else
		{
			if ( preg_match("/^picture\.(...)$/i",$file,$matches) )
			{
				//echo "$dir/ /$file\n";
				$src_file = "$dir/$file";
				$dst_file1 = "$dir/thumb96s.jpg";
				$dst_file2 = "$dir/thumb48s.jpg";

				if ( ! file_exists($dst_file1) )
				{
					echo "ConvertThumbnail96($src_file, $dst_file1)\n";
					JWPicture::ConvertThumbnail96Lite($src_file, $dst_file1);
				}
				if ( ! file_exists($dst_file2) )
				{
					echo "ConvertThumbnail96($src_file, $dst_file2)\n";
					JWPicture::ConvertThumbnail48Lite($src_file, $dst_file2);
				}
			}
		}
	}

	closedir($handle);
}



?>
