<?php

class attach {
	
	function attach(){
		return true;
	}
	
	// Type - Photo
	/* IN:
	  av - array with attach data
	  attach_data -
	  v -
	   OUT: (string) $output
	*/
	function dlg_attach_photo($av,$attach_data,$v){
		$output = '';
		if((isset($av['type']) && $av['type'] == 'photo')){
			if($av['is_local'] == 0 && $av['path'] != ''){
				// Rewrite for Alias
				if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
					$av['path'] = $this->func->windows_path_alias($av['path'],'photo');
				}
$output .= <<<E
    <div class="brick" style='max-width:350px;'><a class="fancybox" data-fancybox="images" rel="p{$v['msg_id']}" href="{$av['path']}"><img style="width:100%" src="{$av['path']}"></a></div>
E;
			}
			if($av['is_local'] == 1 && isset($attach_data[$v['msg_id']][$av['attach_id']])){
				// Rewrite for Alias
				if($this->cfg['vhost_alias'] == true && substr($attach_data[$v['msg_id']][$av['attach_id']]['path'],0,4) != 'http'){
					$attach_data[$v['msg_id']][$av['attach_id']]['path'] = $this->func->windows_path_alias($attach_data[$v['msg_id']][$av['attach_id']]['path'],'photo');
				}
$output .= <<<E
    <div class="brick" style='max-width:350px;'><a class="fancybox" data-fancybox="images" rel="p{$v['msg_id']}" href="{$attach_data[$v['msg_id']][$av['attach_id']]['path']}"><img style="width:100%" src="{$attach_data[$v['msg_id']][$av['attach_id']]['path']}"></a></div>
E;
			}
		}
		return $output;
	} // end of attach photo
	
	// Type = Link
	/* IN:
	  av - array with attach data
	   OUT: (string) $output
	*/
	function dlg_attach_link($av){
		$output = '';
		if(isset($av['type']) && $av['type'] == 'link'){
			// Rewrite for Alias
			if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $this->func->windows_path_alias($av['path'],'photo');
			}

			if($av['text'] != ''){ $av['text'] = nl2br($av['text']); }
			if($av['path'] != ''){
$output .= <<<E
<div>
	<div class="wall-link-img"><a rel="p{$av['attach_id']}" href="#"><img style="width:100%" src="{$av['path']}"></a><a href="{$av['link_url']}" class="wall-link-caption" rel="nofollow noreferrer" target="_blank"><i class="fa fa-link"></i>&nbsp;{$av['caption']}</a></div>
	<div class="col-sm-12" style="border:1px solid rgba(0,20,51,.12);">
		<h6 style="max-width:400px;">{$av['title']}</h6>
		<p class="wall-description">{$av['text']}</p>
	</div>
</div>
E;
			} else {
$output .= <<<E
<div class="col-sm-12">
	<h5><a href="{$av['link_url']}" rel="nofollow noreferrer" target="_blank"><i class="fas fa-share"></i> {$av['title']}</a></h5>
	<p class="wall-description">{$av['text']}</p>
</div>
E;
			}
		}
		return $output;
	} // end of attach link
	
	// Type - Document
	function dlg_attach_doc($av){
		$output = '';
		if((isset($av['type']) && $av['type'] == 'doc')){
			// Rewrite for Alias
			if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $this->func->windows_path_alias($av['path'],'docs');
			}
			// Attach
			if(isset($av['player'])){
				// Have preview
				if($av['path'] != ''){
					// Rewrite for Alias
					if($this->cfg['vhost_alias'] == true && substr($av['player'],0,4) != 'http'){
						$av['player'] = $this->func->windows_path_alias($av['player'],'docs');
					}
					$animated = '';
					if(strtolower(substr($av['player'],-3)) == "gif"){
						$animated = 'class="doc-gif" data-docsrc="'.$av['player'].'" data-docpre="'.$av['path'].'"';
					}
$output .= <<<E
    <div class="brick" style='width:100%;max-width:260px;'><a class="fancybox" data-fancybox="images" rel="p{$av['attach_id']}" href="{$av['player']}"><img {$animated} style="width:100%" src="{$av['path']}"></a></div>
E;
				} else {
					$av['duration'] = $this->func->human_filesize($av['duration']);
					$av['caption'] = strtoupper($av['caption']);
$output .= <<<E
<div class="col-sm-12">
	<h5><a href="{$av['player']}" rel="nofollow noreferrer" target="_blank"><i class="fas fa-share"></i> {$av['title']}</a></h5>
	<p class="wall-description"><span class="label label-default">{$av['caption']}</span> {$av['duration']}</p>
</div>
E;
				}
			}
		}
		return $output;
	} // end of attach document
	
	// Type = Video
	function dlg_attach_video($av){
		$output = '';
		if(isset($av['type']) && $av['type'] == 'video'){
			// Rewrite for Alias
			if($this->cfg['vhost_alias'] == true && substr($av['path'],0,4) != 'http'){
				$av['path'] = $this->func->windows_path_alias($av['path'],'video');
			}
			// Clean player
			$av['player'] = $this->func->clean_player($av['player']);
			if($av['text'] != ''){
				$av['text'] = '<div style="margin-bottom:10px;">'.nl2br($av['text']).'</div>';
			}
			$av['duration'] = $this->skin->seconds2human($av['duration']);
$output .= <<<E
	<div class="msg-video-box">
	    <span class="label label-default msg-video-duration px-2 py-1">{$av['duration']}</span>
	    <a class="various fancybox" href="javascript:;" onclick="javascript:fbox_video_global('{$av['player']}',1);" data-title-id="title-{$av['attach_id']}" style="background-image:url('{$av['path']}');"></a>
	</div>
	<h6 class="msg-video-header" style="max-width:400px;">{$av['title']}</h6>
	<div id="title-{$av['attach_id']}" style="display:none;">
	    {$av['text']}
	    <div class="expander" onClick="expand_desc();">показать</div>
	</div>
E;
		}
		return $output;
	} // end of attach video
	
	// Type - Sticker
	function dlg_attach_sticker($av){
		$output = '';
		if((isset($av['type']) && $av['type'] == 'sticker')){
$output .= <<<E
    <div><img style="width:128px;height:128px;" src="data/stickers/{$av['path']}"></div>
E;
		} // end of STICKER
		return $output;
	}
}

?>