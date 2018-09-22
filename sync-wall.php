<?php

header('Content-Type: text/html; charset=UTF-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('./cfg.php');

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

print $skin->header(array('extend'=>''));
print $skin->navigation($lc);

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : -1;
$count = 100;

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-sync"></i> Синхронизация - стена</span>
    </nav>
</div>
<div class="container">
          <div class="table-responsive">
            <table class="table table-striped">
E;

if($offset >= 0){

$don = false;

// Include VK.API
require_once(ROOT.'classes/VK/VK.php');

// Check token
$q = $db->query("SELECT * FROM vk_session WHERE `vk_id` = 1");
$vk_session = $row = $db->return_row($q);
$token_valid = false;

if($vk_session['vk_token']){
	$vk = new VK($cfg['vk_id'], $cfg['vk_secret'], $vk_session['vk_token']);
	// Set API version
	$vk->setApiVersion($cfg['vk_api_version']);
	$token_valid = $vk->checkAccessToken($vk_session['vk_token']);
} else {
	$vk = new VK($cfg['vk_id'], $cfg['vk_secret']);
	// Set API version
	$vk->setApiVersion($cfg['vk_api_version']);
}

if($vk_session['vk_token'] != '' && $token_valid == true){
	try {
	
	// We logged in, get VK photos
	$api = $vk->api('wall.get', array(
		'owner_id' => $vk_session['vk_user'],
		'offset' => $offset,
		'count' => $count,
		'filter' => 'all',
		'extended' => 1, // 1 — будут возвращены три массива wall, profiles и groups.
	));
	
	$api_posts = array();
	$api_profiles = array();
	$api_groups = array();
	$vk_post_total = 0;
	$fast_sync = (isset($_GET['fast']) && $_GET['fast'] == 1) ? 1 : 0;
	$fast_sync_date = 0;
	$fast_sync_stop = false;
	
	if($api['response'] != ''){
		
		$don = true;
		$api_profiles = $api['response']['profiles'];
		$api_groups = $api['response']['groups'];
		$api_posts = $api['response']['items'];
		$vk_post_total = $api['response']['count'];
		
	}
	
	// If we do fast sync, get the date of the last post in DB
	if($fast_sync == 1){
		$lp = $db->query_row("SELECT date FROM `vk_wall` ORDER BY date DESC LIMIT 1");
		if(!empty($lp['date'])){ $fast_sync_date = intval($lp['date']); }
	}
	
	// Check & process profiles
	if(!empty($api_profiles)){
		
		$profile_ids = '';
		$profile_new_ids = array();
		
		// Get returned IDs
		foreach($api_profiles as $pk => $pv){
			$profile_ids .= ($profile_ids != '' ? ',' : '').$pv['id'];
		}
		
		if($profile_ids != ''){
			$q = $db->query("SELECT * FROM vk_profiles WHERE id IN(".$profile_ids.")");
			$profile_ids = explode(',',$profile_ids);
			
			while($row = $db->return_row($q)){
				if(in_array($row['id'],$profile_ids)){
					// Existing profile. Check it for changes.
					
					// Remove profile id from known list
					$k = array_search($row['id'],$profile_ids);
					unset($profile_ids[$k]);
				}
			}
			
			// Set last profiles as new and save em
			if(!empty($profile_ids)){
				// Get data to new profiles array
				foreach($api_profiles as $ak => $av){
					if(in_array($av['id'],$profile_ids)){
						$profile_new_ids[$av['id']] = $av;
					}
				}
				
				$profile_data = '';
				if(!empty($profile_new_ids)){
					// Make import query string
					foreach($profile_new_ids as $k => $v){
						if(!isset($v['screen_name'])){ $v['screen_name']='id'.$v['id']; }
						$profile_data .= ($profile_data != '' ? ',' : '')."({$v['id']},'".$db->real_escape($v['first_name'])."','".$db->real_escape($v['last_name'])."',{$v['sex']},'{$v['screen_name']}','{$v['photo_100']}','')";
					}
					
					// If we have data to import, do it!
					if($profile_data != ''){
						$q = $db->query("INSERT INTO vk_profiles (`id`,`first_name`,`last_name`,`sex`,`nick`,`photo_uri`,`photo_path`) VALUES ".$profile_data);
					}
				}
			} // end new profiles
		}
	} // Profiles END
	
	
	// Check & process group profiles
	if(!empty($api_groups)){
		
		$group_ids = '';
		$group_new_ids = array();
		
		// Get returned IDs
		foreach($api_groups as $gk => $gv){
			$group_ids .= ($group_ids != '' ? ',' : '').$gv['id'];
		}
		
		if($group_ids != ''){
			$q = $db->query("SELECT * FROM vk_groups WHERE id IN(".$group_ids.")");
			$group_ids = explode(',',$group_ids);
			
			while($row = $db->return_row($q)){
				if(in_array($row['id'],$group_ids)){
					// Existing group. Check it for changes.
					
					// Remove group id from known list
					$k = array_search($row['id'],$group_ids);
					unset($group_ids[$k]);
				}
			}
			
			// Set last groups as new and save em
			if(!empty($group_ids)){
				// Get data to new group profiles array
				foreach($api_groups as $gk => $gv){
					if(in_array($gv['id'],$group_ids)){
						$group_new_ids[$gv['id']] = $gv;
					}
				}
				
				$group_data = '';
				if(!empty($group_new_ids)){
					// Make import query string
					foreach($group_new_ids as $k => $v){
						$group_data .= ($group_data != '' ? ',' : '')."({$v['id']},'".$db->real_escape($v['name'])."','{$v['screen_name']}','{$v['photo_100']}','')";
					}
					
					// If we have data to import, do it!
					if($group_data != ''){
						$q = $db->query("INSERT INTO vk_groups (`id`,`name`,`nick`,`photo_uri`,`photo_path`) VALUES ".$group_data);
					}
				}
			} // end new groups
		}
	} // Groups END
	
	
	// Posts
	if(!empty($api_posts)){
		
		foreach($api_posts as $k => $v){
			$attach = 0;
			$repost = 0;
			$repost_attach = 0;
			
			// Check attachments
			if(!empty($v['attachments'])){
				$attach = 1;
				
				foreach($v['attachments'] as $atv => $atk){
					// Attach - Photo
					if($atk['type'] == 'photo'){
						// Check do we have this attach already?
						$at = $db->query_row("SELECT id FROM vk_photos WHERE id = ".$atk['photo']['id']);
						// Attach found, make a link
						if(!empty($at['id']) && $atk['photo']['owner_id'] == $vk_session['vk_user']){
							// Insert OR update
							$f->wall_attach_update($v['id'],$atk);
						} else {
							$photo_uri = $f->get_largest_photo($atk['photo']);
							
							// Save information about attach
							$f->wall_attach_insert($v['id'],$atk,$photo_uri);
						}
					}
					
					// Attach - Video
					if($atk['type'] == 'video'){
						// Check do we have this attach already?
						$at = $db->query_row("SELECT id FROM vk_videos WHERE id = ".$atk['video']['id']);
						// Attach found, make a link
						if(!empty($at['id']) && $atk['video']['owner_id'] == $vk_session['vk_user']){
							// Insert OR update
							$f->wall_attach_update($v['id'],$atk);
						} else {
							$photo_uri = $f->get_largest_photo($atk['video']);
							$atk['video']['player'] = '';
							
							// Get video player code for external attach
							$v_api = $vk->api('video.get', array(
								'videos' => $atk['video']['owner_id'].'_'.$atk['video']['id'].($atk['video']['access_key'] != '' ? '_'.$atk['video']['access_key'] : ''),
								'extended' => 0, // возвращать ли информацию о настройках приватности видео для текущего пользователя
								'offset' => 0,
								'count' => 1
							));
							
							if(isset($v_api['response']['items'][0]['player']) && $v_api['response']['items'][0]['player'] != ''){
								$atk['video']['player'] = $v_api['response']['items'][0]['player'];
							}
							
							// Save information about attach
							$f->wall_attach_insert($v['id'],$atk,$photo_uri);
						}
					}
					
					// Attach - Link
					if($atk['type'] == 'link'){
						// For links we use a date as id because link type does not have a id
						$atk['link']['id'] = $v['date'];
						$atk['link']['owner_id'] = $v['owner_id'];
						$atk['link']['date'] = $v['date'];
						$atk['link']['access_key'] = '';
						// Check do we have this attach already?
						$at = $db->query_row("SELECT attach_id FROM vk_attach WHERE attach_id = ".$atk['link']['id']);
						// Attach found, just skip it ><
						if(!empty($at['attach_id'])){
							// Insert OR update
							//$f->wall_attach_update($v['id'],$atk);
						} else {
							if(isset($atk['link']['photo'])){
								$photo_uri = $f->get_largest_photo($atk['link']['photo']);
								$atk['link']['width']  = (isset($atk['link']['photo']['width'])) ? $atk['link']['photo']['width'] : 0 ;
								$atk['link']['height'] = (isset($atk['link']['photo']['height'])) ? $atk['link']['photo']['height'] : 0;
							} else {
								$photo_uri = '';
							}
							
							// Save information about attach
							$f->wall_attach_insert($v['id'],$atk,$photo_uri);
						}
					}
					
					// Attach - Audio
					if($atk['type'] == 'audio'){
						// Checking availabiliy of API
						if($atk['audio']['duration'] != 25 && strpos($atk['audio']['url'],"audio_api_unavailable") !== false){
							// Check do we have this attach already?
							$at = $db->query_row("SELECT id FROM vk_music WHERE id = ".$atk['audio']['id']);
							// Attach found, make a link
							if(!empty($at['id']) && $atk['audio']['owner_id'] == $vk_session['vk_user']){
								// Insert OR update
								$f->wall_attach_update($v['id'],$atk);
							} else {
								$photo_uri                  = $atk['audio']['url'];
								$atk['audio']['caption']    = $atk['audio']['artist'];
								$atk['audio']['access_key'] = '';
								// Save information about attach
								$f->wall_attach_insert($v['id'],$atk,$photo_uri);
							}
						}
					}
					
					// Attach - Document
					/*
						title -> title
						size -> duration
						ext -> text
						url -> uri ( for local copy path )
						date -> date
						type -> not saved
						preview (
							photo -> link_url ( for local copy player )
							width -> width
							height -> height
						)
					*/
					if($atk['type'] == 'doc'){
						// Check do we have this attach already?
						$at = $db->query_row("SELECT id FROM vk_docs WHERE id = ".$atk['doc']['id']);
						$photo_uri = '';
						// Attach found, make a link
						if(!empty($at['id']) && $atk['doc']['owner_id'] == $vk_session['vk_user']){
							// Insert OR update
							$f->wall_attach_update($v['id'],$atk);
						} else {
							$atk['doc']['caption'] = $atk['doc']['ext'];
							$atk['doc']['width'] = 0;
							$atk['doc']['height'] = 0;
							$atk['doc']['duration'] = $atk['doc']['size'];
							$atk['doc']['text'] = $atk['doc']['ext'];
							
							if(isset($atk['doc']['preview'])){
								// Images
								if(isset($atk['doc']['preview']['photo'])){
									// Get biggest preview
									$sizes = $f->get_largest_doc_image($atk['doc']['preview']['photo']['sizes']);
									if($sizes['pre'] != ''){
										$photo_uri = $sizes['pre'];
										$atk['doc']['width'] = $sizes['prew'];
										$atk['doc']['height'] = $sizes['preh'];
									}
								}
							} // Preview end
							// Audio MSG
							// no reason to do until VK disabled audio api

							// Save information about attach
							$f->wall_attach_insert($v['id'],$atk,$photo_uri);
						}
					}
				}
			}
			
			$origin = 0;
			$origin_owner = 0;
			// Repost parser
			if(!empty($v['copy_history'])){
				$origin = $v['copy_history'][0]['id'];
				$origin_owner = $v['copy_history'][0]['owner_id'];
				foreach($v['copy_history'] as $chk => $chv){
					$rp = $v['copy_history'][$chk];
					$repost = $rp['id'];
				
					// Check repost attachments
					if(!empty($rp['attachments'])){
						$repost_attach = 1;
						
						foreach($rp['attachments'] as $rpatv => $rpatk){
							// Attach - Photo
							if($rpatk['type'] == 'photo'){
								// Check do we have this attach already?
								$at = $db->query_row("SELECT id FROM vk_photos WHERE id = ".$rpatk['photo']['id']);
								// Attach found, make a link
								if(!empty($at['id']) && $rpatk['photo']['owner_id'] == $vk_session['vk_user']){
									// Insert OR update
									$f->wall_attach_update($rp['id'],$rpatk);
								} else {
									$photo_uri = $f->get_largest_photo($rpatk['photo']);
									
									// Save information about attach
									$f->wall_attach_insert($rp['id'],$rpatk,$photo_uri);
								}
							}
						
							// Attach - Video
							if($rpatk['type'] == 'video'){
								// Check do we have this attach already?
								$at = $db->query_row("SELECT id FROM vk_videos WHERE id = ".$rpatk['video']['id']);
								// Attach found, make a link
								if(!empty($rpat['id']) && $rpatk['video']['owner_id'] == $vk_session['vk_user']){
									// Insert OR update
									$f->wall_attach_update($rp['id'],$rpatk);
								} else {
									$photo_uri = $f->get_largest_photo($rpatk['video']);
									$rpatk['video']['player'] = '';
								
									// Get video player code for external attach
									$v_api = $vk->api('video.get', array(
										'videos' => $rpatk['video']['owner_id'].'_'.$rpatk['video']['id'].($rpatk['video']['access_key'] != '' ? '_'.$rpatk['video']['access_key'] : ''),
										'extended' => 0, // возвращать ли информацию о настройках приватности видео для текущего пользователя
										'offset' => 0,
										'count' => 1
									));
									
									if(isset($v_api['response']['items'][0]['player']) && $v_api['response']['items'][0]['player'] != ''){
										$rpatk['video']['player'] = $v_api['response']['items'][0]['player'];
									}
									
									// Save information about attach
									$f->wall_attach_insert($rp['id'],$rpatk,$photo_uri);
								}
							}
							
							// Attach - Link
							if($rpatk['type'] == 'link'){
								// For links we use a date as id because link type does not have a id
								$rpatk['link']['id']       = $rp['date'];
								$rpatk['link']['owner_id'] = $rp['owner_id'];
								$rpatk['link']['date']     = $rp['date'];
								$rpatk['link']['access_key'] = '';
								// Check do we have this attach already?
								$at = $db->query_row("SELECT attach_id FROM vk_attach WHERE attach_id = ".$rpatk['link']['id']);
								// Attach found, just skip it ><
								if(!empty($at['attach_id'])){
									// Insert OR update
									//$f->wall_attach_update($rp['id'],$rpatk);
								} else {
									if(isset($rpatk['link']['photo'])){
										$photo_uri = $f->get_largest_photo($rpatk['link']['photo']);
										$rpatk['link']['width']  = (isset($rpatk['link']['photo']['width'])) ? $rpatk['link']['photo']['width'] : 0;
										$rpatk['link']['height'] = (isset($rpatk['link']['photo']['height'])) ? $rpatk['link']['photo']['height'] : 0;
									} else {
										$photo_uri = '';
									}
									
									// Save information about attach
									$f->wall_attach_insert($rp['id'],$rpatk,$photo_uri);
								}
							}
							
							// Attach - Audio
							if($rpatk['type'] == 'audio'){
								// Checking availabiliy of API
								if($rpatk['audio']['duration'] != 25 && strpos($rpatk['audio']['url'],"audio_api_unavailable") !== false){
									// Check do we have this attach already?
									$at = $db->query_row("SELECT id FROM vk_music WHERE id = ".$rpatk['audio']['id']);
									// Attach found, make a link
									if(!empty($at['id']) && $rpatk['audio']['owner_id'] == $vk_session['vk_user']){
										// Insert OR update
										$f->wall_attach_update($rp['id'],$rpatk);
									} else {
										$photo_uri                    = $rpatk['audio']['url'];
										$rpatk['audio']['caption']    = $rpatk['audio']['artist'];
										$rpatk['audio']['access_key'] = '';
										// Save information about attach
										$f->wall_attach_insert($rp['id'],$rpatk,$photo_uri);
									}
								}
							}
						}
					} // attachments
					
					// If post have a repost inside, save it as another post
					if($repost > 0){
						// For multiple reposts let's check the next post id, if exists add it to current repost
						$ch_next = $chk+1;
						if(isset($v['copy_history'][$ch_next]['id']) && $v['copy_history'][$ch_next]['id'] > 0){
							$rerepost = $v['copy_history'][$ch_next]['id'];
							$rerepost_owner = ($chk > 1) ? $v['copy_history'][$ch_next-1]['owner_id'] : $v['copy_history'][$ch_next]['owner_id'];
						} else {$rerepost = 0; $rerepost_owner = 0; }
						$f->wall_post_insert($rp,$repost_attach,$rerepost,$rerepost_owner,1);
					}
				
				} // Foreach end
			} // Reposts end
			
			// Insert OR update post
			$f->wall_post_insert($v,$attach,$origin,$origin_owner,0);
			
			// Fast sync option
			// Check the date of the last post to our posts. If found, stop sync.
			if($fast_sync == 1 && $fast_sync_date > 0 && $v['date'] <= $fast_sync_date){
				$fast_sync_stop = true;
			}
		}
		
	}
	
	// I want this logic in one line, but this blow my mind so...
	$to = 0;
	if($offset == 0){
		$to = $count;
		if($count > $vk_post_total){
			$to = $vk_post_total;
		}
	} else {
		if(($count+$offset) > $vk_post_total){
			$to = $vk_post_total;
		} else {
			$to = $count+$offset;
		}
	}
	if($offset > 0){ $ot = $offset; } else { $ot = 1; }
	
	print '<tr><td>Получаем записи <b> '.$ot.' - '.$to.' / '.$vk_post_total.'</b> со стены ВК.</td></tr>';
	
	// Let's recount wall
	$q5 = $db->query("UPDATE vk_counters SET `wall` = (SELECT COUNT(*) FROM vk_wall WHERE `is_repost` = 0)");
	
	if($fast_sync == 1 && $fast_sync_stop == true){
		// No unsynced posts left. This is the end...
		print '<tr><td><div class="alert alert-success" role="alert"><strong>Великая китайская!</strong> Быстрая синхронизация сообщений завершена.</div></td></tr>';
	} else {
	
		// If we done with all posts
		if(($offset+$count) >= $vk_post_total){
			// No unsynced posts left. This is the end...
			print '<tr><td><div class="alert alert-success" role="alert"><strong>Великая китайская!</strong> Синхронизация всех сообщений со стены завершена.</div></td></tr>';
		} else {
			// Some posts on the wall is not synced yed
			print '<tr><td>Перехожу к следующей порции сообщений...</td></tr>';
		
			// Calculate offset and reload page
			$offset_new = $offset+$count;
			print $skin->reload('info',"Страница будет обновлена через <span id=\"gcd\">".$cfg['sync_wall_next_cd']."</span> сек.","sync-wall.php?offset=".$offset_new."&fast=".$fast_sync."",$cfg['sync_wall_next_cd']);
		}
	
	} // Fast sync end
	
	
	// END Of catch

	} catch (Exception $error) {
		echo '<tr><td>'.$error->getMessage().'</td></tr>';
	}
// end of Token Check
} else {
	// Token is NOT valid, re-auth?
print <<<E
<tr>
  <td>
    <div class="alert alert-danger" role="alert"><span>Внимание!</span> Токен является недействительным. Необходимо авторизироваться. Перейти в <a href="index.php">Панель управления</a> для авторизации?</div>
  </td>
</tr>
E;
}

if($don == false && $token_valid == true){
print <<<E
<tr>
  <td>
    <div class="alert alert-info" role="alert">Нет заданий для синхронизации</div>
  </td>
</tr>
E;
}

// End of IF OFFSET
} else {
print <<<E
<tr>
  <td>
    <div class="alert alert-info" role="alert">Нет заданий для синхронизации</div>
  </td>
</tr>
E;
}

print <<<E
            </table>
          </div>
</div>
E;

print $skin->footer(array('extend'=>''));

$db->close($res);

?>