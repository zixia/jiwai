<?php

/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      shwdai@gmail.com 
 *
 */
class JWRemote{

	static function GetPublicStatus($num=100) {
		$r = self::Get("PUBLIC $num", '10.1.40.10', 4001);
		return self::_BuildStatusData($r);
	}

	static function GetFriendStatus($user_id=0) {
		if (is_array($user_id)) {
			$friend_ids = $user_id;
		} else {
			$friend_ids = self::GetFriendId($user_id);
		}
		if (empty($friend_ids)) { 
			$r = array(); 
		} else {
			$idstring = join(',', $friend_ids);
			$r = self::Get("GET $idstring", '10.1.40.10', 4001);
		}
		return self::_BuildStatusData($r);
	}

	static function GetActivateUserId($friend_ids=array(), $num=60) {
		if (empty($friend_ids)) { 
			return  array(); 
		}
		$idstring = join(',', $friend_ids);
		return self::Get("GET $num $idstring", '10.1.40.10', 4003);
	}

	static function GetFriendId($user_id=0) {
		$r = self::Get("MEFOLLOW $user_id", '10.1.40.10', 4002);
		$r[] = strval($user_id);
		return array_unique($r);
	}

	static private function _BuildStatusData($r=array()){
		settype($r, 'array');
		$sa = array();
		$ua = array();
		foreach( $r AS $k ) {
			$sa[] = $k[0];
			$ua[] = $k[1];
		}
		return array(
			'status_ids' => $sa,
			'user_ids' => $ua,
		);
	}

	static function Get($cmd, $host='10.1.40.10', $port=4001){
		if( ! ( $sock = @socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) ){
			@socket_close($sock);
			return array();
		}
		if( ! @socket_connect($sock, $host, $port)){
			@socket_close($sock);
			return array();
		}
		if( ! socket_write( $sock, trim($cmd)."\r\n")) {
			@socket_close($sock);
			return array();
		}
		$line = trim(socket_read($sock, 8192));
		$r = @json_decode($line);
		socket_close($sock);
		return $r ? $r : array();
	}
}
