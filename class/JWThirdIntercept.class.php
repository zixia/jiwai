<?php
/**
 * @author shwdai@gmail.com
 * @version $Id$
 */
class JWThirdIntercept{
	static function IsAutoFriendShip( $idFriend ) {
		$idFriend = JWDB::CheckInt( $idFriend );
		if( $idFriend == 35559 )  //35559 = qian8ao
			return true;

		return false;
	}
}
?>
