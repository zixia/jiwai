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

		if ( !($word = trim($word)) )
			return true;

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
			'wordTerm' => trim($word),
			'timeCreate' => date('Y-m-d H:i:s'),
		);
		
		return JWDB::SaveTableRow( 'TrackUser', $uArray );
	}

	/**
	 * Destroy TrackUser
	 */
	static function Destroy( $idUser, $word ) {
		$idUser = JWDB::CheckInt( $idUser );

		$idTrackWordSequence = JWTrackWord::GetUserTrackOrder($word);
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
	static public function GetWordListByHot($size = 100) {

		$mc_key = JWDB_Cache::GetCacheKeyByFunction( array('JWTrackUser', 'GetWordListByHot'), array($size) );
		$memcache = JWMemcache::Instance();
		$words = $memcache->Get( $mc_key );

		$expire = 60 * 15; //15 mins

		if ( false == $words ) {	
			$sql = "SELECT wordTerm,COUNT(1) AS count FROM TrackUser GROUP BY wordTerm ORDER BY count DESC LIMIT $size";

			$words = JWDB::GetQueryResult( $sql, true );
			if ( $words ) {
				$memcache->Set($mc_key, $words, 0, $expire);
			}
		}

		return $words;
	}

	/**
	 * Get WordList by idUser
	 */
	static function GetWordListByIdUser( $idUser, $join=true ){

		$idUser = JWDB::CheckInt( $idUser );

		$sql = "SELECT wordTerm FROM TrackUser WHERE idUser=$idUser";

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return $join ? null : array();

		$rtn = array();
		foreach( $rows as $r ) {
			$rtn[] = $r['wordTerm'];
		}

		return $join ? join(', ', $rtn) : $rtn;
	}

	/**
	 * GetIdUsersBySequence
	 */
	static function GetIdUsersBySequence( $sequence = array() ){
		if( empty( $sequence ) )
			return array();

		settype( $sequence, 'array' );

		$sequenceString = implode( "','", $sequence );
		$sql = "SELECT distinct( idUser ), wordTerm FROM TrackUser WHERE idTrackWordSequence IN ('{$sequenceString}')";

		$rows = JWDB::GetQueryResult( $sql, true );
		if( empty( $rows ) )
			return array();

		$ids = JWUtility::GetColumn($rows, 'idUser');
		$words = JWUtility::GetColumn($rows, 'wordTerm');

		return array_combine($ids, $words);
	}

	static function IsTrackUser( $idUser, $word ) {

		$idUser = abs(intval($idUser));

		if ( !$idUser ) return false;

		$array = array(
				'idUser' => $idUser,
				'wordTerm' => $word,
				);

		return JWDB::ExistTableRow( 'TrackUser' , $array );
	}

	/**
	 * GetIdUsersByWord
	 */
	static function GetIdUsersByWord( $word, $size = 12 ){
		$qword = JWDB::EscapeString($word);
		$sql = "SELECT idUser FROM TrackUser WHERE wordTerm = '{$qword}' ORDER BY timeCreate DESC LIMIT $size";

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
