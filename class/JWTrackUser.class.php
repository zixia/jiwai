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

	/**
	 * Destroy TrackUser
	 */
	static function Destroy( $idUser, $word ) {
		$idUser = JWDB::CheckInt( $idUser );

		$idExist = JWDB::ExistTableRow( 'TrackWord', array( 'word' => strtolower(trim($word)),) );
		if( false == $idExist )
			return true;

		$eArray = array(
			'idUser' => $idUser,
			'idTrackWord' => $idExist,
		);
		$idExist = JWDB::ExistTableRow( 'TrackUser', $eArray );	
		
		if( false == $idExist )
			return true;

		return JWDB::DelTableRow( 'TrackUser', array( 'id'=> $idExist, ) );
	}

	/**
	 * Get IdWords by IdUser
	 */
	static function GetIdWordsByIdUser( $idUser ){
		$idUser = JWDB::CheckInt( $idUser );
		$sql = <<<_SQL_
SELECT idTrackWord
	FROM
		TrackUser
	WHERE
		idUser = $idUser
	ORDER BY timeCreate DESC
_SQL_;
		$rows = JWDB::GetQueryResult( $sql , true );
		if( empty( $rows ) )
			return array();
		
		$rtn = array();
		foreach( $rows as $r ) {
			if( $r['idTrackWord'] ) {
				array_push( $rtn, $r['idTrackWord'] );
			}
		}

		return $rtn;
	}

	/**
	 * Get WordList by idUser
	 */
	static function GetWordListByIdUser( $idUser ){
		$idWords = self::GetIdWordsByIdUser( $idUser );
		if( empty( $idWords ) ) 
			return null;

		$trackWords = JWTrackWord::GetDbRowsByIds( $idWords );
		if( empty( $trackWords ) )
			return null;
		
		$rtn = null;
		foreach( $trackWords as $r ) {
			$rtn .= ", $r[word]";
		}

		return trim( $rtn, ', ');
	}
}
?>
