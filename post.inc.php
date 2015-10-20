<?php
include('./source/plugin/iltc_open/public.php');

if(isset($_G['gp_tid'])) $tid = $_G['gp_tid'];
else showError('Missing Parameter');

//TODO:Chech whether user is allowed to view this threads.

//Get Forum
$query = DB::query("SELECT fid, author, authorid, subject, dateline, views, replies FROM ".DB::table('forum_thread')." WHERE tid = $tid LIMIT 1");
$result['thread'] = DB::fetch($query);

if($result['thread'] == false) showError('No thread Found', 'error');

$result['thread']['author'] = iconv("GBK", "UTF-8", $result['thread']['author']);
$result['thread']['subject'] = iconv("GBK", "UTF-8", $result['thread']['subject']);

if(IS_GET){
	$page = isset($_G['gp_page']) ? $_G['gp_page'] : 1;
	$limit = isset($_G['gp_limit']) ? $_G['gp_limit'] : 20;
	
	$sql = "SELECT pid, first, author, authorid, subject, dateline, message FROM "
		.DB::table('forum_post')." WHERE tid = $tid AND invisible >= 0 ORDER BY pid LIMIT ".(($page - 1) * $limit).", $limit";
	
	$query = DB::query($sql);
	
	if(($temp = DB::fetch($query)) == false) showError('No Post Found', 'error');
	
	do {
		$temp['author'] = iconv("GBK", "UTF-8", $temp['author']);
		$temp['subject'] = iconv("GBK", "UTF-8", $temp['subject']);
		$temp['message'] = iconv("GBK", "UTF-8", $temp['message']);
	    $result['posts'][] = $temp;
	} while($temp = DB::fetch($query));
	
	//更新访问量
	C::t('forum_thread')->update($tid, array('views' => $result['thread']['views']+1));
}else{
	if(!isset($_G['gp_message'])) showError('Missing Parameter');
	
	$pid = C::t('forum_post')->count_table('tid:'.$tid) + 1;
	
	C::t('forum_post')->insert('tid:'.$tid, array(
		'pid' => $pid,
		'tid' => $tid,
		'fid' => $result['thread']['fid'],
		'author' => $user_info['username'],
		'authorid' => $user_info['uid'],
		'dateline' => TIMESTAMP,
		'message' => iconv("UTF-8", "GBK", $_G['gp_message']),
		'useip' => $_G['clientip'],
		'port' => $_G['remoteport'],
		'position' => $result['thread']['replies'] + 2 //主楼占一个位置
	));
	
	//更新回帖信息
	C::t('forum_thread')->update($tid, array(
		'replies' => $result['thread']['replies']+1,
		'lastpost' => TIMESTAMP,
		'lastposter' => $user_info['username']
	));
	
	$result['newpost'] = array(
		'pid' => $pid,
		'message' => $_G['gp_message']
	);
}

showResult($result, 'success');