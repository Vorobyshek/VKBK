<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('./cfg.php');
if(isset($_GET['_pjax']) || isset($_POST['_pjax'])){ $cfg['pj'] = true; }

// Get DB
require_once(ROOT.'classes/db.php');
$db = new db();
$res = $db->connect($cfg['host'],$cfg['user'],$cfg['pass'],$cfg['base']);

// Get Skin
require_once(ROOT.'classes/skin.php');
$skin = new skin();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

if(!$cfg['pj']){
	print $skin->header(array('extend'=>''));
	print $skin->navigation($lc);
}

$settings = array(
	'auto-queue-photo'=>0,
	'auto-queue-audio'=>0,
	'play-local-video'=>0,
	'start-local-video'=>0
);

$q = $db->query("SELECT * FROM vk_status");
while($row = $db->return_row($q)){
	if($row['key'] == 'auto-queue-photo'){  $row['val'] == '1' ? $row['val'] = 'checked' : $row['val'] = '';	}
	if($row['key'] == 'auto-queue-audio'){  $row['val'] == '1' ? $row['val'] = 'checked' : $row['val'] = '';	}
	if($row['key'] == 'play-local-video'){  $row['val'] == '1' ? $row['val'] = 'checked' : $row['val'] = '';	}
	if($row['key'] == 'start-local-video'){ $row['val'] == '1' ? $row['val'] = 'checked' : $row['val'] = '';	}
	$settings[$row['key']] = $row['val'];
}

print <<<E
<div class="container">
          <h2 class="sub-header"><i class="fa fa-sliders-h"></i> Настройки</h2>
          <div class="container">
			<div class="row">
				<div class="col-sm-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Редиректы</h3>
						</div>
						<div class="panel-body">
							<div class="clearfix">
							<div style="float:left;margin:0 10px 10px 0">
								<input id="auto-queue-photo" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="info" {$settings['auto-queue-photo']}>
							</div>
							<div>Автоматически переходить к очереди закачек после синхронизации фотографий.</div>
							</div>
							<hr/>
							<div class="clearfix">
							<div style="float:left;margin:0 10px 10px 0">
								<input id="auto-queue-audio" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="info" {$settings['auto-queue-audio']}>
							</div>
							<div>Автоматически переходить к очереди закачек после синхронизации аудиофайлов.</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Видео</h3>
						</div>
						<div class="panel-body">
							<div class="clearfix">
							<div style="float:left;margin:0 10px 10px 0">
								<input id="play-local-video" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="info" {$settings['play-local-video']}>
							</div>
							<div>Воспроизводить локальное видео вместо онлайн-плеера.</div>
							</div>
							<hr/>
							<div class="clearfix">
							<div style="float:left;margin:0 10px 10px 0">
								<input id="start-local-video" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="info" {$settings['start-local-video']}>
							</div>
							<div>Автоматически воспроизводить локальное видео.</div>
							</div>
						</div>
					</div>
				</div>
			</div>
          </div>
</div>
E;

$ex_bot = <<<E
<script type="text/javascript">
$(document).ready(function() {
	$('input[data-toggle]').bootstrapToggle();
	$('#auto-queue-photo').change(function() {
		$.get("ajax/settings-save-bool.php", { "option":"auto-queue-photo","v":$(this).prop('checked') } );
    });
	$('#auto-queue-audio').change(function() {
		$.get("ajax/settings-save-bool.php", { "option":"auto-queue-audio","v":$(this).prop('checked') } );
    });
	$('#play-local-video').change(function() {
		$.get("ajax/settings-save-bool.php", { "option":"play-local-video","v":$(this).prop('checked') } );
    });
	$('#start-local-video').change(function() {
		$.get("ajax/settings-save-bool.php", { "option":"start-local-video","v":$(this).prop('checked') } );
    });
});
</script>

E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=> $ex_bot));
} else {
	print $ex_bot;
}

$db->close($res);

?>