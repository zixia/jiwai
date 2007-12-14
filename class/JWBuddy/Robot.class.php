<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	seek@gmail.com
 * @version	$Id$
 */

/**
 * JiWai.de JWRobot_Buddy Class
 */
class JWBuddy_Robot {

	static public $buddy_robot_host = '10.1.40.10';
	static public $buddy_robot_port = 55501;

	static public $cmd = '/usr/java/jdk/bin/java';

	static public $msn_setting = array(
		'classpath' => array(
			'/nonweb/buddyRobot',
			'/javaLib/cindy.jar',
			'/javaLib/commons-logging.jar',
			'/javaLib/jml-1.0b1.jar',
		),
		'classname' => 'MsnBuddyRobot',
	);

	static public $gtalk_setting = array(
		'classpath' => array(
			'/nonweb/buddyRobot',
			'/javaLib/smack.jar',
			'/javaLib/smackx.jar',
		),
		'classname' => 'GTalkBuddyRobot',
	);

	static public function GetBuddyList( $type='msn', $username=null, $password=null )
	{
		$mc_key = JWBuddy_Import::GetCacheKeyByTypeAndUsernameAndPassword( $type, $username, $password );
		$memcache = JWMemcache::Instance();
		$memcache->Del( $mc_key, false );

		$result = self::GetBuddyListFromRobot( $type, $username, $password );
	
		$memcache->Set( $mc_key, $result, 0, 0 );
	}

	static public function GetBuddyListFromRobot( $type='msn', $username=null, $password=null )
	{
		$type = strtolower( $type );

		if ( false == in_array( $type, array('msn', 'gtalk',) ) )
			return array();

		if ( null == $username || null == $password )
			return array();

		if ( false === ( $cmd = self::BuildCommand($type, $username, $password) ) )
			return array();

		$handle = popen($cmd, 'r');
		$buddy_array = array();

		while( $buddy = fgets($handle) )
			array_push( $buddy_array, trim($buddy) );

		if ( true === ( $error_code = fclose($handle) ) )
			return $buddy_array;

		return array();
	}

	static public function GetRootPath()
	{
		return dirname(dirname(dirname( __FILE__ )));
	}
	
	static public function BuildCommand( $type, $username, $password )
	{
		switch( $type ) 
		{
			case 'msn':
				$setting = self::$msn_setting;
			break;
			case 'gtalk':
				$setting = self::$gtalk_setting;
			break;
			default:
				return false;
		}
		
		$rootpath = self::GetRootPath();
		$classpath = null;
		$classname = $setting['classname'];
		
		foreach( $setting['classpath'] AS $path)
		{
			$classpath .= ":$rootpath$path";
		}

		$classpath = trim( $classpath, ':' );

		$cmd = self::$cmd 
			. ' -cp '. $classpath
			. ' -Dusername=' . escapeshellarg($username)
		     	. ' -Dpassword=' . escapeshellarg($password)
			. ' ' . $classname
		;
		
		return $cmd;
	}

	static public function SendImportRequest( $type, $username, $password )
	{
		$info = Base64_Encode( serialize( array( $type, $username, $password ) ) ) . "\r\n";

		/* create socket */
		$sock = @socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		if( $sock == false )
			return false;

		/* connect to buddy robot */
		$flag = @socket_connect( $sock, self::$buddy_robot_host , self::$buddy_robot_port );
		if( $flag == false )
			return false;
		
		/* write to socket */
		$flag = @socket_write( $sock, $info, strlen($info) );
		if ( $flag == false ) 
			return false;

		/* close socket */
		if ( is_resource( $sock ) )
			@socket_close( $sock );

		return true;
	}

}
?>
