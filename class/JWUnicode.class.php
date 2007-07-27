<?php

class JWUnicode {
    static private $blocks_p = array (
        '\x{0020}-\x{007E}', //Basic Latin, aka ASCII
        '\x{0081}-\x{024F}', //Latin: Latin-1 Supplement, Latin Extended-A, Latin Extended-B
        '\x{0400}-\x{04FF}', //Cyrillic 斯拉夫语
        '\x{0370}-\x{03FF}', //Greek and Coptic 希腊字母
        '\x{FF01}-\x{FF5E}', //Halfwidth and Fullwidth Forms 全角ASCII
        '\x{2000}-\x{206F}', //General Punctuation 通用半角标点
        '\x{3000}-\x{303F}', //CJK Symbols and Punctuation CJK 符号和标点
        '\x{4E00}-\x{9FBF}', //CJK Unified Ideographs CJK 统一表意符号
        '\x{3400}-\x{4DBF}', //CJK Unified Ideographs Extension A CJK 统一表意符号扩展 A
        //'\x{FE30}-\x{FE4F}', //CJK Compatibility Forms 无聊的标点，包括竖排书名号等
        '\x{3040}-\x{309F}', //Hiragana 日文平假名
        '\x{30A0}-\x{30FF}', //Katakana 日文片假名
        '\x{3100}-\x{312F}', //注音字母
        '\x{1100}-\x{11FF}', //朝鲜文
        '\x{AC00}-\x{D7AF}', //朝鲜文音节
    );
    static private $blocks_n = array (
        'A-Za-z0-9',
        '_', //用户名允许用的特殊字符添加在这里
        //'\x{FF10}-\x{FF19}\x{FF20}-\x{FF3A}\x{FF41}-\x{FF5A}', //全角0-9A-Za-z
        '\x{4E00}-\x{9FBF}', //CJK Unified Ideographs CJK 统一表意符号
        '\x{3400}-\x{4DBF}', //CJK Unified Ideographs Extension A CJK 统一表意符号扩展 A
        '\x{3040}-\x{309F}', //Hiragana 日文平假名
        '\x{30A0}-\x{30FF}', //日文片假名
        '\x{3100}-\x{312F}', //注音字母
        '\x{1100}-\x{11FF}', //朝鲜文
        '\x{AC00}-\x{D7AF}', //朝鲜文音节
    );
    static private function check($s, array $a) {
        return preg_match('/^['.implode('', $a).']+$/u', $s);
    }
    static private function filter($s, array $a) {
        return preg_replace('/[^'.implode('', $a).']/u', '', $s);
    }
    static private function unify(&$s, $a) {
        $r = true;
        $g = self::guessGB($s);
        $u = self::validateUTF8($s);
        if ((!$u && $g)||($g && !self::check($s, $a))) {
            $s = iconv('GBK', 'UTF-8//IGNORE', $s);
            $r = false;
        }
        $s = self::filter($s, $a);
        return $r;
    }
    static function guessGB($s) {
        return mb_convert_encoding(mb_convert_encoding($s, 'UTF-8', 'GBK'), 'GBK', 'UTF-8')==$s;
    }
    static function validateUTF8($s) {
        return mb_convert_encoding(mb_convert_encoding($s, 'UCS-4LE', 'UTF-8'), 'UTF-8', 'UCS-4LE')==$s;
    }
    static function unifyName(&$s) {
        return self::unify($s, self::$blocks_n);
    }
    static function unifyPhrase(&$s) {
        return self::unify($s, self::$blocks_p);
    }
}

?>
