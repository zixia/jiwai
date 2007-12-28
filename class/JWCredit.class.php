<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	zixia@zixia.net
 * @version	$Id$
 */

/**
 * JiWai.de Credit Class
 */
class JWCredit {

	/**
	 * const credit type
	 */
	const CREDIT_BLOCK = 'BLOCK';
	const CREDIT_LIMIT = 'LIMIT';
	const CREDIT_ANONYMOUS = 'ANONYMOUS';
	const CREDIT_NORMAL = 'NORMAL';
	const CREDIT_HONOR = 'HONOR';
	const CREDIT_WIZARD = 'WIZARD';
	const CREDIT_ADMIN = 'ADMIN';

	static private $m_credit_order = array(
		'BLOCK' => 10, 
		'LIMIT' => 20,
		'ANONYMOUS' => 30,
		'NORMAL' => 40,
		'HONOR' => 50,
		'WIZARD' => 60,
		'ADMIN' => 70,
	);

	const OP_EQUAL = 'eq';
	const OP_NOTEQUAL = 'neq';
	const OP_LESSTHAN = 'lt';
	const OP_GREATTHAN = 'gt';
	const OP_NOTLESSTHAN = 'nlt';
	const OP_NOTGREATTHAN = 'ngt';
	
	/**
	 * Constructing method, save initial state
	 *
	 */
	private function __construct()
	{
	}

	/**
	 * destroy db row by user_id;
	 */
	static public function DestroyDbRowByIdUser($user_id)
	{
		$user_id = JWDB::CheckInt($user_id);
		$user_idCredit = JWDB::CheckInt($user_id);

		return JWDB::DelTableRow( 'Credit', array(
			'idUser' => $user_id,
		));
	}

	/**
	 * get db rows by user ids 
	 */
	static public function GetDbRowByIdUsers( $user_ids=array() ) 
	{
		if ( empty( $user_ids ) )
			return array();

		$id_string = implode( $user_ids, ',' );
		$sql = "SELECT * FROM Credit WHERE idUser IN ($id_string)";

		$rows = JWDB::GetQueryResult( $sql, true );

		$rtn_array = array();
		if( false == empty($rows) )
		{
			foreach( $rows as $one )
			{
				$rtn_array[ $one['idUser'] ] = $one;
			}
		}

		if( count($rows) < count($user_ids) )
		{
			foreach( $user_ids as $user_id )
			{
				if( false == isset( $rtn_array[$user_id] ) )
				{
					$rtn_array[$user_id] = array( 
						'id' => NULL, 
						'idUser' => $user_id, 
						'credit' => self::CREDIT_NORMAL,
					);
				}
			}
		}
		return $rtn_array;
	}

	/**
	 * get db row by user id
	 */
	static public function GetDbRowByIdUser( $user_id ) {

		$user_id = JWDB::CheckInt( $user_id );

		$rows = self::GetDbRowByIdUsers( array($user_id) );

		return isset($rows[ $user_id ]) ? $rows[ $user_id ] : array();
	}


	/**
	 * create
	 */
	static public function Create($user_id, $credit=self::CREDIT_BLOCK )
	{
		$user_id = JWDB::CheckInt($user_id);

		$exist_array = array(
			'idUser' => $user_id,
		);

		if( $exist_id = JWDB::ExistTableRow( 'Credit', $exist_array ) )
		{
			$update_array = array(
				'credit' => $credit,
			);
			JWDB::UpdateTableRow( 'Credit', $exist_id, $update_array );
			return $exist_id;
		}

		$exist_array['credit'] = $credit;
		return JWDB::SaveTableRow( 'Credit', $exist_array );
	}

	/**
	 * is user_ids in certain credit
	 * flag ( eq, neq, lt, gt, nlt, ngt ) => ( ==, !=, <, >, >=, <= )
	 */
	 static public function IsCreditIdUsers( $user_ids, $credit = self::CREDIT_BLOCK, $flag=self::OP_EQUAL ) 
	 {
		if ( empty( $user_ids ) )
			return array();

		$rows = self::GetDbRowByIdUsers( $user_ids ) ;
		if( empty( $rows ) )
			return array();

		$op = '==';
		switch( $flag )
		{
			case self::OP_EQUAL:
				$op = '==';
			break;
			case self::OP_NOTEQUAL:
				$op = '!=';
			break;
			case self::OP_LESSTHAN:
				$op = '<';
			break;
			case self::OP_GREATTHAN:
				$op = '>';
			break;
			case self::OP_NOTLESSTHAN:
				$op = '>=';
			break;
			case self::OP_NOTGREATTHAN:
				$op = '<=';
			break;
		}

		$rtn_array = array();
		foreach( $rows as $one ) 
		{
			eval('$value = '. self::Compare( $one['credit'], $credit ) . $op . '0;');
			$rtn_array[ $one['idUser'] ] = $value;
		}

		return $rtn_array;
	 }

	/**
	 * is user_id lower certain credit
	 */
	static public function IsCreditIdUser( $user_id, $credit = self::CREDIT_BLOCK, $flag=self::OP_EQUAL)
	{
		$user_id = JWDB::CheckInt( $user_id );
		$rows = self::IsCreditIdUsers( array($user_id), $credit, $flag );
		return isset($rows[ $user_id ]) ? $rows[ $user_id ] : false;
	}
	
	/**
	 * Comparable interface
	 */
	static public function Compare( $credit_o, $credit_b )
	{
		return ( self::$m_credit_order[ $credit_o ] - self::$m_credit_order[ $credit_b ] );
	}
}
?>
