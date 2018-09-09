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

print $skin->header_ajax();

$settings = array(
	'auto-queue-photo'=>0,
	'auto-queue-audio'=>0,
	'play-local-video'=>0,
	'start-local-video'=>0
);

$q = $db->query("SELECT * FROM vk_status");
while($row = $db->return_row($q)){
	if(array_key_exists($row['key'],$settings)){
		$row['val'] == '1' ? $row['val'] = 'checked' : $row['val'] = '';
		$settings[$row['key']] = $row['val'];
	}
}

print <<<E
<div class="nav-scroller mb-2" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fas fa-sliders-h"></i> Настройки</span>
    </nav>
</div>
<div class="container">
          <div class="container">
			<div class="row">
				<div class="col-sm-5 small">
<div class="card bg-light mb-3" style="max-width: 20rem;">
  <div class="card-header">Редиректы</div>
  <div class="card-body border-bottom">
    <p class="card-text">Автоматически переходить к очереди закачек после синхронизации фотографий.</p>
	<div class="text-center">
		<input id="auto-queue-photo" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success" data-offstyle="secondary" {$settings['auto-queue-photo']}>
	</div>
  </div>
  <div class="card-body">
    <p class="card-text">Автоматически переходить к очереди закачек после синхронизации аудиофайлов.</p>
	<div class="text-center">
		<input id="auto-queue-audio" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success" data-offstyle="secondary" {$settings['auto-queue-audio']}>
	</div>
  </div>
</div>
				</div>
				<div class="col-sm-5 small">
<div class="card bg-light mb-3" style="max-width: 20rem;">
  <div class="card-header">Видео</div>
  <div class="card-body border-bottom">
    <p class="card-text">Воспроизводить локальное видео вместо онлайн-плеера.</p>
	<div class="text-center">
		<input id="play-local-video" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success" data-offstyle="secondary" {$settings['play-local-video']}>
	</div>
  </div>
  <div class="card-body">
    <p class="card-text">Автоматически воспроизводить локальное видео.</p>
	<div class="text-center">
		<input id="start-local-video" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success" data-offstyle="secondary" {$settings['start-local-video']}>
	</div>
  </div>
</div>
				</div>
			</div>
          </div>
</div>
E;


print $skin->footer_ajax();

$db->close($res);

?>