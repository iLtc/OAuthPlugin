<?php
include('./source/plugin/iltc_open/public.php');

if(isset($_G['gp_fid'])) $fid = $_G['gp_fid'];
else showError('Missing Parameter');

//TODO:Chech whether user is allowed to view this threads.

//Get Forum
$query = DB::query("SELECT name, threads, posts FROM ".DB::table('forum_forum')." WHERE fid = $fid AND status = 1 LIMIT 1");
$result['forum'] = DB::fetch($query);

if($result['forum'] == false) showError('No Forum Found', 'error');

$result['forum']['name'] = iconv("GBK", "UTF-8", $result['forum']['name']);

$page = isset($_G['gp_page']) ? $_G['gp_page'] : 1;
$limit = isset($_G['gp_limit']) ? $_G['gp_limit'] : 20;

$sql = "SELECT thread.tid, thread.author, thread.authorid, thread.subject, thread.dateline, lastpost, lastposter, views, replies, message FROM "
	    .DB::table('forum_thread thread').", ".DB::table('forum_post post')
		." WHERE thread.fid = $fid AND thread.displayorder >= 0 AND thread.fid = post.fid AND post.first = 1 ORDER BY thread.tid DESC LIMIT ".(($page - 1) * $limit).", $limit";

$query = DB::query($sql);

if(($temp = DB::fetch($query)) == false) showError('No Thread Found', 'error');

do {
	$temp['author'] = iconv("GBK", "UTF-8", $temp['author']);
	$temp['subject'] = iconv("GBK", "UTF-8", $temp['subject']);
	$temp['lastposter'] = iconv("GBK", "UTF-8", $temp['lastposter']);
	$temp['message'] = iconv("GBK", "UTF-8", $temp['message']);
    $result['threads'][] = $temp;
} while($temp = DB::fetch($query));

showResult($result, 'success');