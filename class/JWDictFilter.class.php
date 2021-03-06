<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * class JWDictFilter
 */
class JWDictFilter {

	private $cnWordList = array();
	private $enWordList = array();

	private $wordPath = null;
	private $lastModified = 0;

	public function __construct() {}

	public static function GetLastModified($wordPath)
	{
		if( file_exists( $wordPath ) )
			return filemtime( $wordPath );
		return 0;
	}

	public function CheckLastModified()
	{
		if ( null == $this->wordPath )
			return ;

		$lastModified = self::GetLastModified($this->wordPath);
		if ( $lastModified != $this->lastModified ) 
		{
			$this->Load( $this->wordPath, true );
		}
	}

	public function Load( $wordPath, $forceReload=false ) 
	{
		if ( false == empty($this->cnWordList) && false==$forceReload )
			return;

		$this->wordPath = $wordPath;
		$this->lastModified = self::GetLastModified($this->wordPath);
		$cacheKey = JWDB_Cache::GetCacheKeyByFunction( array('JWDictFilter', 'Load'), $wordPath );
		/* 
		 * Get Loaded Result from Memcache 
		 */
		$cacheResult = JWMemcache::Get( $cacheKey ) ;
		if( is_array($cacheResult) ) 
		{
			if( $cacheResult['lastModified'] == $this->lastModified ) 
			{
				$this->cnWordList = $cacheResult['cn'];	
				$this->enWordList = $cacheResult['en'];	
				return;
			}
		}
		
		/**
		 * reload dict
		 */
		$this->enWordList = $this->cnWordList = array();

		if ( $fd = @fopen($wordPath, 'r') ) 
		{
			while ($line = fgets($fd, 256)) 
			{
				$line = trim($line);
				$word = $line;

				if (preg_match("/[[:alpha:]]+/", $line))
				{
					$this->enWordList[strtolower($line)] = 1;
					continue;
				}

				if (strlen($word) < 4)
					continue;
					
				$first = substr($word, 0, 2);
				if (!isset($this->cnWordList[$first])) 
					$this->cnWordList[$first] = array();

				if( isset($this->cnWordList[$first][$word]) )
					$this->cnWordList[$first][$word] |= 0x01;
				else
					$this->cnWordList[$first][$word] = 0x01;

				//nest word
				$len = strlen($word);
				while ($len > 4) 
				{
					$len -= 2;
					$word = substr($word, 0, -2);
					if ( isset($this->cnWordList[$first][$word]) )
						$this->cnWordList[$first][$word] |= 0x02;
					else
						$this->cnWordList[$first][$word] = 0x02;
				}
			}				
			fclose($fd);
		}
	
		/*
   		 * Save Result to MemCache
		 */		 
		$cacheResult = array(
			'cn' => $this->cnWordList,
			'en' => $this->enWordList,
			'lastModified' => $this->lastModified,
		);
		JWMemcache::Set( $cacheKey, $cacheResult );
	}

	private function Find($word) {

		if ( empty($this->cnWordList) ) 
		{
			return false;
		}

		if ( isset($this->enWordList[strtolower($word)] ))
		{
			return true;
		}

		$first = substr($word, 0, 2);
		if( false == isset($this->cnWordList[$first]) )
		{
			return false;
		}

		if( false == isset($this->cnWordList[$first][$word]) )
		{
			return false;
		}
		return true;
	}

	public function GetFilterWords($string, $out="UTF-8")
	{
		//CheckLastModified;
		$this->CheckLastModified();

		//全角到半角
		$string = JWTextFormat::ConvertCorner( $string );
	
		//Get english word, number
		$filterWords = array();
		if( preg_match_all("/(\b\w+\b)/", $string, $matches))
		{
			$string = preg_replace("/(\b\w+\b)/", "", $string );
			foreach($matches[1] as $w){
				if( $this->find($w) )
					array_push($filterWords, $w);
			}
		}

		//滤除标点

		//转换为GB2312
		$string = mb_convert_encoding($string, "GB2312", "UTF-8,GB2312");
		$strlen = strlen($string);
		
		$word = null;
		$word_maybe = null;
		$prefix = null;
		$backtraceIndex = 0;
		for($i=0; $i<$strlen; $i++)
		{
			$char = $string[$i];
			$ord = ord($char);
			if($ord < 0x81 )
			{
				continue;
			}
			$i++;
			if( $i>=$strlen )
				break;
			$char .= $string[$i];

			if( $word == null )
			{
				$word = $char;
				if( false == isset($this->cnWordList[$word]) ) 
				{ // prefix word.
					$word = null;
					continue;
				}
				else
				{
					$prefix = $word;
					$backtraceIndex = $i;  // remark the backtrace point;
				}
			}
			else
			{
				$word .= $char;
				if( isset($this->cnWordList[$prefix][$word]))
				{ //find it
					if( $this->cnWordList[$prefix][$word] & 0x01 )
					{ //find word alone
						if ( $this->cnWordList[$prefix][$word] & 0x02 )
						{ //may next
							$word_maybe = $word;
							$backtraceIndex = $i;
						}
						else
						{
							array_push($filterWords, $word);
							$word = null;
						}
						continue;
					}
					else
					{ // not allon
						continue;
					}
				}
				else
				{
					if( $word_maybe )
					{
						array_push($filterWords, $word_maybe);
					}
					$i = $backtraceIndex;
					$word = null;
					$word_maybe = null;
					$prefix = null;
				}
			}
		}

		$filterWords = array_unique($filterWords);
		if( !empty( $filterWords ) ) 
		{
			$filterWordsString = implode("|", $filterWords);
			$filterWords = explode("|", mb_convert_encoding($filterWordsString, "UTF-8", "GB2312") );
		}
		return $filterWords;
	}
}
/** Test Case
	$a = new JWDictFilter();
	$a->load( "dict.txt" );
	var_dump($a->GetFilterWords("我是ｓeek,叽歪abc123开发人员工"));
*/
?>
