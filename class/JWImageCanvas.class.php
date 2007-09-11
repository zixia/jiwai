<?php

mb_internal_encoding('UTF-8');

/*
 * This class is ugly and buggy, lack lots of checks
 */
class JWImageCanvas {
	//public $transparentColor = 'ee11ee';
	public $image;
	public $width;
	public $height;
	protected $textFontName;
	protected $textFontSize;
	protected $textFontHeight;
	protected $textFontYOffset;
	protected $textAreaLeft;
	protected $textAreaTop;
	protected $textAreaWidth;
	protected $textCursorLeft;
	protected $textCursorTop;
	protected $textLineSpacing;
	public function __construct($w=32000, $h=32000, $bg=false) {
	    if (is_string($w)) {
	        $this->loadFromFile($w);
	    } else {
    		$this->width = $w;
    		$this->height = $h;
    		$this->image = imagecreatetruecolor($this->width, $this->height);
    		if ($bg!==false) $this->clear($bg);
    	}
		$this->setFont();
		$this->setTextArea();
	}
	public function border($color=null) {
		imagerectangle($this->image, 0, 0, $this->width-1, $this->height-1, $this->color($color));
	}
	public function rect($l, $t, $w, $h, $c){
		imagerectangle($this->image, $l, $t, $l+$w-1, $t+$h-1, $this->color($c));
	}
	public function gradient($f_c,$s_c){
		sscanf($f_c, "%2x%2x%2x", $red, $green, $blue);
		$f_c = array($red,$green,$blue);
		sscanf($s_c, "%2x%2x%2x", $red, $green, $blue);
		$s_c = array($red,$green,$blue);
		if($f_c[0]>$s_c[0]) $r_range=$f_c[0]-$s_c[0]; else $r_range=$s_c[0]-$f_c[0];
		if($f_c[1]>$s_c[1]) $g_range=$f_c[1]-$s_c[1]; else $g_range=$s_c[1]-$f_c[1];
		if($f_c[2]>$s_c[2]) $b_range=$f_c[2]-$s_c[2]; else $b_range=$s_c[2]-$f_c[2];
		$r_px=$r_range/$this->height;
		$g_px=$g_range/$this->height;
		$b_px=$b_range/$this->height;
		$r=$f_c[0];
		$g=$f_c[1];
		$b=$f_c[2];
		for($i=0;$i<=$this->height;$i++){
			$col=imagecolorallocate($this->image,round($r),round($g),round($b));
			imageline($this->image,0,$i,$this->width-1,$i,$col);
			if($f_c[0]<$s_c[0]) $r+=$r_px; else $r-=$r_px;
			if($f_c[1]<$s_c[1]) $g+=$g_px; else $g-=$g_px;
			if($f_c[2]<$s_c[2]) $b+=$b_px; else $b-=$b_px;
   		}
	}
	public function clear($color=null) {
		imagefilledrectangle($this->image, 0, 0, $this->width-1, $this->height-1, $this->color($color));
	}
	public function setTextArea($left=0, $top=0, $width=0) {
		if ($width==0) $width = $this->width - $left;
		$this->textAreaLeft = $left;
		$this->textAreaTop = $top;
		$this->textAreaWidth = $width;
	}
	public function getWrittenArea() {
		return array($this->textCursorTop ? $this->textAreaWidth : $this->textCursorLeft, $this->textCursorTop+$this->textFontHeight);
	}
	public function getFontHeight() {
		return $this->textFontHeight;
	}
	public function setFont($size=10, $file='../fonts/wqy-bsong.ttf', $lineSpacing=2) { //
		$this->textFontName = $file;
		$this->textFontSize = $size;
		$b = self::box($this->textFontName, $this->textFontSize, '我');
		$this->textFontHeight = $b['height'];
		$this->textFontYOffset = $b['yOffset'];
		$this->textLineSpacing = $lineSpacing;
	}
	public function newLine() {
		$this->textCursorLeft = 0;
		$this->textCursorTop += $this->textFontHeight+$this->textLineSpacing;
	}
	public function moveCursor($x=0, $y=0) {
		$this->textCursorLeft += $x;
		$this->textCursorTop += $y;
	}
	public function color($input = '000000', $default = '000000') {
		if ($input==='' || $input===null) return imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 222, 2, 222, 0));
		$hex = (eregi('^[0-9a-f]{6}$', $input)) ? $input : $default;
		$c = array( 'r' => hexdec(substr($hex, 0, 2)), // 1st pair of digits
			'g' => hexdec(substr($hex, 2, 2)), // 2nd pair
			'b' => hexdec(substr($hex, 4, 2))  // 3rd pair
		);
		return imagecolorallocate($this->image, $c['r'], $c['g'], $c['b']);
	}
	public function outputGIF() {
		header('Content-type: image/gif');
		imagegif($this->image);
	}
	public function outputPNG() {
		header('Content-type: image/png');
		imagepng($this->image);
	}
	public function text($text, $color='ffffff') {
		$t = $this->splitText($text);
		$c = $this->color($color);
		$b = imagefttext($this->image, $this->textFontSize, 0, 
			$this->textCursorLeft + $this->textAreaLeft, $this->textCursorTop + $this->textAreaTop + $this->textFontYOffset, 
			$c, $this->textFontName, array_shift($t), array());
		foreach ($t as $i) {
			$this->newLine();
			$b = imagefttext($this->image, $this->textFontSize, 0, 
				$this->textCursorLeft + $this->textAreaLeft, $this->textCursorTop + $this->textAreaTop + $this->textFontYOffset, 
				$c, $this->textFontName, array_shift($t), array());
		}
		$this->textCursorLeft += $b[0]<-1 ? abs($b[2])+abs($b[0])-1 : abs($b[2]-$b[0])+1;
	}
    public function putImage($im, $x=0, $y=0, $w=0, $h=0) {
        //imagecopy($this->image, $im->image, $x, $y, 0, 0, ($w ? $w : $im->width), ($h ? $h : $im->height));
        imagecopymerge($this->image, $im->image, $x, $y, 0, 0, ($w ? $w : $im->width), ($h ? $h : $im->height),100);
    }
    const TILE_REPEATX = 1;
    const TILE_REPEATY = 2;
    public function tileImage($im, $mode=3) {
        $y = 0;
        while ($y<$this->height) {
            $x = 0;
            while ($x<$this->width) {
                $this->putImage($im, $x, $y);
                if ($mode & self::TILE_REPEATX) $x += $im->width; else break;
            }
            if ($mode & self::TILE_REPEATY) $y += $im->height; else break;
        }
    }
	
	protected function loadFromFile($f) {
	    $this->image = imagecreatefromstring(file_get_contents($f)); //FIXME: mem costy!
	    $this->width = imagesx($this->image);
	    $this->height = imagesy($this->image);
	}
	protected static function box($font, $size, $text) {
		$bbox = imageftbbox($size, 0, $font, $text, array());
		if ($bbox[0] >= -1)
			$xOffset = -abs($bbox[0] + 1);
		else
			$xOffset = abs($bbox[0] + 2);
		$width = abs($bbox[2] - $bbox[0]);
		if ($bbox[0] < -1) $width = abs($bbox[2]) + abs($bbox[0]) - 1;
		$yOffset = abs($bbox[5] + 1);
		if ($bbox[5] >= -1) $yOffset = -$yOffset; // Fixed characters below the baseline.
		$height = abs($bbox[7]) - abs($bbox[1]);
		if ($bbox[3] > 0) $height = abs($bbox[7] - $bbox[1]) - 1;
		return array(
			'width' => $width,
			'height' => $height,
			'xOffset' => $xOffset, // Using xCoord + xOffset with imagettftext puts the left most pixel of the text at xCoord.
			'yOffset' => $yOffset, // Using yCoord + yOffset with imagettftext puts the top most pixel of the text at yCoord.
			'belowBasepoint' => max(0, $bbox[1])
		);
	}
	protected function getTextWidth($s) {
		$b = self::box($this->textFontName, $this->textFontSize, $s);
		return $b['width'];
	}
	/*
	const postfix = ',.’”…，．：；？、。';
	const prefix = '《‘“';
	*/
	const postfix = '!%),.:;>?]}¢¨°·ˇˉ―‖’”…‰′″›℃∶、。〃〉》」』】〕〗〞︶︺︾﹀﹄﹚﹜﹞！＂％＇），．：；？］｀｜｝～￠';
	const prefix = '$([{£¥·‘“〈《「『【〔〖〝﹙﹛﹝＄（．［｛￡￥';
	protected function splitText($text) {
		$r = array();
		$s = $text;
		$w = $this->getTextWidth($s);
		//echo $w.' '.$this->textCursorLeft.' ';
		$l = 0;
		while ($w+($l ? 0 : $this->textCursorLeft)>$this->textAreaWidth) {
			$s1 = mb_strimwidth($s, 0, mb_strwidth($s)-round(mb_strwidth($s)*$this->textAreaWidth/($this->textAreaWidth-($l ? 0 : $this->textCursorLeft)-$w)));
			while ($this->getTextWidth($s1)+($l ? 0 : $this->textCursorLeft)>$this->textAreaWidth) $s1 = mb_substr($s1, 0, -1);
			$i = 0;
			$s = substr($s, strlen($s1));
			$last = mb_substr($s1, -1, 1);
			$first = mb_substr($s, 0, 1);
			while ($i<5 && $first && $last && (mb_strpos(self::prefix, $last)!=false || mb_strpos(self::postfix, $first)!=false)) {
			    $s1 = mb_substr($s1, 0, -1);
			    $s = $last.$s;
			    $first = $last;
			    $last = mb_substr($s1, -1, 1);
				$i++;
				//echo $first;
			}
			while ($first==' '||$first=='　') {
				$s = mb_substr($s, 1);
				$first = mb_substr($s, 0, 1); 
			}
			//echo mb_strpos(self::postfix, $first).$first;	die();
			$r[] = $s1;
			$w = $this->getTextWidth($s);
			$l++;
		}
		$r[] = $s;
		//var_dump($r);
		return $r;
	}
}

?>
