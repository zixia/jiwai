<?php
class KBS_Consts {
	static $strings = array(
			'error'=>'>发生错误</',
		);
	static function convertToGBK() {
		foreach (self::$strings as $k=>$v) {
			self::$strings[$k] = iconv('UTF-8', 'GBK', $v);
		}
	}
}
KBS_Consts::convertToGBK();
?>
