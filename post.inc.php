<?php
include('./source/plugin/iltc_open/public.php');

if(isset($_G['gp_tid'])) $tid = $_G['gp_tid'];
else showError('Missing Parameter');

//TODO:Chech whether user is allowed to view this threads.

//Get Forum
$query = DB::query("SELECT author, authorid, subject, dateline, views, replies FROM ".DB::table('forum_thread')." WHERE tid = $tid LIMIT 1");
$result['thread'] = DB::fetch($query);

if($result['thread'] == false) showError('No thread Found', 'error');

$result['thread']['author'] = iconv("GBK", "UTF-8", $result['thread']['author']);
$result['thread']['subject'] = iconv("GBK", "UTF-8", $result['thread']['subject']);

$page = isset($_G['gp_page']) ? $_G['gp_page'] : 1;
$limit = isset($_G['gp_limit']) ? $_G['gp_limit'] : 20;

$sql = "SELECT pid, first, author, authorid, subject, dateline, message FROM "
	.DB::table('forum_post')." WHERE tid = $tid AND invisible > 0 ORDER BY pid LIMIT ".(($page - 1) * $limit).", $limit";

$query = DB::query($sql);

if(($temp = DB::fetch($query)) == false) showError('No Post Found', 'error');

do {
	$temp['author'] = iconv("GBK", "UTF-8", $temp['author']);
	$temp['subject'] = iconv("GBK", "UTF-8", $temp['subject']);
	$temp['message'] = iconv("GBK", "UTF-8", $temp['message']);
    $result['posts'][] = $temp;
} while($temp = DB::fetch($query));

showResult($result, 'success');