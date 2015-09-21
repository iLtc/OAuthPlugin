<?php
include('./source/plugin/iltc_open/public.php');

$result = array();

//TODO: Hide some special forum

//TODO: Show sprcial forum to login user

$sql = "SELECT fid, fup, type, name, threads, posts, todayposts, yesterdayposts, lastpost FROM "
	.DB::table('forum_forum')." WHERE status = 1";
$query = DB::query($sql);

while($temp = DB::fetch($query)) {
	$temp['name'] = iconv("GBK", "UTF-8", $temp['name']);
	$temp['lastpost'] = iconv("GBK", "UTF-8", $temp['lastpost']);
    $result['data'][$i++] = $temp;
    $result['status'] = 'success';
}

showResult($result);