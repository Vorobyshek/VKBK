<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('../cfg.php');

$id = 0;
if(isset($_POST['id']) && is_numeric($_POST['id'])){
	$p = intval($_POST['id']);
	if($p > 0){ $id = $p; }
	unset($p);
}

$response = array(
	'error' => 0,
	'success' => 0,
	'msg' => ''
);

if($id < 1 || !isset($_POST['artist']) || !isset($_POST['title']) || !isset($_POST['duration']) || !isset($_POST['uri']) || empty($_POST['artist']) || empty($_POST['title']) || empty($_POST['uri'])){
	$response['error'] = 1;
	$response['msg'] = 'Некорректные данные';
} else {

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Put new track to queue
$music_data = array(
	'artist' => ($_POST['artist'] == '' ? '- Unknown -' : $_POST['artist']),
	'title' => ($_POST['title'] == '' ? '- Unknown -' : $_POST['title']),
	'album_id' => 0,
	'duration' => (!is_numeric($_POST['duration']) ? 0 : $_POST['duration']),
	'date' => time(),
	'uri' => $_POST['uri']
);

$q = $db->query("INSERT INTO vk_music (`id`,`artist`,`title`,`album`,`duration`,`uri`,`date_added`,`date_done`,`saved`,`deleted`,`path`,`hash`,`in_queue`) VALUES ({$id},'".$db->real_escape($music_data['artist'])."','".$db->real_escape($music_data['title'])."',0,{$music_data['duration']},'{$music_data['uri']}',{$music_data['date']},0,0,0,'','',true)");

if($q){
	$response['success'] = 1;
	$response['msg'] = 'Добавлено';
} else {
	$response['error'] = 1;
	$response['msg'] = 'Ошибка';
}

$db->close($res);

}

print json_encode($response);

?>