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
class JWDB {
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
	static private $mysqli_link__;

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
		$db_config = JWConfig::instance();
		$db_config = $db_config->db;

		if ( !isset($db_config) )
			throw new JWException("DB can't find DB Config");

		self::$mysqli_link__ = new mysqli($db_config->host
				, $db_config->username
				, $db_config->passwd
				, $db_config->dbname
			);

		if (mysqli_connect_errno())
   			throw new JWException("Connect failed: " . mysqli_connect_error());

		if (!self::$mysqli_link__->set_charset("utf8"))
			throw new JWException("Error loading character set utf8: " . $mysqli->error);

	}

	static public function close()
	{
		return ;
		//XXX need to deal with more conditions. use function init_db? provent to init_db every time?
		if (isset(self::$mysqli_link__)){
			self::$mysqli_link__->close();
			self::$mysqli_link__ = null;
		}
	}

	static public function GetDb()
	{
		if (empty(self::$mysqli_link__)){
			JWDB::instance();
		}

		return self::$mysqli_link__;
	}


	static public function escape_string( $string )
	{
		return self::GetDb()->escape_string($string);
	}

	static public function GetInsertId()
	{
		$db = self::GetDb();

		return $db->insert_id;
	}

	static public function Execute( $sql )
	{
		$db = self::GetDb();

		if ( $result = $db->query($sql) ){
			return $result;
		}else{
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
	static public function GetQueryResult( $sql, $more_than_one=false )
	{
		//TODO need mysqli_real_escape_string, but it do escape through db server? damn it!
		$db = self::GetDb();

		$aResult = null;

		if ( $result = $db->query($sql) ){

			if ( 0!==$result->num_rows && $more_than_one){
				$aResult = array();
			}

			while ( $row=$result->fetch_assoc() ){
				if ( $more_than_one ){ // array of assoc array
					array_push( $aResult, $row );
				}else{ // assoc array
					$aResult = $row;
					break;
				}
			}

		}else{
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
		foreach ( $condition as $k => $v ){
			if ( !$first )
				$sql .= " AND ";

			if ( is_int($v) )
				$sql .= " $k=$v ";
			else
				$sql .= " $k='" . self::escape_string($v) . "' ";

			if ( $first = true )
				$first = false;
		}
		// " WHERE $field='$value' AND field2=value2 ");

		$result = $db->query ($sql);

		if ( !$result ){
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
		$db = self::GetDb();

		$sql = "SELECT id FROM $table WHERE ";
		
		$first = true;
		foreach ( $condition as $k => $v ){
			if ( !$first ){
				$sql .= " AND ";
			}

			if ( is_int($v) )
				$sql .= " $k=$v ";
			else
				$sql .= " $k='" . self::escape_string($v) . "' ";

			if ( $first = true )
				$first = false;
		}
		$sql .= ' LIMIT 1 ';
		// " WHERE $field='$value' AND field2=value2  LIMIT 1");

		$result = $db->query ($sql);

		if ( !$result ){
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

        if ( $result->num_rows==0 ){
			return 0;
		}

		$row = $result->fetch_assoc();
		return $row['id'];
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

			$col_list .= "$k";

			if ( is_int($v) )
				$val_list .= "$v";
			else
				$val_list .= "'" . self::escape_string($v) . "'";

			if ( $first = true )
				$first = false;
		}
		$sql .= " ($col_list) values ($val_list) ";
		// " (field1,field2) values (value1,value2)";

		$result = $db->query ($sql);

		if ( !$result ){
			throw new JWException ("DB Error: " . $db->error);
			return false;
		}

		return true;
	}


	/*
	 * @return bool
			succ / fail
	 */
	static public function UpdateTableRow( $tableName, $idPK, $conditionArray )
	{
		if ( ! is_int($idPK) )
			throw new JWException ("idPK need be int");

		$db = self::GetDb();

		$sql = "UPDATE $tableName SET ";
		
		$first = true;
		foreach ( $conditionArray as $k => $v ){
			if ( !$first ){
				$sql .= " , ";
			}

			if ( is_int($v) )
				$sql .= "$k=$v";
			else
				$sql .= "$k='" . self::escape_string($v) . "'";

			if ( $first = true )
				$first = false;
		}
		$sql .= " WHERE id=$idPK";
		// " (field1,field2) values (value1,value2)";

		//die($sql);

		$result = $db->query ($sql);

		if ( !$result ){
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
				$where_condition .= ",";
			}

			$where_condition .= "$k=";

			if ( is_int($v) )
				$where_condition .= "$v";
			else
				$where_condition .= "'" . self::escape_string($v) . "'";

		}
		$sql .= " $where_condition LIMIT $limit ";

		$result = $db->query ($sql);

		if ( !$result ){
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

		$reduce_function_content = <<<_FUNC_
            if ( empty(\$reduce_string) )
                \$reduce_string = '';
            else
                \$reduce_string .= ",";

			switch (\$type)
			{
				case 'int'	:
					\$reduce_string .= intval(\$id);
					break;
				case 'char'	:
					//fall to default
				default		:
					\$reduce_string .= "'\$id'";
					break;
			}
			return \$reduce_string;
_FUNC_;
		$condition_in = array_reduce(	$ids
										,create_function(
												"\$reduce_string, \$id, \$type='$type'"
												,"$reduce_function_content"
											)
										,''
									);
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
}
?>
