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

// Get Functions
require_once(ROOT.'classes/func.php');
$f = new func();

// Get local counters for top menu
$lc = $db->query_row("SELECT * FROM vk_counters");

if(!$cfg['pj']){
	print $skin->header(array('extend'=>''));
	print $skin->navigation($lc);
}

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="far fa-comment-alt"></i> Диалоги</span>
    </nav>
</div>
<div class="container">
    <div class="row" id="dial-list">
E;

$dial_list = array();
$dial_users = '';
$dial_groups = '';

// Show Dialog List
$r = $db->query("SELECT * FROM vk_dialogs ORDER BY date DESC");
while($row = $db->return_row($r)){
	if($row['chat_id'] > 0){ $row['id'] = $row['admin_id']; } // Set profile id for multichat
	$dial_list[$row['date']] = array('id' => $row['id'],'chat_id' => $row['chat_id'],'title' => $row['title'],'date' => $row['date'],'data' => '');
	if($row['id'] >= 1){ $dial_users .= ($dial_users != '' ? ',' : '').$row['id']; } else { $dial_groups .= ($dial_groups != '' ? ',' : '').abs($row['id']); }
} // End of while

if(!empty($dial_users)){
	$r = $db->query("SELECT * FROM vk_profiles WHERE `id` IN (".$dial_users.")");
	while($row = $db->return_row($r)){
		foreach($dial_list as $k => $v){
			if($v['id'] == $row['id']){ $dial_list[$k]['data'] = $row; $dial_list[$k]['data']['path'] = 'profiles'; }
		}
	}
}
if(!empty($dial_groups)){
	$r = $db->query("SELECT * FROM vk_groups WHERE `id` IN (".$dial_groups.")");
	while($row = $db->return_row($r)){
		foreach($dial_list as $k => $v){
			if($v['id'] == -$row['id']){ $dial_list[$k]['data'] = $row; $dial_list[$k]['data']['path'] = 'groups'; }
		}
	}
}

print <<<E
		<div class="col-sm-4" id="dialog-left">
E;

foreach($dial_list as $k => $v){
	if(!is_array($v['data'])){	// For some reason if we don't have any info about user. How this could be?!
print <<<E
<div class="mb-1 dialogs-head border-bottom list-group-item list-group-item-action justify-content-between" style="cursor:pointer;">
<div class="ml-5 pl-3 d-flex">
<small style="color:red;">Неопознанный пользователь</small> <span class="full-date ml-auto"></span>
</div>
<div class="ml-5 pl-3 d-flex text-truncate">ID: {$v['id']}</div>
</div>
E;
	} else {
		$n = (!isset($v['data']['name']) ? $n = $v['data']['first_name'].' '.$v['data']['last_name'] : $n = $v['data']['name'] );
		$full_date = date("d M Y H:i",$v['date']);
		$v['date'] = $f->dialog_date_format($v['date']);
		if($v['chat_id'] > 0){ $v['id'] = 2000000000+$v['chat_id']; }
print <<<E
<div class="mb-1 dialogs-head border-bottom list-group-item list-group-item-action justify-content-between" onclick="javascript:dialog_load({$v['id']});return false;" style="cursor:pointer;">
<img src="data/{$v['data']['path']}/{$v['data']['photo_path']}" class="wall-ava mb-2" />
<div class="ml-5 pl-3 d-flex">
<small>{$n}</small> <span class="full-date ml-auto" data-placement="right" data-toggle="tooltip" data-original-title="{$full_date}">{$v['date']}</span>
</div>
<div class="ml-5 pl-3 d-flex text-truncate">{$v['title']}</div>
</div>
E;
	}
}

print <<<E
		</div>
		<div class="col-sm-8 mb-3 p-3 bg-white rounded box-shadow" id="dialog-right">
			<div class="d-flex justify-content-center align-items-center" id="dialog-default">
				<div class="text-center" id="dialog-none">
				<i class="far fa-comment-alt"></i><br/>
				<small>Выберите диалог для просмотра</small>
				</div>
				
			</div>
			<div id="dialog-data"></div>
		</div>
E;

print <<<E

    </div>
</div>
E;

// Fancybox Options
$fancybox_options = <<<E
	loop		: true,
	keyboard	: true,
	arrows		: true,
	infobar		: false,
	toolbar		: true,
	buttons		: [ 'fullScreen','close' ],
	animationEffect		: false,
	transitionEffect	: false,
	touch		: {
		vertical	: false
	},
	hash		: false
E;

$ex_bot = <<<E
<script type="text/javascript">
// Default options
var page = 0;
var dlgid = 'none';
var stopit = false;
var tmpscroll = 0;

$(document).ready(function() {
	const psl = new PerfectScrollbar('#dialog-left', {
	wheelSpeed: 2,
	wheelPropagation: true,
	minScrollbarLength: 50
	});
	
	const container = document.querySelector('#dialog-right');
	const psr = new PerfectScrollbar(container, {
	wheelSpeed: 2,
	wheelPropagation: true,
	minScrollbarLength: 50
	});

	var notload = false;
	var dlgcon = jQuery("#dialog-right");
	
	
	// Bootstrip select
	$('.selectpicker').selectpicker({
		iconBase: 'fa',
		tickIcon: 'fa-check'
	});
	
	// Hash URL commands
	urlCommands.bind('dlgid', function(id) { dlgid = id; });
	
	// Not default options -> reload
	if(dlgid != 'none'){
		urlCommands.urlPush({page:0});
		page = 0;
		ajax_page_reload('dialog',"?page=0&dlgid="+dlgid);
		scroll_or_roll(page);
	}
	
	container.addEventListener('ps-y-reach-start', function () {
		if(stopit == false){
			page = page+1;
			$.ajax({
				async : false,
				cache : false,
				method : "GET",
				url : paginator_dialog+"?page="+page+"&dlgid="+dlgid
			}).done( function(data){
				if(data.length == 0){ stopit = true; }
				$('#dialog-data').prepend(data);
				console.log('Page changed '+dlgid+' page '+page);
				urlCommands.urlPush({dlgid:dlgid,page:page});
				scroll_or_roll(page);
			});
		}
    });

	$('.various-local').fancybox({
		{$fancybox_options}
	});
	
	$(".tip").tooltip();
	
	dialog_height();

});

$(window).on('resize',function(){
	dialog_height();
});

function dialog_height(){
	var vh = $(window).height();
	var ch = 180;
	$("#dialog-left").css("height",vh-ch);
	$("#dialog-right").css("height",vh-ch);
}

function scroll_or_roll(page){
	if($('#dlgp'+page).length > 0){
		var sort = $('#dlgp'+page).position().top;
		if(sort < 600){ sort = 600; }
		console.log('scROLLING to: '+sort);
		jQuery("#dialog-right").animate({ scrollTop: sort}, 250);
	}
}

function dialog_load(id){
	$("#dialog-data").html(' ');	// Destroy old data
	page = 0;
	dlgid = id;
	stopit = false;
    urlCommands.urlPush({dlgid:id,page:0});
    ajax_page_reload('dialog',"?page=0&dlgid="+id);
	scroll_or_roll(0);
}

</script>
E;

if(!$cfg['pj']){
	print $skin->footer(array('extend'=> $ex_bot));
} else {
	print $ex_bot;
}

$db->close($res);

?>