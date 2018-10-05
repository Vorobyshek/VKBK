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

$dialogs = array(
	'min' => 0,
	'max' => $lc['dialogs']
);

$count = 100;

print <<<E
<div class="nav-scroller bg-white box-shadow mb-4" style="position:relative;">
    <nav class="nav nav-underline">
		<span class="nav-link active"><i class="fa fa-sync"></i> Синхронизация - Сообщения</span>
    </nav>
</div>
<div class="container">
	<div class="col-sm-12 my-3 p-3 bg-white rounded box-shadow">
		<div class="row">
			<div class="col-sm-10">
				<div class="text-center"><h6 class="border-bottom border-gray pb-2 mb-0">Диалоги</h6></div>
				<div class="progress" style="height:40px;">
					<div class="progress-bar progress-bar-striped bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="d-total"></div>
				</div>
				<div class="text-center"><h6 class="border-bottom border-gray pb-2 pt-2 mb-0">Сообщения</h6></div>
					<div class="progress" style="height:40px;">
						<div class="progress-bar progress-bar-striped bg-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="m-total"></div>
					</div>
				</div>
				<div class="col-sm-2 text-center">
					<div id="sync-ssp"><i class="far fa-play-circle"></i></div>
				</div>
		</div>
	</div>
	<div class="col-sm-12 my-3 p-3 bg-white rounded box-shadow">
		<div class="text-center"><h6 class="border-bottom border-gray pb-2 mb-0">Очередь</h6></div>
		<div class="table-responsive" style="overflow-x:hidden;max-height:300px;">
			<div id="d-log"></div>
		</div>
	</div>
    <div class="table-responsive">
        <table class="table table-striped">
        </table>
    </div>
	<div class="row">
		<input type="hidden" id="d-status" size="10" value="start" />
		<input type="hidden" id="d-min" size="5" value="{$dialogs['min']}" />
		<input type="hidden" id="d-max" size="5" value="{$dialogs['max']}" />
		<input type="hidden" id="m-min" size="5" value="0" />
		<input type="hidden" id="m-max" size="5" value="0" />
	</div>
</div>
E;

$ex_bot = <<<E
<script type="text/javascript">
// Default options
var syncStart   = '<i class="far fa-play-circle text-primary"></i>';		// Default
var syncPause   = '<i class="far fa-pause-circle text-secondary"></i>';
var syncError   = '<i class="far fa-times-circle text-danger"></i>';		// Error
var syncSuccess = '<i class="far fa-check-circle text-success"></i>';		// Finished sync
var syncProceed = '<i class="far fa-arrow-alt-circle-right"></i>';			// Between sync modes
var syncProcess = '<i class="fas fa-spinner fa-pulse text-secondary"></i>';	// Sync working
    syncProcess += '<span>{$cfg['sync_dialog_next_cd']}</span>';

var dstatus = 'start';  // [start|pause|process|success|error]
var sync_do = 'none';	// [dlg|msg|next|none]
var dmin = 0;
var dmax = {$dialogs['max']};
var mmin = 0;
var mmax = 0;
var sync_ssp = jQuery("#sync-ssp");		// StartStopProceed
var sync_status  = jQuery("#d-status");	// Sync status

var spin_counter = null;
var spin_count = {$cfg['sync_dialog_next_cd']};

$(document).ready(function() {
	
	// Hash URL commands
	urlCommands.bind('dmin', function(id) { dmin = id; jQuery("#d-min").val(id); });
	urlCommands.bind('mmin', function(id) { mmin = id; jQuery("#m-min").val(id); });
	urlCommands.bind('mmax', function(id) { mmax = id; jQuery("#m-max").val(id); });
	urlCommands.bind('dstatus', function(id) { dstatus = id; jQuery("#d-status").val(id); });
	
	// If filter command changed, update url and reload data with new filter
	jQuery("#d-min").on('change', function(){ urlCommands.urlPush({dmin:this.value}); });
	jQuery("#m-min").on('change', function(){ urlCommands.urlPush({mmin:this.value}); });
	jQuery("#m-max").on('change', function(){ urlCommands.urlPush({mmax:this.value}); });
	
	// Basic control StartStopProceed bind
	jQuery("#sync-ssp").on('click', function(){
		console.log('SSP pressed - sync_do: '+sync_do+' status: '+sync_status.val());
		if(sync_status.val() == 'start' && sync_do == 'none'){
			// Phase 1: Set control style to 'process' and do dialogs check
			sync_ssp.html(syncProcess); sync_set_status('process');
			sync_do = 'dlg'; process_query('/ajax/sync-message.php?do=dlg&offset=0','dlg');
			console.log('Executed sync [Start&None]: '+sync_do);
		}
		if(sync_status.val() == 'pause' && sync_do == 'next'){
			// Phase 2: Control on 'pause' after dialogs check
			// And we ready for the next step - messages check
			sync_ssp.html(syncProcess); sync_set_status('process');
			sync_do = 'msg'; process_query('/ajax/sync-message.php?do=next&offset=0','msg');
			console.log('Executed sync [Pause&Next]: '+sync_do);
		}
	});
	
	sync_check();
	
});

/*
	Function process_query
	In:
	uri - query url		(string)
	s   - sync state	(string)
*/
function process_query(uri,s){
	if(s != ''){ sync_do = s; }
	console.log('Processing query: '+sync_do+' URL: '+uri);
	if(sync_do == 'dlg' || sync_do == 'msg'){
		console.log('Processing start: '+sync_do);
		jQuery.ajax({
			async : false, method : "GET", url : uri
		}).done( function(data){
			var r = jQuery.parseJSON(data);
			// Show log
			jQuery.each( r.response.msg, function( i, item ) {
				jQuery(item).prependTo("#d-log");
			});
			// Update dialogs count
			if(r.error == false){
				if(r.response.done > 0){
					var qtype = (sync_do == 'dlg') ? 'd' : 'm';
					var mnext = (r.response.next_uri != '') ? 1 : 0;
					console.log('Query T: '+qtype);
					update_count(r.response.done,r.response.total,qtype,mnext);
				}
				// Check next URL and do a query if yes...
				if(r.response.next_uri != ''){
					console.log('DBG: Timeout & Reload');
					// Update countdown between requests
					spin_counter = setInterval(function(){
						spin_count = spin_count-1;
						if(spin_count <= 0) {
							// We done, set spin_count back to defined and process
							clearInterval(spin_counter);
							spin_count = {$cfg['sync_dialog_next_cd']};
							jQuery("#sync-ssp span").html('');
							process_query(r.response.next_uri,sync_do);
						} else {
							// Just another waste of one second...
							jQuery("#sync-ssp span").html(spin_count);
						}
					}, 1000);
					//setTimeout(function(){
					//	process_query(r.response.next_uri,sync_do)
					//},{$cfg['sync_dialog_next_cd']}*1000);
				}
				if(r.response.done == 0 && sync_do == 'msg' && r.response.next_uri == ''){
					if(sync_do != 'next'){
						console.log('DBG: no errors, done 0, sync MSG; Aborting.');
						sync_do = 'none'; sync_ssp.html(syncSuccess); sync_set_status('success');
					}
				}
			}
			
			if(r.error == true){
				console.log('ERROR!!!');
				jQuery(r.response.error_msg).prependTo("#d-log");
				sync_do = 'error'; sync_ssp.html(syncError); sync_set_status('error');
			}
		});
	}
}

/*
	Function: update_count
	In:
	c  - count			(int)
	tc - total count	(int)
	t  - type			(string)
	n  - next or finish	(bool)
*/
function update_count(c,tc,t,n){
	var minval = jQuery("#"+t+"-min");
	var total  = jQuery("#"+t+"-total");
	var a = Math.floor(minval.val());
	var a1 = a+c;
	console.log('UpC type: '+t+' items done: '+c+' minval: '+a);
	if(t == 'd'){
		// Check `dmax` if not set, update from response
		if(dmax == 0){ dmax = tc; total.val(tc); }
		console.log('UpC D: total items: '+dmax);
		if(a1 != dmax && a1 < dmax){
			var p = Math.floor(a1 / (dmax / 100));
			console.log('UpC D: less '+dmax+' total items: '+a1+' ('+p+'%)');
			progress_change(minval,a1,total,p);
		}
		if(a1 >= dmax){
			console.log('UpC D: more or equal '+dmax+' total items: '+a1+' (100%)');
			progress_change(minval,dmax,total,100);		
			// Dialogs sync finished, stopping task
			if(sync_do == 'dlg'){
				console.log('UpC D event finished. Setting NEXT event.');
				sync_do = 'next'; sync_ssp.html(syncPause); sync_set_status('pause');
			}
		}
	} // end D type
	if(t == 'm'){
		// Update max count for new dialog values
		if(tc > 0){ total.val(tc); mmax = tc; }
		console.log('UpC M: total items: '+mmax);
		if(a1 != mmax && a1 < mmax){
			var p = Math.floor(a1 / (mmax / 100));
			console.log('UpC M: less '+mmax+' total items: '+a1+' ('+p+'%)');
			progress_change(minval,a1,total,p);
		}
		if(a1 >= mmax){
			console.log('UpC T: more or equal '+mmax+' total items: '+a1+' (100%)');
			progress_change(minval,mmax,total,100);
			// Messages sync finished, stopping task
			if(sync_do == 'msg'){
				if(n == 1){
					console.log('UpC M event process. Setting event to NEXT.');
					sync_do = 'msg'; sync_ssp.html(syncProcess); sync_set_status('process');
				}
				if(n == 0){
					console.log('UpC M event finished. Setting event to SUCCESS.');
					sync_do = 'none'; sync_ssp.html(syncSuccess); sync_set_status('success');
				}
			}
		}
	} // end M type
}

/*
	Function progress_change
	In:
	selector - min value		(object)
	value    - current value	(object)
	total    - total selector	(selector)
	percent  - current percent	(int)
*/
function progress_change(selector,value,total,percent){
	selector.val(value);
	selector.change();
	total.css("width",percent+"%");
}

/*
	Function sync_check
	Check default values of Status from URL and update html if needed
*/
function sync_check(){
	//alert(dstatus);
	if(dstatus != 'start'){
		if(dstatus == 'pause'){ sync_ssp.html(syncPause); }
		else if(dstatus == 'process'){
			console.log(dmin);
			if(dmin == dmax){
				sync_ssp.html(syncSuccess);
				sync_set_status('success');
				update_count(dmax);
			} else {
				sync_ssp.html(syncProcess);
			}
		}
		else if(dstatus == 'success'){
			sync_ssp.html(syncSuccess);
			sync_set_status('success');
			update_count(dmax);
		}
		else { sync_ssp.html(syncError); }
	}
}

/*
	Function sync_set_status
	Update status var and URL
	In:
	v - status (string)
*/
function sync_set_status(v){
	sync_status.val(v);
	urlCommands.urlPush({dstatus:v});
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