<?php
if(false==defined('PAGE_FIRST')) define('PAGE_FIRST','&laquo;&laquo;');
if(false==defined('PAGE_LAST')) define('PAGE_LAST','&raquo;&raquo;');
if(false==defined('PAGE_PRE')) define('PAGE_PRE','前页');
if(false==defined('PAGE_NEXT')) define('PAGE_NEXT','后页');

class JWPager{

	public $rowCount = 0;
	public $pageNo = 1;
	public $pageSize = 20;
	public $pageCount = 0;
	public $offset = 0;
	public $pageString = 'page';

	private $script = null;
	private $valueArray = array();
	private $pageLimit = 10;
	
	public function __construct( $options=array() )
	{
		$this->defaultQuery();
		$this->valueArray = isset($options['valueArray']) ? $options['valueArray'] : $this->valueArray;
		$this->pageLimit = isset($options['pageLimit']) ? intval($options['pageLimit']) : $this->pageLimit;
		$this->pageString = isset($options['pageString']) ? $options['pageString'] : $this->pageString;
		$this->pageSize = isset($options['pageSize']) ? intval($options['pageSize']) : $this->pageSize;
		$this->rowCount = isset($options['rowCount']) ? intval($options['rowCount']) : $this->rowCount;

		$this->pageCount = ceil($this->rowCount/$this->pageSize);
		$this->pageCount = ($this->pageCount<=0)?1:$this->pageCount;
		$this->pageNo = isset($options['pageNo'])
			?  intval($options['pageNo'])
			: intval(@$_GET[$this->pageString]);
		$this->pageNo = $this->pageNo<=0 ? 1 : $this->pageNo;
		$this->pageNo =	$this->pageNo > $this->pageCount ? $this->pageCount : $this->pageNo;
		$this->offset = ( $this->pageNo - 1 ) * $this->pageSize;
	}

	private function genURL( $url="/index.php", $param, $value ){
		if( false === strpos($url,'?')) return $url.'?'.urlEncode($param).'='.urlEncode($value);
		else return $url.'&'.urlEncode($param).'='.urlEncode($value);
	}

	private function defaultQuery()
	{
		$script_uri = @$_SERVER['SCRIPT_URI'];
		$q_pos = strpos($script_uri,'?');
		if ( $q_pos > 0 )
		{
			$qstring = substr($script_uri, $q_pos+1);
			parse_str($qstring, $valueArray);
			$script = substr($script_uri,0,$q_pos);
		}
		else
		{
			$script = $script_uri;
			$valueArray = array();
		}
		$this->valueArray = empty($valueArray) 
			? array()
			: $valueArray;
		$this->script = $script;
	}

	private function genHref($url, $value)
	{
		return $this->genURL($url, $this->pageString, $value);
	}

	public function paginate($url=null){
		$from = $this->pageSize*($this->pageNo-1)+1;
		$from = ($from>$this->rowCount) ? $this->rowCount : $from;
		$to = $this->pageNo * $this->pageSize;
		$to = ($to>$this->rowCount) ? $this->rowCount : $to;
		$size = $this->pageSize;
		$no = $this->pageNo;
		$max = $this->pageCount;
		$total = $this->rowCount;
		$html = $this->genHref($url);

		return array(
			'from' => $from,
			'to' => $to,
			'size' => $size,
			'no' => $no,
			'max' => $max,
			'total' => $total,
			'html' => $html,
		);
	}

	public function RangeArray($no=1, $count=1) 
	{
		if ( $no < 7 ) {
			if ( $no > 4 ) {
				$range = range(1, ($no+2));
			} else {
				$range = range(1, 6);
			}
			$range = array_merge($range, range($count-1, $count));

		} else if ( ($count-$no) < 6 ) {
			$range = array(1, 2);
			if ( ($count-$no) > 3 ) {
				$range = array_merge( $range, range( $no-2, $count));
			} else {
				$range = array_merge( $range, range( $count-5, $count));
			}
		} else {
			$range = array(1, 2);
			$range = array_merge( $range, range( $no-2, $no+2));
			$range = array_merge( $range, range( $count-1, $count));
		}
		if ( $count <= 12 ) $range = range(1, $count);

		$range = array_unique($range);
		sort( $range );
		return $range;
	}

	public function genYuan()
	{
		$url = $this->script;
		foreach($this->valueArray as $key=>$value) {
			if ( $key != $this->pageString ) {
				$url = $this->genURL($url,$key,$value);
			}
		}
		$range = self::RangeArray($this->pageNo, $this->pageCount);
		$html = null;
		
		//display PRE_PAGE LINK
		if ( $this->pageNo > 1 ) {
			$html .= "<a href=\"".$this->genHref($url,$this->pageNo-1)."\">".PAGE_PRE."</a>\n";
		} else {
			$html .= "<a href=\"javascript:void(0);\" class=\"un\">".PAGE_PRE."</a>\n";
		}

		$last = 0;
		foreach( $range AS $one ) {
			if ( $one-$last-1 ) {
				$html .= "<span>...</span>\n";
			}
			$last = $one;
			if ( $one == $this->pageNo ) {
				$html .= "<a href=\"javascript:void(0);\" class=\"now\">".$one."</a>\n";
			} else {
				$html .= "<a href=\"".$this->genHref($url,$one)."\">".$one."</a>\n";
			}
		}

		//display NEXT page LINK
		if ( $this->pageCount == $this->pageNo ) {
			$html .= "<a href=\"javascript:void(0);\" class=\"un\">".PAGE_NEXT."</a>\n";
		} else {
				$html .= "<a href=\"".$this->genHref($url,$this->pageNo+1)."\">".PAGE_NEXT."</a>\n";
		}

		return $html;
	}
}
?>
