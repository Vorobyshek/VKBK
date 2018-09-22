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

$album_id = (isset($_GET['id'])) ? intval($_GET['id']) : '';
$header = '';
$photos = '';
$pic_albums = '';

if($album_id){
	$album = $db->query_row("SELECT * FROM vk_albums WHERE `id` = {$album_id}");
	$header = $album['name'];
		
	$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? intval($_GET['page']) : 0;
	$npage = $page+1;
	$offset_page = ($page > 0) ? $cfg['perpage_photo']*$page : 0;
	$q = $db->query("SELECT * FROM vk_photos WHERE `saved` = 1 AND `album_id` = {$album_id} ORDER BY `date_added` DESC LIMIT {$offset_page},{$cfg['perpage_photo']}");
	while($row = $db->return_row($q)){
		// Rewrite if you plan to store content outside of web directory and will call it by Alias
		if($cfg['vhost_alias'] == true && substr($row['path'],0,4) != 'http'){
			$row['path'] = $f->windows_path_alias($row['path'],'photo');
		}
$photos .= <<<E
    <div class="brick" style='width:{$cfg['photo_layout_width']}px;'><a class="fancybox" rel="album" href="{$row['path']}" data-fancybox="images"><img style="width:100%" src="{$row['path']}"></a></div>
E;
	}
	
} else { // end if album id

	$q = $db->query("SELECT a.*, p.path FROM vk_albums a LEFT JOIN vk_photos p ON a.id = p.album_id RIGHT JOIN ( SELECT album_id, MAX( date_added ) AS mdate FROM vk_photos GROUP BY album_id ) p2 ON a.id = p2.album_id WHERE p.date_added = mdate GROUP BY a.id");
	while($arow = $db->return_row($q)){
		if($cfg['vhost_alias'] == true && substr($arow['path'],0,4) != 'http'){
			$arow['path'] = $f->windows_path_alias($arow['path'],'photo');
		}

$pic_albums .= <<<E
<div class="col-sm-3">
<a href="albums.php?id={$arow['id']}" data-pjax style="background-image:url('{$arow['path']}');"><div class="d-flex justify-content-between align-items-center"><span class="text-truncate">{$arow['name']}</span><span>{$arow['img_total']}</span></div></a>
</div>
E;
	}

	// Show latest photos
	$q = $db->query("SELECT * FROM vk_photos WHERE `saved` = 1 ORDER BY `date_added` DESC LIMIT 0,25");
	while($row = $db->return_row($q)){
		// Rewrite if you plan to store content outside of web directory and will call it by Alias
		if($cfg['vhost_alias'] == true && substr($row['path'],0,4) != 'http'){
			$row['path'] = $f->windows_path_alias($row['path'],'photo');
		}
$photos .= <<<E
    <div class="brick" style='width:{$cfg['photo_layout_width']}px;'><a class="fancybox" rel="album" href="{$row['path']}" data-fancybox="images"><img style="width:100%" src="{$row['path']}"></a></div>
E;
	}
	$npage = 1;

}

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-camera-retro"></i> Фотографии</span>
		{$skin->albums_header($header)}
		<span class="nav-item mt-2 ml-auto mr-4">
			<select title="Альбомы" id="album" class="selectpicker show-tick" data-live-search="true" data-size="10" data-width="auto" data-style="btn-sm">
E;

/* Album list dropdown */
$r = $db->query("SELECT * FROM vk_albums ORDER BY id ASC");
while($album_list = $db->return_row($r)){
	if($album_list['id'] == $album_id){
		print '<option data-icon="fa fa-folder-open" data-subtext="'.$album_list['img_done'].'" value="'.$album_list['id'].'">'.$album_list['name'].'</option>';
	} else {
		print '<option data-icon="fa fa-folder-open" data-subtext="'.$album_list['img_done'].'" value="'.$album_list['id'].'">'.$album_list['name'].'</option>';
	}
}

print <<<E
            </select>
        </span>
    </nav>
</div><!-- Second menu end -->

    <!--div class="container-fluid"-->
      <div class="container">
        <div class="col-sm-12" id="albums-list">
E;
		if(!$album_id){
			print '<div class="row pic-albums">'.$pic_albums.'</div>';
		}
print <<<E
          <div class="free-wall" id="freewall">
			{$photos}
E;

print <<<E
			<div class="paginator-next" style="display:none;"><span class="paginator-val">{$npage}</span><a href="ajax/albums-paginator.php?id={$album_id}&page={$npage}">следующая страница</a></div>
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
$(document).ready(function() {
	$('.selectpicker').selectpicker();

	// Default options
	var freewall_width = {$cfg['photo_layout_width']};
	var notload = false;
	var list = jQuery("#freewall");
	var album = 'none';

	// If filter command changed, update url and reload data with new filter
	jQuery("#album").on('change', function(){
		if(album != this.value){
			album = this.value;
			$(".pic-albums").remove();
			ajax_page_reload('album',"?page=0&id="+album);
		}
	});

	apr_jscroller('album',jQuery("#freewall"));
	freewill(new Freewall("#freewall"),true,'n');

	$(".fancybox").fancybox({
		{$fancybox_options}
	});
	
	$(".full-name").tooltip();
	
	picalbschk();
	
});

$(window).resize(function() {
	picalbschk();
});

/* Toggle Visibility of Albums List */
function pic_albs(){
	$(".pic-albums").css({"overflow":"visible","height":"auto"});
	$(".pic-albums-more").hide();
}

/* Check and Place `show all alums` button */
function picalbschk(){
	var ww = $(window).width();
	if(jQuery(".pic-albums").css("overflow") === "hidden"){
		$(".pic-albums-more").remove();
		var picalbs = $(".pic-albums .col-sm-3:nth-child(4)");
		if(ww <= 768){
			var picalbs = $(".pic-albums .col-sm-3:nth-child(1)");
		}
		picalbs.after("<div class=\"pic-albums-more clearfix\" onclick=\"javascript:pic_albs();\">показать все альбомы</div>");
	}
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