<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	shwdai@gmail.com
 * @version		$Id$
 */

/**
 * JiWai.de BindOther Class
 */
class JWTag {

    static public function IsValidTagName( $tag_name ) 
	{
		if( mb_strlen( $tag_name ) < 2 || mb_strlen( $tag_name ) > 20 ) 
				return false;
		if( false !== strpos( $tag_name, '#' ) )
				return false;

		return true;
	}
    
    /**
     * Create a tag
     */
    static public function Create( $tag_name=null ) 
    {
        if( false === self::IsValidTagName( $tag_name ) )
            return false;

        return JWDB::SaveTableRow( 'Tag', array(
            'name' => $tag_name,
            'description' => $tag_name,
            'timeCreate' => JWDB::MysqlFuncion_Now(),
        ));
    }

    /**
     * Get DbRow
     */
    static public function GetDbRowByName( $tag_name=null ) 
    {
	$tag_name = trim( $tag_name );
        if( null == $tag_name )
            return false;

        $sql = "SELECT * FROM Tag WHERE name='$tag_name'";
        $row = JWDB::GetQueryResult( $sql );

        if( empty($row) )
            return array();

        return $row;
    }

    /**
     * Get DbRow
     */
    static public function GetDbRowById( $tag_id=null ) 
    {
	$tag_id = JWDB::CheckInt( $tag_id );
	$rows = self::GetDbRowsByIds( array( $tag_id ) );
	return isset( $rows[$tag_id] ) ? $rows[ $tag_id ] : array();
    }

    /**
     * Get DbRows
     */
    static public function GetDbRowsByIds( $tag_ids = array() )
    {
	    if( empty( $tag_ids ))
		    return array();

	    $id_strings = implode( ',', $tag_ids );
	    $sql = "SELECT * FROM Tag WHERE id IN ($id_strings)";
	    $rows = JWDB::GetQueryResult( $sql, true);

	    if( empty($rows) )
		    return array();

	    $rtn_array = array();
	    foreach( $rows as $one ) 
	    {
		    $rtn_array[ $one['id'] ] = $one;
	    }

	    return $rtn_array;
    }

	/**
	 * Get All DbRows of Tag [ limit ]
	 */
	static public function GetDbRowsAll( $num = JWStatus::DEFAULT_STATUS_NUM, $start = 0) 
	{

		$sql = <<<_SQL_
SELECT * 
	FROM
		Tag
ORDER BY 	timeCreate desc
LIMIT		$start, $num
_SQL_;

		$rows = JWDB::GetQueryResult( $sql, true );

		if (empty($rows))
			return array();

		return $rows;
	}

	/**
	 * Get sum of Tag All Post
	 */
	static public function GetCountPostAll()
	{  

		$sql = <<<_SQL_
SELECT sum(countPost) AS sum
	FROM
		Tag
_SQL_;
		$row = JWDB::GetQueryResult( $sql );

		return $row['sum'];
	}

	/**
	 * Get sum of Tag All Topic
	 */
	static public function GetCountTopicAll()
	{  

		$sql = <<<_SQL_
SELECT sum(countTopic) AS sum
	FROM
		Tag
_SQL_;
		$row = JWDB::GetQueryResult( $sql );

		return $row['sum'];
	}
}
?>
