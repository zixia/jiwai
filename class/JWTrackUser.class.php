<?php
/**
 * @author shwdai@gmail.com
 * @version $Id$
 */
class JWTrackUser{
	/**
	 * Create TrackUser | If Exists return id
	 */
	static function Create( $idUser, $word ) {
		$idUser = JWDB::CheckInt( $idUser );

		$idTrackWord = JWTrackWord::Create( $word );

		if( false == $idTrackWord )
			return false;

		$idExist = JWDB::ExistTableRow( 'TrackUser', array(
			'idUser' => $idUser,
			'idTrackWord' => $idTrackWord,
		));

		if( $idExist ){
			return $idExist;
		}
		
		$uArray = array(
			'idUser' => $idUser,
			'idTrackWord' => $idTrackWord,
			'timeCreate' => date('Y-m-d H:i:s'),
		);
		
		return JWDB::SaveTableRow( 'TrackUser', $uArray );
	}
}

?>
