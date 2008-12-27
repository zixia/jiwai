<?php
/**
 * @package	 JiWai.de
 * @copyright   AKA Inc.
 * @author	  zixia@zixia.net
 * @version	 $Id$
 */

if (!defined('JW_CONFIG_FILE'))
/**
 * Path of JiWai configuration file
 *
 */
define('JW_CONFIG_FILE',   JW_ROOT.'/config/config.xml');

if (!defined('JW_CONFIG_DATA'))
/**
 * Compiled NOVAJAX configuration data
 *
 */
define('JW_CONFIG_DATA',   null);

if(!defined('TPL_COMPILED_DIR')) 
	define('TPL_COMPILED_DIR', dirname(__FILE__).'/../compiled' );
if(!defined('TPL_TEMPLATE_DIR')) 
	define('TPL_TEMPLATE_DIR', dirname(__FILE__).'/../template' );

/**
 * JiWai.de Configuration Class
 */
class JWConfig {
	/**
	 * Instance of this singleton
	 *
	 * @var JWConfig
	 */
	static private $instance__;

	static private $ini = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWConfig
	 */
	static public function &instance()
	{
		if (!isset(self::$instance__)) {
			if (JW_CONFIG_DATA) self::$instance__ = self::restore(JW_CONFIG_DATA);
			else self::$instance__ = self::load(JW_CONFIG_FILE);
			if (!self::$instance__) throw new JWException('JiWai.de: Configuration file or data not loaded.');
		}
		return self::$instance__;
	}

	static public function ini()
	{
		$config_file = JW_ROOT . 'config/config.ini' ;
		if ( null == self::$ini )
		{
			self::$ini = parse_ini_file($config_file, true);
		}
		return self::$ini;
	}

	static public function asXML() 
	{
		return self::$instance__->asXML();
	}

	static public function load($file) 
	{
		return simplexml_load_file($file);
	}

	static public function save($file) 
	{
		file_put_contents($file, self::$instance__->asXML());
	}

	static public function dump()
	{
		//var_dump($a);
		//var_dump(self::_dump($a));
		return serialize(self::_dump(self::$instance__));
	}

	static private function _dump($i)
	{
		if (is_object($i)) {
			$c = $i->children();
			if (!count($c)) {
				$o = (string)$i;
			} else {
				$o = new stdClass();
				foreach ($c as $k => $v) {
					if (isset($o->$k)) {
						if (!is_array($o->$k)) $o->$k = array($o->$k);
						$o->{$k}[] = self::_dump($v);
					} else $o->$k = self::_dump($v);
				}
			}
			$i = $o;
		} elseif (is_array($i)) {
			foreach ($i as $k => $v) {
				$i[$k] = self::_dump($v);
			}
		}
		return $i;
	}

	static public function restore($data)
	{
		return unserialize($data);
	}


}
?>
