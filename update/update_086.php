<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

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

$ex_top = <<<E
<style type="text/css">
body {padding-top:10px;margin-bottom:10px;}
.wall-box{margin-bottom:0;}
</style>
E;

print $skin->header(array('extend'=>$ex_top));

print <<<E
<div class="wall-body">
    <div class="container">
E;

$found = array('total'=>0,'list'=>array(),'ids'=>'');
$q = $db->query("SELECT * FROM `vk_messages_attach` WHERE `type` != 'sticker' AND `uri` != '' AND `path` != '' AND `skipthis` = 0");
while($row = $db->return_row($q)){
	preg_match_all("/\/([^\/]+)\.[a-z]{3,4}$/",$row['uri'],$a);
	preg_match_all("/\/([^\/]+)\.[a-z]{3,4}$/",$row['path'],$b);
	if($row['type'] == 'doc'){
		$a[1][0] = $row['attach_id'];
	}
	
	if($a[1][0] != $b[1][0]){
		$found['total']++;
		$found['list'][] = array('uid'=>$row['uid'],'uri'=>$row['uri'],'path'=>$row['path']);
		$found['ids'] .= ($found['ids'] != '' ? ',' : '').$row['uid'];
	}
}

$need_db = '2018122301';
$current_db = ($need_db == $cfg['version_db']) ? true : false;

print <<<E
<div class="row">
    <div class="col-sm-12 wall-box">
		<ul class="list-group list-unstyled">
			<div>Скрипт проверки коллизий у вложений в диалогах для версии <b>0.8.6</b></div>
			<li><label><span class="badge badge-info">информация</span></label><p>Ваша версия VKBK: <b>{$cfg['version']}</b></p></li>
		</ul>
	
		<ul class="list-group list-unstyled">
			<div>База данных</div>
E;
if($current_db === true){
	print '<li><label><span class="badge badge-success">порядок</span></label><p>Правильная версия базы данных: <b>'.$cfg['version_db'].'</b></p></li>';
} else {
	print '<li><label><span class="badge badge-danger">ошибка</span></label><p>Версия базы данных отличается от необходимой!</br>Ваша версия: <b>'.$cfg['version_db'].'</b></br>Требуемая версия: <b>'.$need_db.'</b></p></li>';
}
print <<<E
		</ul>
		<ul class="list-group list-unstyled">
			<div>Данные</div>
			<li><label><span class="badge badge-info">информация</span></label><p>Найдено <b>{$found['total']}</b> записей требующих обновновления.</br>Если найдено 0 записей, обновление не требуется.</p></li>
		</ul>
E;

if(!isset($_GET['update']) && $found['total'] > 0){
print <<<E
		<hr/>
		<div style="text-align:center;">
			<a href="update/update_086.php?update=1" class="btn btn-success">Обновить</a>
		</div>
E;

	if(!empty($found['list'])){
print <<<E
<hr/>Данные для проверки:
<table class="table table-sm table-bordered table-responsive m-0">
<tr><th>UID</th><th>URI</th><th>PRE</th><th>PATH</th><th>PRE</th></tr>
E;
		foreach($found['list'] as $fk => $fv){
			if($cfg['vhost_alias'] == true && substr($fv['path'],0,4) != 'http'){
				$fv['path'] = $f->windows_path_alias($fv['path'],'photo');
			}
			print '<tr><td><b>'.$fv['uid'].'</b></td><td>'.$fv['uri'].'</td><td><img src="'.$fv['uri'].'" style="max-width:75px;max-height:75px;" /></td><td>'.$fv['path'].'</td><td><img src="'.$fv['path'].'" style="max-width:75px;max-height:75px;" /></td></tr>';
		}
print <<<E
</table>
E;
	}

}

if(isset($_GET['update']) && $_GET['update'] == '1' && $found['ids'] != ''){
	// Update
	$db->query("UPDATE `vk_messages_attach` SET `path` = '' WHERE `uid` IN({$found['ids']})");
	print '<hr/>Данные успешно обновлены.<br/>Перейдите в очередь закачки чтобы обновить данные для исправленных записей.';
}

print <<<E
	</div>
</div>

          </div>
</div>
</body>
</html>
E;

$db->close($res);

?>