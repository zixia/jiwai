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

		$idTrackWordSequence = JWTrackWord::GetUserTrackOrder($word);

		if( null == $idTrackWordSequence )
			return true;

		$idExist = JWDB::ExistTableRow( 'TrackUser', array(
			'idUser' => $idUser,
			'idTrackWordSequence' => $idTrackWordSequence,
		));

		if( $idExist ){
			return $idExist;
		}
		
		$uArray = array(
			'idUser' => $idUser,
			'idTrackWordSequence' => $idTrackWordSequence,
			'wordTerm' => $word,
			'timeCreate' => date('Y-m-d H:i:s'),
		);
		
		return JWDB::SaveTableRow( 'TrackUser', $uArray );
	}

	/**
	 * Destroy TrackUser
	 */
	static function Destroy( $idUser, $word ) {
		$idUser = JWDB::CheckInt( $idUser );

		$idTrackWordSequence = JWTrackUser::GetUserTrackOrder($word);
		if( null == $idTrackWordSequence )
			return true;

		$eArray = array(
			'idUser' => $idUser,
			'idTrackWordSequence' => $idTrackWordSequence,
		);
		$idExist = JWDB::ExistTableRow( 'TrackUser', $eArray );	
		
		if( false == $idExist )
			return true;

		return JWDB::DelTableRow( 'TrackUser', array( 'id'=> $idExist, ) );
	}

	/**
	 * Get WordList by idUser
	 */
	static function GetWordListByIdUser( $idUser ){

		$idUser = JWDB::CheckInt( $idUser );

		$sql = "SELECT wordTerm FROM TrackUser WHERE idUser=$idUser";
		
		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return null;

		$rtn = null;
		foreach( $rows as $r ) {
			$rtn .= ", $r[wordTerm]";
		}

		return trim( $rtn, ', ');
	}

	/**
	 * GetIdUsersBySequence
	 */
	static function GetIdUsersBySequence( $sequence = array() ){
		if( empty( $sequence ) )
			return array();

		settype( $sequence, 'array' );

		$sequenceString = implode( "'", $sequence );
		$sql = <<<_SQL_
SELECT distinct( idUser ) 
	FROM 
		TrackUser
	WHERE
		idTrackWordSequence IN ('$sequenceString')
_SQL_;

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$rtn = array();
		foreach( $rows as $r ) {
			array_push( $rtn, $r['idUser'] );
		}

		return $rtn;
	}
}
?>
