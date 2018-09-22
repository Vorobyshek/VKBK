<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check do we have all needed GET data
$id = 0;
if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$v = intval($_GET['id']);
	if($v > 0){ $id = $v; }
	unset($v);
}
$oid = 0;
if(isset($_GET['oid']) && is_numeric($_GET['oid'])){
	$oid = intval($_GET['oid']);
}

require_once('../cfg.php');

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get Functions
require_once(ROOT.'classes/func.php');
$f = new func();

print <<<E
<div class="container video-details-bg" id="video-details-bg" onclick="javascript:hide_details();"></div>
<div class="col-sm-12 col-md-4 col-md-offset-8 video-details" id="video-details">
<i class="fas fa-times details-close" onclick="javascript:hide_details();"></i>
E;

$play = $db->query_row("SELECT val as local FROM vk_status WHERE `key` = 'play-local-video'");

mb_internal_encoding("UTF-8");
$row = $db->query_row("SELECT * FROM vk_videos WHERE `id` = {$id} AND `owner_id` = {$oid}");

	// Rewrite if you plan to store content outside of web directory and will call it by Alias
	if($cfg['vhost_alias'] == true && substr($row['preview_path'],0,4) != 'http'){
		$row['preview_path'] = $f->windows_path_alias($row['preview_path'],'video');
	}

	$row['duration'] = $skin->seconds2human($row['duration']);
print <<<E
<div class="col-sm-12">
<div class="white-box">
	<div class="video-preview" style="background-image:url('{$row['preview_path']}');margin:0;">
		<span class="label">{$row['duration']}</span>
	</div>
	</div>
	<div class="video-info">
		<div>{$row['title']}</div>
		<div style="margin-top:5px;padding-top:5px;border-top:1px solid #eee;">
E;

	// Show icon for known services
	$service = false;

	// Youtube
	if(strstr($row['player_uri'],'youtube.com') || strstr($row['player_uri'],'youtu.be')){
		$service = true;
		print $skin->details_row('Источник:','<i class="fab fa-youtube" style="color:red;"></i>');
		if($row['local_path'] != ''){
			print $skin->details_row('Локальная копия:','<b style="color:#4caf50">есть</b>');
			print $skin->details_row('Формат:',strtoupper($row['local_format']));
			print $skin->details_row('Разрешение:',$row['local_w'].'x'.$row['local_h']);
			print $skin->details_row('Размер:',$f->human_filesize($row['local_size']));
		} else {
			preg_match("/embed\/([^\?]+)\?/",$row['player_uri'],$pu);
			$key = $pu[1];
			print $skin->details_row('Локальная копия:','<b>нет</b>');
			print $skin->details_row('&nbsp;','<a href="ytget.php?id='.$row['id'].'&key='.$key.'&s=yt" target="_blank">скачать?</a>');
		}
	}
	// Vkontakte
	if(strstr($row['player_uri'],'vk.com')) {
		$service = true;
		print $skin->details_row('Источник:','<i class="fab fa-vk" style="color:#517397;"></i>');
		if($row['local_path'] != ''){
			print $skin->details_row('Локальная копия:','<b style="color:#4caf50">есть</b>');
			print $skin->details_row('Формат:',strtoupper($row['local_format']));
			print $skin->details_row('Разрешение:',$row['local_h'].'p');
			print $skin->details_row('Размер:',$f->human_filesize($row['local_size']));
		} else {
			preg_match("/oid\=([\-0-9]+)\&id\=([\-0-9]+)/",$row['player_uri'],$pu);
			$key = $pu[1].'_'.$pu[2];
			print $skin->details_row('Локальная копия:','<b>нет</b>');
			print $skin->details_row('&nbsp;','<a href="ytget.php?id='.$row['id'].'&key='.$key.'&s=vk" target="_blank">скачать?</a>');
		}
	}
	
	if($service == false){
		print $skin->details_row('Источник:','<i class="fas fa-film"></i>');
	}

print <<<E
		</div>
	</div></div>
</div>
E;

$db->close($res);

?>