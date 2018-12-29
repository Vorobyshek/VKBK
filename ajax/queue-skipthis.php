<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('../cfg.php');

$id = 0;
if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$p = intval($_GET['id']);
	if($p > 0){ $id = $p; }
	unset($p);
}
$oid = 0;
if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
	$p = intval($_GET['oid']);
	if($p > 0){ $oid = $p; }
	unset($p);
}

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

$types = array(
	'p',//'m','v','dc',						// Media types
	//'pr','gr',							// Profiles & Groups
	'atph','atvi','atli','atau','atdc',		// Attachments
	'matph','matvi','matli','matdc','matst'	// Dialogs attachments
);

if(isset($_GET['t']) && in_array($_GET['t'],$types) && isset($_GET['id']) && isset($_GET['oid'])){
	$t = $_GET['t'];
	
	if($t == 'p'){
		$db->query("UPDATE vk_photos SET `skipthis` = 1 WHERE `id` = ".$id." ");
	}
	
	if($t == 'atph' || $t == 'atvi' || $t == 'atdc' || $t == 'atli'){
		$db->query("UPDATE vk_attach SET `skipthis` = 1 WHERE `attach_id` = ".$id." AND `owner_id` = ".$oid." ");
	}
	
	if($t == 'matph' || $t == 'matvi' || $t == 'matdc'){
		$db->query("UPDATE vk_messages_attach SET `skipthis` = 1 WHERE `attach_id` = ".$id." AND `owner_id` = ".$oid." ");
	}
	if($t == 'matli'){
		$db->query("UPDATE vk_messages_attach SET `skipthis` = 1 WHERE `attach_id` = ".$id." AND `date` = ".$oid." AND `type` = 'link' ");
	}
	if($t == 'matst'){
		$db->query("UPDATE vk_messages_attach SET `skipthis` = 1 WHERE `date` = ".$id." AND `type` = 'sticker' ");
	}
} else {
	print 'Неопознанный параметр';
}

$db->close($res);

?>