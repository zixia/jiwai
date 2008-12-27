<?php
/**
 * @author: seek@jiwai.com
 */
class JWUtility {

	static public function Option($a=array(), $v=null, $all=null)
	{
		$option = null;
		if ( $all ){
			$selected = ($v) ? null : 'selected';
			$option .= "<option value='' $selected>$all</option>";
		}

		$v = explode(',', $v);
		settype($v, 'array');
		foreach( $a AS $key=>$value )
		{
			$selected = in_array($key, $v) ? 'selected' : null;
			$option .= "<option value='$key' $selected>$value</option>";
		}
		
		return $option;
	}

	static public function SortArray($a=array(), $s=array(), $key=null)
	{
		if ( $key ) $a = self::GetColumn($a, $key, false);
		$ret = array();
		foreach( $s AS $one ) 
		{
			if ( isset($a[$one]) )
				$ret[$one] = $a[$one];
		}
		return $ret;
	}

	static public function GetColumn($a=array(), $column='id', $null=true)
	{
		$ret = array();
		foreach( $a AS $one )
		{   
			if ( $null || @$one[ $column ] )
				$ret[] = @$one[ $column ];
		}   

		return $ret;
	}

	/* support 2-level now */
	static public function AssColumn($a=array(), $column='id')
	{
		$two_level = func_num_args() > 2 ? true : false;
		if ( $two_level )
			$scolumn = func_get_arg(2);

		$ret = array();
		if ( false == $two_level )
		{   
			foreach( $a AS $one )
			{   
				$ret[ @$one[$column] ] = $one;
			}   
		}   
		else
		{   
			foreach( $a AS $one )
			{   
				if ( false==isset( $ret[ @$one[$column] ] ) )
					$ret[ @$one[$column] ] = array();

				$ret[ @$one[$column] ][ @$one[$scolumn] ] = $one;
			}
		}
		return $ret;
	}
}
?>
