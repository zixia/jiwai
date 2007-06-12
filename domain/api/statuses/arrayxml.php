<?php
function array_to_xml($array, $level=1) {
	$xml = '';
	
	foreach ($array as $key=>$value) {
		
		$key = strtolower($key);
		if($value===false) 
			$value='false';

		if (is_array($value)) { // 大于一层的 assoc array
			$xml .= str_repeat("\t",$level)
			."<$key>\n"
			. array_to_xml($value, $level+1)
			. str_repeat("\t",$level)."</$key>\n";
		} else { // 一层的 assoc array
		//			if (trim($value)!='') 
		//			{
			if (htmlspecialchars($value)!=$value) {
			$xml .= str_repeat("\t",$level)
			."<$key><![CDATA[$value]]></$key>\n";
			} else {
			$xml .= str_repeat("\t",$level).
			"<$key>$value</$key>\n";
			}
			//			}
		}
	}
	return $xml;
}
?>
