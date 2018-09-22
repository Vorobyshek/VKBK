<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check do we have all needed GET data
$page = 0;
if(isset($_GET['page']) && is_numeric($_GET['page'])){
	$p = intval($_GET['page']);
	if($p > 0){ $page = $p; }
}
$dlgid = 0;
if(isset($_GET['dlgid']) && is_numeric($_GET['dlgid'])){
	$dlgid = $_GET['dlgid'];
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

$offset_page = ($page > 0) ? $cfg['perpage_dlg_messages']*$page : 0;
// Get 1 more video to see do we have something on the next page
$perpage = $cfg['perpage_dlg_messages']+1;
$next = 0;

$messages = array();
$users = array();
$users_data = array();
$groups = array();
$groups_data = array();
$attach = array();
$attach_data = array();

// Get message data
$q = $db->query("SELECT * FROM vk_messages WHERE `msg_dialog` = {$dlgid} ORDER BY `msg_date` DESC LIMIT {$offset_page},{$perpage}");
while($row = $db->return_row($q)){
	if($next < $cfg['perpage_dlg_messages']){
		
		$messages[] = $row;
		if($row['msg_user'] < 1){
			$groups[$row['msg_user']] = abs($row['msg_user']);
		} else {
			$users[$row['msg_user']] = $row['msg_user'];
		}
		if($row['msg_attach'] == 1){
			$attach[] = $row['msg_id'];
		}
	
	}
	// Increase NEXT so if we load a full page we would have in the end NEXT = perpage+1
	// Otherwise if next would be lower or equal perpage there is no result for the next page
	$next++;
}

// Get User information
if(count($users) > 0){
	$q = $db->query("SELECT id, first_name, last_name, nick, photo_path FROM vk_profiles WHERE `id` IN(".implode(",",$users).")");
	while($row = $db->return_row($q)){
		$users_data[$row['id']] = $row;
	}
}
// Get Groups information
if(count($groups) > 0){
	$q = $db->query("SELECT id, name, photo_path FROM vk_groups WHERE `id` IN(".implode(",",$groups).")");
	while($row = $db->return_row($q)){
		$groups_data[$row['id']] = $row;
	}
}
// Get Attachments
if(count($attach) > 0){
	$attachs_local = array();
	$skipped_local = array('sticker','link');
	$q = $db->query("SELECT * FROM vk_messages_attach WHERE `wall_id` IN(".implode(",",$attach).")");
	while($row = $db->return_row($q)){
		$attach_data[$row['wall_id']][] = $row;
		if($row['is_local'] == 1 && !in_array($row['type'],$skipped_local)){
			$attachs_local[$row['type']][$row['attach_id']] = $row['wall_id'];
		}
	}
	// If local attach exists
	if(count($attachs_local) > 0){
		// Set empty types for ID's
		$local = array(
			'photos' => array(),
			'videos' => array(),
			'docs'   => array()
		);
		// Go through locals and get ID's for types of attach
		// Structure of array: [type][attach_id] = wall_id
		foreach($attachs_local as $atlt => $atli){
			if($atlt == 'photo'){ foreach($atli as $lk => $lv){ $local['photos'][] = $lk; } }
			if($atlt == 'video'){ foreach($atli as $lk => $lv){ $local['videos'][] = $lk; } }
			if($atlt == 'doc'){   foreach($atli as $lk => $lv){ $local['docs'][] = $lk; } }
		}
		// If we have some local data... GET IT!
		// add local data to attach as [ID] => data so we could get it later
		if(!empty($local['photos'])){
			$qp = $db->query("SELECT * FROM vk_photos WHERE `id` IN(".implode(",",$local['photos']).")");
			while($row = $db->return_row($qp)){
				$attach_data[$attachs_local['photo'][$row['id']]][$row['id']] = $row;
			}
		}
		if(!empty($local['docs'])){
			$qp = $db->query("SELECT * FROM vk_docs WHERE `id` IN(".implode(",",$local['docs']).")");
			while($row = $db->return_row($qp)){
				$attach_data[$attachs_local['doc'][$row['id']]][$row['id']] = $row;
			}
		}
		if(!empty($local['videos'])){
			$qp = $db->query("SELECT * FROM vk_videos WHERE `id` IN(".implode(",",$local['videos']).")");
			while($row = $db->return_row($qp)){
				$attach_data[$attachs_local['video'][$row['id']]][$row['id']] = $row;
			}
		}
	}
}

$messages = array_reverse($messages);
if(count($messages) > 0){
	print '<div id="mp'.$page.'">';
}
foreach($messages as $k => $v){
	if($v['msg_user'] < 1){
		$uplus = abs($v['msg_user']);
		$who = $groups_data[$uplus];
		$ava_path = "groups/".$who['photo_path'];
		$who['first_name'] = $who['name'];
	} else {
		// Check if user is a ghost (does not known by me)
		if(!isset($users_data[$v['msg_user']])){
			$who = array(
				'id' => $v['msg_user'],
				'first_name' => "Неизвестный ID ".$v['msg_user'],
				'last_name' => "",
				'nick' => "Призрак",
				'photo_path' => ""
			);
			$ava_path = "#f44336";
		} else {
			$who = $users_data[$v['msg_user']];
			$ava_path = "profiles/".$who['photo_path'];
		}
	}
	
	$output_attach = '';
	
	if(isset($attach_data[$v['msg_id']])){

	$output_attach .= '<div class="p-2">';
	foreach($attach_data[$v['msg_id']] as $ak => $av){
		// Let's try to guess what type of data we have received
		
		// Type - Sticker
		if((isset($av['type']) && $av['type'] == 'sticker')){
$output_attach .= <<<E
    <div><img style="width:128px;height:128px;" src="data/stickers/{$av['path']}"></div>
E;
		} // end of STICKER
		
		// Type - Photo or attach photo
		if((isset($av['type']) && $av['type'] == 'photo')){
			if($av['is_local'] == 0 && $av['path'] != ''){
				// Rewrite for Alias
				if($cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
					$av['path'] = $f->windows_path_alias($av['path'],'photo');
				}
$output_attach .= <<<E
    <div class="brick" style='width:{$cfg['wall_layout_width']}px;'><a class="fancybox" data-fancybox="images" rel="p{$v['msg_id']}" href="{$av['path']}"><img style="width:100%" src="{$av['path']}"></a></div>
E;
			}
			if($av['is_local'] == 1 && isset($attach_data[$v['msg_id']][$av['attach_id']])){
				// Rewrite for Alias
				if($cfg['vhost_alias'] == true && substr($attach_data[$v['msg_id']][$av['attach_id']]['path'],0,4) != 'http'){
					$attach_data[$v['msg_id']][$av['attach_id']]['path'] = $f->windows_path_alias($attach_data[$v['msg_id']][$av['attach_id']]['path'],'photo');
				}
$output_attach .= <<<E
    <div class="brick" style='width:{$cfg['wall_layout_width']}px;'><a class="fancybox" data-fancybox="images" rel="p{$v['msg_id']}" href="{$attach_data[$v['msg_id']][$av['attach_id']]['path']}"><img style="width:100%" src="{$attach_data[$v['msg_id']][$av['attach_id']]['path']}"></a></div>
E;
			}
		} // end of attach photo
		
		// Remote Link Attach
		if(isset($av['type']) && $av['type'] == 'link'){
			// Rewrite for Alias
			if($cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $f->windows_path_alias($av['path'],'photo');
			}

			if($av['text'] != ''){ $av['text'] = nl2br($av['text']); }
			if($av['path'] != ''){
$output_attach .= <<<E
    <div class="wall-link-img"><a rel="p{$av['attach_id']}" href="#"><img style="width:100%" src="{$av['path']}"></a><a href="{$av['link_url']}" class="wall-link-caption" rel="nofollow noreferrer" target="_blank"><i class="fa fa-link"></i>&nbsp;{$av['caption']}</a></div>
<div class="col-sm-12" style="border:1px solid rgba(0,20,51,.12);">
	<h6>{$av['title']}</h6>
	<p class="wall-description">{$av['text']}</p>
</div>
E;
			} else {
$output_attach .= <<<E
<div class="col-sm-12">
	<h5><a href="{$av['link_url']}" rel="nofollow noreferrer" target="_blank"><i class="fas fa-share"></i> {$av['title']}</a></h5>
	<p class="wall-description">{$av['text']}</p>
</div>
E;
			}

		} // end of attach link
		
		// Type - Document or attach document
		if((isset($av['type']) && $av['type'] == 'doc')){
			// Rewrite for Alias
			if($cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $f->windows_path_alias($av['path'],'docs');
			}
			// Attach
			if(isset($av['player'])){
				// Have preview
				if($av['path'] != ''){
					// Rewrite for Alias
					if($cfg['vhost_alias'] == true && substr($av['player'],0,4) != 'http'){
						$av['player'] = $f->windows_path_alias($av['player'],'docs');
					}
					$animated = '';
					if(strtolower(substr($av['player'],-3)) == "gif"){
						$animated = 'class="doc-gif" data-docsrc="'.$av['player'].'" data-docpre="'.$av['path'].'"';
					}
$output_attach .= <<<E
    <div class="brick" style='width:100%;'><a class="fancybox" data-fancybox="images" rel="p{$av['attach_id']}" href="{$av['player']}"><img {$animated} style="width:100%" src="{$av['path']}"></a></div>
E;
				} else {
					$av['duration'] = $f->human_filesize($av['duration']);
					$av['caption'] = strtoupper($av['caption']);
$output_attach .= <<<E
<div class="col-sm-12">
	<h5><a href="{$av['player']}" rel="nofollow noreferrer" target="_blank"><i class="fas fa-share"></i> {$av['title']}</a></h5>
	<p class="wall-description"><span class="label label-default">{$av['caption']}</span> {$av['duration']}</p>
</div>
E;
				}
			}
			
		} // end of attach document
		
		// Remote Video Attach
		if(isset($av['type']) && $av['type'] == 'video'){
			// Rewrite for Alias
			if($cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $f->windows_path_alias($av['path'],'video');
			}
			
			// Clean player
			$av['player'] = $f->clean_player($av['player']);

			if($av['text'] != ''){ $av['text'] = '<div style="margin-bottom:10px;">'.nl2br($av['text']).'</div>'; }
			$av['duration'] = $skin->seconds2human($av['duration']);
$output_attach .= <<<E
	<div class="msg-video-box">
	    <span class="label label-default msg-video-duration px-2 py-1">{$av['duration']}</span>
	    <a class="various fancybox" href="javascript:;" onclick="javascript:fbox_video_global('{$av['player']}',1);" data-title-id="title-{$av['attach_id']}" style="background-image:url('{$av['path']}');"></a>
	</div>
	<h6 class="msg-video-header">{$av['title']}</h6>
	<div id="title-{$av['attach_id']}" style="display:none;">
	    {$av['text']}
	    <div class="expander" onClick="expand_desc();">показать</div>
	</div>
E;
		} // end of attach video
		
	}
	$output_attach .= '</div><div style="clear:both;"></div>';

	} // Output Attach end
	
	if(substr($ava_path,0,1) == "#"){
		$ava_path = '<span class="mb-2 ml-2" style="background:'.$ava_path.';display:block;min-width:35px;min-height:35px;float:left;border-radius:50%;"></span>';
	} else {
		$ava_path = '<img src="data/'.$ava_path.'" class="wall-ava dlg-ava mb-2 ml-2" />';
	}
	
print <<<E
<div class="msg-body mb-3">
	{$ava_path}
	<div class="ml-5 pl-3">
		<strong>{$who['first_name']}</strong>&nbsp;&nbsp;{$f->dialog_date_format($v['msg_date'])}<br/>
		{$v['msg_body']}{$output_attach}
	</div>
</div>
E;
}
if(count($messages) > 0){
	print '<div id="dlgp'.$page.'"></div></div>';
}

$db->close($res);

?>