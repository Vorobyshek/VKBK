<?php
// VK mParser by @in4in-dev
// Source url: https://gist.github.com/in4in-dev/09f32f313f11b2c10778d9e2ffe7e60e
//(js -> php) code. letter by letter

global $n, $i, $id, $cfg;
$n = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMN0PQRSTUVWXYZO123456789+/=";
$id = intval($cfg['vk_user']); //YOUR USER ID

$i = [
	'v' => function($e) {
		return strrev($e);
	},
	'r' => function($e, $t){
		global $n;
		$e = str_split($e);
		for ($o = $n . $n, $s = count($e); $s--;){
			$i = stripos($o, $e[$s]);
			if(~$i){
				$e[$s] = substr($o, $i - $t, 1);
			}
		}
		return implode("", $e);
	},
	's' => function($e, $t) {
		$n = strlen($e);
		if ($n) {
			$i = r($e, $t);
			$o = 0;
			$e = str_split($e);
			for (; ++$o < $n;){
				$p = array_splice($e, $i[$n - 1 - $o], 1, $e[$o]);
				$e[$o] = $p[0];
			}

			$e = implode("", $e);
		}

		return $e;
	},
	'i' => function($e, $t){
		global $i, $id;
		$k = $i['s'];
		return $k($e, $t ^ $id);
	},
];

function o() {
	return false;
}

function a($e){
	global $n;
	if (!$e || strlen($e) % 4 == 1) {
		return !1;
	}
	$s = 0;
	for ($o = 0, $a = "";$s < strlen($e);) {
		$i = $e[$s++];
		$i = strpos($n, $i);
		if ($i !== false) {
			$t = ($o % 4) ? 64 * $t + $i : $i;
			if ($o++ % 4) {
				$a .= chr(255 & $t >> (-2 * $o & 6));
			}
		}
	}

	return $a;
}

function r($e, $t) {
	$n = strlen($e);
	$i = [];
	if ($n) {
		$o = $n;
		$t = abs($t);
		for (; $o--;){
			$t = ($n * ($o + 1) ^ $t + $o) % $n;
			$i[$o] = $t;
		}
	}
	return $i;
}

function s($e){
	global $i;
	if (!o() && strpos($e, "audio_api_unavailable") !== false) {
		$t = explode("?extra=", $e);
		$t = $t[1];
		$t = explode("#", $t);
		$n = ("" === $t[1]) ? "" : a($t[1]);
		$t = a($t[0]);
		if (!is_string($n) || !$t){ return $e;}
		$n = $n ? explode(chr(9), $n) : [];
		for ($l = count($n); $l--;) {
			$r = explode(chr(11), $n[$l]);
			$s = array_splice($r, 0, 1, $t);
			$s = $s[0];
			if (!$i[$s]){ return $e; }
			$t = $i[$s](...$r);
		}
		if ($t && "http" === substr($t, 0, 4)){ return $t;}
	}
	return $e;
}


//For example
//EASY <><<><><> 
//$extra = s("https://m.vk.com/mp3/audio_api_unavailable.mp3?extra=encodevaluefromvk"); //Encode extra url -> Good extra url
//Or see -> test.php        