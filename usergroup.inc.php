<?php
if(!defined('IN_DISCUZ')){
	$result = array(
		'status' => 'fail',
		'error' => 'Access Denied'
	);
    exit(json_encode($result));
}

$result = array();
$i = 0;
$query = DB::query("SELECT groupid, radminid, type, grouptitle FROM ".DB::table('common_usergroup')." ORDER BY creditshigher");
while($temp = DB::fetch($query)) {
    $result['data'][$i++] = $temp;
    $result['status'] = 'success';
}

echo(json_encode($result));