<?php
include('./source/plugin/iltc_open/public.php');

$result = array();

switch($_G['gp_action']){
	case 'view':
		if(isset($_G['gp_tid']) && isset($_G['gp_aid'])){
			$tid = $_G['gp_tid'];
			$aid = $_G['gp_aid'];
		}else showError('Missing Parameter');
	
		$tableid = $tid % 10;
	
		$sql = "SELECT dateline, filename, filesize, attachment, description, isimage, thumb, picid FROM "
    		.DB::table('forum_attachment_'.$tableid)." WHERE aid = $aid AND tid = $tid LIMIT 1";
		
		$query = DB::query($sql);
		$result['data'] = DB::fetch($query);
		
		if($result['data'] != false){
			$result['data']['filename'] = iconv("GBK", "UTF-8", $result['data']['filename']);
			$result['data']['description'] = iconv("GBK", "UTF-8", $result['data']['description']);
			$result['data']['attachment'] = $_G['setting']['attachurl'].'forum/'.$result['data']['attachment'];
		}else showError('No Attachment Found');
		showResult($result, 'success');
		break;
	
	default:
		showError('Missing Parameter');
}