<?php
class JWEmote {
	const EMOTE_PATH = '/img/emote/';
	static function LoadEmote($theme) {
		$f = file_get_contents($theme);
		$info = array();
		$emote = array();/*
		if (preg_match_all('#^\s*(\w+)\s*=\s*(.+)#m', $f, $m, PREG_SET_ORDER)) {
			foreach ($m as $n) {
				$info[$n[1]] = $n[2];
			}
		}*/
		if (preg_match_all('#^([^.\s=]+\.[a-z]+)\s+([^\s].+[^\s])\s*$#m', $f, $m, PREG_SET_ORDER)) {
			foreach ($m as $n) {
				$emote[$n[1]] = preg_split('#[\s\t]+#', $n[2]);
			}
		}
		return $emote;
	}
	static function AddSlash_RegExp($s) {
		return preg_replace('#([\\/\\$\(\\)\\*\\+\\.\\?\\[\\{\\^\\|])#', '\\\$1', $s);
	}
	static function RenderJS($theme, $path) {
		$stmt = 'function emote(str) { return str';
		$emote = self::LoadEmote($path);
		foreach ($emote as $file => $em) {
			$repl = '<img alt="$2" src="'.JWTemplate::GetAssetUrl(self::EMOTE_PATH.basename($theme).'/'.$file).'" />';
			foreach ($em as $k=>$e) $em[$k] = htmlspecialchars($e);
			$patt = self::AddSlash_RegExp($em);
			$patt = implode('|', $patt);
			$stmt.= ".replace(/(>[^><]+)($patt)([^><]+<)/, '$1$repl$3')";
		}
		$stmt.= "; }\n";
		//$stmt.= 'if (window._auto_emote) $(window._auto_emote).innerHTML = emote($(window._auto_emote).innerHTML);';
		header('Content-Type: text/javascript');
		echo $stmt;
	}
}
?>
