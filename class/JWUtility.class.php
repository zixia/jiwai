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

	static public function GetAstro($birthday='0000-00-00') {
		list($year, $month, $day) = explode('-', $birthday);
		$date = strtotime('1999-'.$month.'-'.$day);
		$astros = array(
				array("摩羯座",strtotime('1999-00-01')),
				array("水瓶座",strtotime('1999-00-20')),
				array("双鱼座",strtotime('1999-01-19')),
				array("白羊座",strtotime('1999-02-21')),
				array("金牛座",strtotime('1999-03-21')),
				array("双子座",strtotime('1999-04-21')),
				array("巨蟹座",strtotime('1999-05-22')),    
				array("狮子座",strtotime('1999-06-23')),
				array("处女座",strtotime('1999-07-23')),
				array("天秤座",strtotime('1999-08-23')),
				array("天蝎座",strtotime('1999-09-23')),
				array("射手座",strtotime('1999-10-22')),
				array("摩羯座",strtotime('1999-11-22')), 
			       );

		for($i=12;$i>=0;$i--){
			if ($date >= $astros[$i][1]) 
				return $astros[$i][0]; 
		}
	}
}
?>
