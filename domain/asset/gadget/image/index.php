<?php
require_once 'data.php';

$source = isset($_GET['source']) ? (int)$_GET['source'] : 0;

$count_def = 5;
$count_min = 1;
$count_max = 10;
$count = isset($_GET['count']) ? (int)$_GET['count'] : $count_def;
if ($count<$count_min) $count = $count_min;
if ($count>$count_max) $count = $count_max;

$modes = array('line', 'bar', 'block');
$mode = isset($_GET['mode']) ? $_GET['mode'] : 0;
$mode = isset($modes[$mode]) ? $mode : 0;

$width_def = array(320, 400, 180);
$width_min = array(100, 300, 160);
$width_max = array(1600, 1600, 1600);
$width = isset($_GET['width']) ? (int)$_GET['width'] : $width_def[$mode];
if ($width<$width_min[$mode]) $width = $width_min[$mode];
if ($width>$width_max[$mode]) $width = $width_max[$mode];

$height_def = array(18, 400, 180);
$height_min = array(16, 300, 160);
$height_max = array(24, 600, 240);
$height = isset($_GET['height']) ? (int)$_GET['height'] : $height_def[$mode];
if ($height<$height_min[$mode]) $height = $height_min[$mode];
if ($height>$height_max[$mode]) $height = $height_max[$mode];

$colors_def = array('FFF3DA', 'aaaaaa', '515151', '929292', '', '', '000000'); //bg border body from via time xxx
for($i=0;$i<7;$i++) {
	$v = 'color'.$i;
	$$v = isset($_GET[$v]) ? color_check($_GET[$v]) : $colors_def[$i];
}
//$color1 = $color0;
if (!$color3) $color3 = $color2;
if (!$color4) $color4 = $color3;
if (!$color5) $color5 = $color4;

$margin_def = 6;
$margin_min = 0;
$margin_max = 20;
$margin = isset($_GET['margin']) ? (int)$_GET['margin'] : $margin_def;
if ($margin<$margin_min) $margin = $margin_min;
if ($margin>$margin_max) $margin = $margin_max;

$padding_def = 6;
$padding_min = 0;
$padding_max = 20;
$padding = isset($_GET['padding']) ? (int)$_GET['padding'] : $padding_def;
if ($padding<$padding_min) $padding = $padding_min;
if ($padding>$padding_max) $padding = $padding_max;

$bgimage = isset($_GET['bgimage']) ? $_GET['bgimage'] : '';

////////////////////////////////////////////////////////////////////////////
$font_size = 10;
$icon_size = 48;
$data = $mode==1 ? getOwnTimeline(1) : ($source ? getFriendsTimeline($count) : getOwnTimeline($count));
$color00 = $color0;
if ($bgimage) {
	$color00 = null;
	$bg = new JWImageCanvas($bgimage);
	$bgmode = JWImageCanvas::TILE_REPEATX; // | JWImageCanvas::TILE_REPEATY
}

function color_check($s, $default = '000000') {
	return $s=='' ? null : ((eregi('^[0-9a-f]{6}$', $s)) ? $s : $default);
}

//error_reporting(E_ALL ^ E_NOTICE);

switch($mode) {
	case 0:
		$v = new JWImageCanvas(strlen(serialize($data))*10, 20, $color00); //guess the width
		$v->setFont($font_size);
		foreach ($data as $d) {
			if ($source) $v->text($d['from'].': ', $color3);
			$v->text($d['body'].' ', $color2);
			$v->text($d['time'].' ', $color5);
			$v->text($d['via'].'   ', $color4);
		}
		$a = $v->getWrittenArea();
		$w = $a[0] + $width - $font_size;
		$h = $a[1];
		$y = ($height - $h) / 2;
		$x = $width - $font_size;
		//$v->outputGIF(); die();
		$gif = new JWGIFAnimation();
		$m = new JWImageCanvas($width, $height);
		while ($x + $w > $font_size + $width) {
			if ($bgimage) $m->tileImage($bg, $bgmode); else //CPU costy
			$m->clear($color0);
			//imagerectangle($m->image, 0, 0, $width-1, $height-1, $m->color($color1));
			$cw = $w;
			if ($x<1) {
				$dx = 1;
				$sx = 1-$x;
				$cw -= 1-$x;
			} else {
				$dx = $x;
				$sx = 0;
			}
			if ($dx+$cw>=$width-1) $cw = $width - 1 - $dx;
			imagecopymerge($m->image, $v->image, $dx, $y, $sx, 0, $cw, $h, 100);
			$gif->addFrame($m->image);
			$x -= $font_size;
		}
		//echo count($frames); die();
		header('Content-type: image/gif');
		//imagegif($im2); die();
		echo $gif->finish();
		break;
	case 1:
		$d = $data[0];
		$w = $width - $margin - $icon_size - $padding - $margin;
		$v = new JWImageCanvas($w, round(strlen(serialize($data))*8/($w-$icon_size)*40), $color00);
		$v->text($d['body'].' ', $color2);
		$v->text($d['time'].' ', $color5);
		$v->text($d['via'], $color4);
		$a = $v->getWrittenArea();
		$h = $a[1];
		$m = new JWImageCanvas($width, max($h+$padding+16, $icon_size)+$margin+$margin, $color0);
		if ($bgimage) $m->tileImage($bg, $bgmode);
		$m->setTextArea($margin+$icon_size+$padding, $margin);
		$m->setFont(12);
		//$m->text($d['from'].' ', $color2);
		//$m->moveCursor(0, 2);
		$m->setFont(9);
		$m->text($url, $color2);
		$m->putImage($v, $margin+$icon_size+$padding, $margin+16+$padding, $w, $h);
		$m->putImage(new JWImageCanvas($d['icon']), $margin, $margin);
		if ($color2) $m->border($color2);
		$m->outputPNG();
		break;
	case 2:
		if ($source) {
			$w = $width - $margin - $margin;
			$v = new JWImageCanvas($w, round(strlen(serialize($data))*8/($w-$icon_size)*40), $color00); //guess the height
			$v->setFont($font_size);
			$v->setTextArea($icon_size+$padding, 0);
			$lines = 0;
			$h = -999;
			foreach ($data as $d) {
				if ($lines++) {
				    $v->newLine();
				    $v->moveCursor(0, $padding);
				}
				$a = $v->getWrittenArea();
				$last = $h;
				$h = $a[1] - $v->getFontHeight();
				if (($h-$last) < $icon_size + $padding) {
					$v->moveCursor(0, $icon_size + $padding - ($h-$last));
					$a = $v->getWrittenArea();
					$h = $a[1] - $v->getFontHeight();
				}
				$a = $v->getWrittenArea();
				$v->putImage(new JWImageCanvas($d['icon']), 0, $a[1] - $v->getFontHeight());
				$v->text($d['from'].': ', $color3);
				$v->text($d['body'].' ', $color2);
				$v->text($d['time'].' ', $color5);
				$v->text($d['via'], $color4);
			}
			$a = $v->getWrittenArea();
			$last = $h;
			$h = $a[1] - $v->getFontHeight();
			if (($h-$last) < $icon_size + $padding) {
				$v->moveCursor(0, $icon_size + $padding - ($h-$last));
				$a = $v->getWrittenArea();
				$h = $a[1] - $v->getFontHeight();
			}
			//$v->outputGIF(); die();
			$m = new JWImageCanvas($width, $h+$margin+$margin, $color0);
			if ($bgimage) $m->tileImage($bg, $bgmode);
			$m->putImage($v, $margin, $margin);
			if ($color2) $m->border($color2);
			$m->outputPNG();
		} else {
			$w = $width - $margin - $margin;
			$v = new JWImageCanvas($w, round(strlen(serialize($data))*8/$w*40), $color00); //guess the height
			$v->setFont($font_size);
			$lines = 0;
			foreach ($data as $d) {
				if ($lines++) {
				    $v->newLine();
				    $v->moveCursor(0, $padding);
				}
				$v->text($d['body'].' ', $color2);
				$v->text($d['time'].' ', $color5);
				$v->text($d['via'], $color4);
			}
			$a = $v->getWrittenArea();
			$h = $a[1];
			//$v->outputGIF(); die();
			$m = new JWImageCanvas($width, $margin+$h+$padding+$icon_size+$margin, $color0);
			if ($bgimage) $m->tileImage($bg, $bgmode);
			$m->putImage(new JWImageCanvas($d['icon']), $margin, $margin);
			$m->setTextArea($margin+$icon_size+4, $margin);
			$m->setFont(12);
			$m->text($d['from'], $color2);
			$m->newLine();
			$m->setFont(9);
			$m->text($url, $color2);
			$m->putImage($v, $margin, $margin+$icon_size+4, $w, $h);
			if ($color2) $m->border($color2);
			$m->outputPNG();
		}
		break;
}

?>