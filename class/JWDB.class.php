<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Database Class
 */
class JWDB implements JWDB_Interface
{
	/**
	 * Instance of this singleton
	 *
	 * @var JWDB
	 */
	static private $msInstance;

	/**
	 * MySQLi DB Link
	 *
	 * @var JWConfig
	 */
	static private $msMysqliLink;

	const	DEFAULT_LIMIT_NUM	= 20;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWDB
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		$db_config = JWConfig::Instance();
		$db_config = $db_config->db;

		if ( !isset($db_config) )
			throw new JWException("DB can't find DB Config");

		self::$msMysqliLink = new mysqli($db_config->host
				, $db_config->username
				, $db_config->passwd
				, $db_config->dbname
			);

		if (mysqli_connect_errno())
   			throw new JWException("Connect failed: " . mysqli_connect_error());

		if (!self::$msMysqliLink->set_charset("utf8"))
			throw new JWException("Error loading character set utf8: " . $mysqli->error);

	}

	/**
	* Destructing method, write everything left
	*
	*/
	function __destruct()
	{
		self::Close();
	}

	static public function Close()
	{
		if (isset(self::$msMysqliLink))
		{
			self::$msMysqliLink->close();
			self::$msMysqliLink = null;
		}
		self::$msInstance = null;
	}

	static private function GetDb()
	{
		JWDB::Instance();

		return self::$msMysqliLink;
	}


	static public function EscapeString( $string )
	{
		try {
			return self::GetDb()->escape_string($string);
		}catch(Exception $e){
			JWDB::Close();
			throw new JWException( "DB escape_string" );
		}
	}

	static public function GetInsertedId()
	{
		$db = self::GetDb();

		return $db->insert_id;
	}


	/*
	 *	@deprecated
	 *
	 *	不建议使用，能避免使用就避免使用！
	 *
	 *	直接执行一条 sql。由于系统无法知道更新了哪个表，所以无法正常的更新 memcache，也无法通知脏数据的更新
	 *
	 */
	static public function Execute( $sql )
	{
		$db = self::GetDb();

		if ( $result = $db->query($sql) ){
			return $result;
		}else{
			JWDB::Close();
			throw new JWException( "DB Query" );
		}
		// XXX here unreachable 
		throw new JWException( "unreachable" );
	}

	/*
	 *	@param	string	SQL
	 *	@param	bool	need return more then one row?
	 *	@return	array	row or array of rows
	 */
	static public function GetQueryResult( $sql, $moreThanOne=false, $forceReload=false )
	{
		//TODO need mysqli_real_escape_string, but it do escape through db server? damn it!
		$db = self::GetDb();

		$aResult = null;

		if ( $result = $db->query($sql) ){

			if ( 0!==$result->num_rows && $moreThanOne){
				$aResult = array();
			}

			while ( $row=$result->fetch_assoc() ){
				if ( $moreThanOne ){ // array of assoc array
					array_push( $aResult, $row );
				}else{ // assoc array
					$aResult = $row;
					break;
				}
			}

		}else{
			JWDB::Close();
			throw new JWException( "DB Query" );
		}

		return $aResult;
	}

	/*
	 * 方便删除。
	 * @param condition array key为col name，val为条件值，多个条件的逻辑关系为AND
	 * @return bool
	 */
	static public function DelTableRow( $table, $condition )
	{
		$db = self::GetDb();

		$sql = "DELETE FROM $table WHERE ";
		
		$first = true;
		foreach ( $condition as $k => $v )
		{
			if ( !$first )
				$sql .= " AND ";

			if ( is_int($v) )
				$sql .= " `$k`=$v ";
			else if ( is_null($v) )
				$sql .= " `$k` IS NULL ";
			else
				$sql .= " `$k`='" . self::EscapeString($v) . "' ";
			if ( $first )
				$first = false;
		}
		// " WHERE $field='$value' AND field2=value2 ");

		$result = $db->query ($sql);

		if ( !$result ){
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}
		return true;
	}


	/*
	 *	所有的数据表都有 id 字段（PK）
	 * 	@return 	id 主键的值，或者0
	 */
	static public function ExistTableRow( $table, $condition )
	{
		$db_row = self::GetTableRow($table, $condition, 1);

		if ( empty($db_row) )
			return 0;

		return $db_row['id'];
	}


	/*
	 * @return true / false
	 */
	static public function SaveTableRow( $table, $condition )
	{
		$db = self::GetDb();

		$sql = "INSERT INTO $table ";
		
		$col_list = '';
		$val_list = '';

		$first = true;
		foreach ( $condition as $k => $v ){
			if ( !$first ){
				$col_list .= ",";
				$val_list .= ",";
			}

			$col_list .= "`$k`";

			if ( is_int($v) )
				$val_list .= "$v";
			else if ( $v === null )
				$val_list .= "NULL";
			else
				$val_list .= "'" . self::EscapeString($v) . "'";

			if ( $first )
				$first = false;
		}
		$sql .= " ($col_list) values ($val_list) ";
		// " (field1,field2) values (value1,value2)";

		$result = $db->query ($sql);

		if ( !$result ){
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return self::GetInsertedId();
	}


	/*
	 * @return bool
			succ / fail
	 */
/* 7/14/07 zixia: 作废，无法正确得到 OnDirty 的 db_row
	static public function ReplaceTableRow( $tableName, $conditionArray )
	{
		$db = self::GetDb();

		$sql = "REPLACE $tableName SET ";
		
		$first = true;
		foreach ( $conditionArray as $k => $v ){
			if ( !$first ){
				$sql .= " , ";
			}

			if ( is_null($v) )
				$sql .= "$k=NULL";
			else if ( is_int($v) )
				$sql .= "$k=$v";
			else
				$sql .= "$k='" . self::EscapeString($v) . "'";

			if ( $first )
				$first = false;
		}

		$result = $db->query ($sql);

		if ( !$result ){
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return $result;
	}
*/


	/*
	 * @return bool
			succ / fail
	 */
	static public function UpdateTableRow( $tableName, $idPK, $conditionArray )
	{
		$idPK = JWDB::CheckInt( $idPK );

		$db = self::GetDb();

		$sql = "UPDATE $tableName SET ";
		
		$first = true;
		foreach ( $conditionArray as $k => $v )
		{
			if ( !$first )
				$sql .= " , ";

			if ( is_null($v) )
				$sql .= "`$k`=NULL";
			else if ( is_int($v) )
				$sql .= "`$k`=$v";
			else
				$sql .= "`$k`='" . self::EscapeString($v) . "'";

			if ( $first)
				$first = false;
		}
		$sql .= " WHERE id=$idPK";
		// " (field1,field2) values (value1,value2)";

		$result = $db->query ($sql);

		if ( !$result ){
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return $result;
	}


	/*
	 * 	@return 	array/array of array	$row/$rows
	 */
	static public function GetTableRow( $table, $condition, $limit=1 )
	{
		$db = self::GetDb();

		$sql = "SELECT * FROM $table WHERE ";
		
		$where_condition = '';

		$first = true;
		foreach ( $condition as $k => $v ){
			if ( $first ){
				$first = false;
			} else {
				$where_condition .= " AND ";
			}

			$where_condition .= "`$k`=";

			if ( is_int($v) )
				$where_condition .= "$v";
			else
				$where_condition .= "'" . self::EscapeString($v) . "'";

		}
		$sql .= " $where_condition LIMIT $limit ";

		$result = $db->query ($sql);

		if ( !$result ){
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		if ( 1==$limit )
		{
			$row = $result->fetch_assoc();
			return $row;
		}


		$rows = array();

		while ( $row=$result->fetch_assoc() )
		{
			array_push($rows,$row);
		}

		return $rows;
	}

	/*
	 *	将一个  array 变为 "('zixia@zixia.net','msn'), ('yaya@zixia.net', 'msn')" 或者 "(1,2),(2,3)"
	 *
	 *	@param	array	$arrayOfArray	array( array('address'=>'zixia@zixia.net','type'=>'msn'), array(), array() )
	 *	@param	array	$keys			array 中 key 的顺序。array( 'address', 'type' )
	 *	@param	array	$type			取值 {int|char}，代表数据类型
	 *
	 *	@return	string	$in_condition	可以用在 IN ( $in_condition ) 的 SQL 语句中
	 */
	static public function GetInConditionFromArrayOfArray( $arrayOfArray, $keys, $type='char' )
	{
		if ( !is_array($arrayOfArray) )
			throw new JWException('must array');

		/*
		 *	根据参数，创建 reduce_function，并存入 JWFunction 以备下次使用
		 */
		$func_key_name 		= "JWDB::GetInConditionFromArrayOfArray_${type}_" . join("_", $keys);
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = '
            	if ( empty($reduce_string) )
                	$reduce_string = "";
            	else
	                $reduce_string .= ",";
	
				switch ($type)
				{
					case "int"	:
						$reduce_string 		.= "(";
						$is_first = true;
						foreach ( $keys as $key )
						{
							if ( $is_first )	$is_first = false;
							else				$reduce_string .= ",";

							$reduce_string .= intval($oneArray[$key]);
						}
						$reduce_string 		.= ")";
						break;

					case "char"	:
						//fall to default
					default		:
						$reduce_string 		.= "(";
						$is_first = true;
						foreach ( $keys as $key )
						{
							if ( $is_first )	$is_first = false;
							else				$reduce_string .= ",";

	
							$reduce_string .= "\'" . JWDB::EscapeString($oneArray[$key]) . "\'";
						}
						$reduce_string 		.= ")";
						break;
				}
				return $reduce_string;
';

			
			/*
			 *	构造参数中的缺省值 array 字符串
			 */
			$keys_array_content	= "array(";
			$is_first = true;
			foreach ( $keys as $key )
			{
				if ( $is_first )
					$is_first = false;
				else
					$keys_array_content .= ",";

				$keys_array_content .= "'$key'";
			}
			$keys_array_content .= ")";


			$reduce_function_param 	= '$reduce_string, $oneArray, $keys=' . $keys_array_content . ', $type=' . "'$type'";
			$func_callable_name 	= create_function( $reduce_function_param,$reduce_function_content );

			JWFunction::Set($func_key_name, $func_callable_name);
		}
		
		$condition_in = array_reduce($arrayOfArray, $func_callable_name, '');
		return $condition_in;

	}
	

	/*
	 *	将一个  array 变为 "1,2,3" 或者 "'zixia','daodao'"
	 *	@param	array	$ids
	 *	@param	array	$type			取值 {int|char}，代表数据类型
	 *	@return	string	$in_condition	可以用在 IN ( $in_condition ) 的 SQL 语句中
	 */
	static public function GetInConditionFromArray( $ids, $type='int' )
	{
		if ( !is_array($ids) )
			throw new JWException('must array');

		$ids = array_unique($ids);

		/*
		 *	根据参数，创建 reduce_function，并存入 JWFunction 以备下次使用
		 */
		$func_key_name 		= "JWDB::GetInConditionFromArray_$type";
		$func_callable_name	= JWFunction::Get($func_key_name);

		if ( empty($func_callable_name) )
		{
			$reduce_function_content = '
            	if ( empty($reduce_string) )
                	$reduce_string = "";
            	else
	                $reduce_string .= ",";
	
				switch ($type)
				{
					case "int"	:
						$reduce_string .= intval($id);
						break;
					case "char"	:
						//fall to default
					default		:
						$reduce_string .= "\'" . JWDB::EscapeString($id) . "\'";
						break;
				}
				return $reduce_string;
';

			$func_callable_name = create_function( "\$reduce_string, \$id, \$type='$type'"
														,"$reduce_function_content"
													);
			JWFunction::Set($func_key_name, $func_callable_name);
		}
		
		$condition_in = array_reduce($ids, $func_callable_name, '');
		return $condition_in;
	}
	
	/*
	 *	检查变量是否为 int 值，并返回 int 类型的数字
	 *	注意：这里的 int 不可以为 0
	 */
	static public function CheckInt($id)
	{
		$id = intval($id);

		if ( 0>=$id )
			throw new JWException('must int!');

		return $id;
	}


	/*
	 *	查找某个查询中最大的 id
	 *	功能说明：有些时候我们根据一个条件，查找到了相关的id。但是还需对这些 id 做一些运算，得到结果。
					为了 cache 结果，我们需要知道条件查询的 id 是否有变化。所以用这个max id作为版本号
	 */
	static public function GetMaxId($table, $condition)
	{
		$db = self::GetDb();

		$sql = "SELECT MAX(id) as idMax  FROM $table WHERE ";
		
		$first = true;
		foreach ( $condition as $k => $v )
		{
			if ( $first )
				$first = false;
			else 
				$sql .= " AND ";

			if ( is_int($v) )
				$sql .= " `$k`=$v ";
			else
				$sql .= " `$k`='" . self::EscapeString($v) . "' ";
		}
		// " WHERE $field='$value' AND field2=value2 ");

		$result = $db->query ($sql);

		if ( !$result )
		{
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		$row = $result->fetch_assoc();
		return $row['idMax'];
	}

	/*
	 *	当一个用户被删除时，相关的外键会变为 NULL
	 *	应该定期清理
	 */
	static public function CleanDiedRows()
	{
		$sqls = array (
					 'DELETE FROM Status 		WHERE idUser IS NULL'
					,'DELETE FROM Device 		WHERE idUser IS NULL'
					,'DELETE FROM RememberMe	WHERE idUser IS NULL'
					,'DELETE FROM Picture		WHERE idUser IS NULL'
					,'DELETE FROM Invitation	WHERE (idUser IS NULL)'

					,'DELETE FROM Friend 		WHERE (idUser IS NULL) OR (idFriend IS NULL)'
					,'DELETE FROM Follower 		WHERE (idUser IS NULL) OR (idFollower IS NULL)'

					,'DELETE FROM Favourite		WHERE (idUser IS NULL) OR (idStatus IS NULL)'
					,'DELETE FROM Invitation	WHERE (idInvitee IS NULL) AND (timeRegister IS NOT NULL)'
				);
		 
		foreach ( $sqls as $sql ) 
			self::Execute($sql);

		return;
	}


	/*
	 *	返回 Mysql NOW() 函数返回的串
	 */
	static public function MysqlFuncion_Now($timestamp=null)
	{
		if ( empty($timestamp) )
			$timestamp = time();

		return date("Y-m-d H:i:s", $timestamp);
	}

	static public function MysqlFuncion_Aton($dottedIp)
	{
		return sprintf('%u', ip2long($dottedIp));
	}

	/*
	 * @return bool
			succ / fail
	 */
	static public function UpdateTableRowNumber( $table, $idPK, $column, $value=1, $reset=false)
	{
		$idPK = JWDB::CheckInt( $idPK );
		$value = intval( $value );
        if (false==$reset)
        {
            $sql=<<<_SQL_
UPDATE $table 
    SET `$column` = `$column` + $value
    WHERE id = $idPK;
_SQL_;
        }
        else
        {
            $sql=<<<_SQL_
UPDATE $table 
    SET `$column` = $value
    WHERE id = $idPK;
_SQL_;
        }

        $db = self ::GetDb();
		$result = $db->query ($sql);

		if ( !$result ){
			JWDB::Close();
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return $result;
    }

}
?>
