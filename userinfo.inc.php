<?php
if(!defined('IN_DISCUZ')){
	$result = array(
		'status' => 'error',
		'error' => 'Access Denied'
	);
    exit(json_encode($result));
}

$result = array();
$sql = "SELECT member.uid, username, email, adminid, groupid, extgroupids, allowadmincp, credits, newpm, extcredits1 AS ml, extcredits2 AS sp FROM "
    .DB::table('common_member member').", ".DB::table('common_member_count count')
    ." WHERE member.uid = count.uid AND ";
if(isset($_G['gp_username'])){
    $sql .= "username = '{$_G['gp_username']}'";
}else if(isset($_G['gp_email'])){
    $sql .= "email = '{$_G['gp_email']}'";
}else if(isset($_G['gp_uid'])){
    $sql .= "member.uid = '{$_G['gp_uid']}'";
}else{
	$result = array(
		'status' => 'error',
		'error' => 'Missing Parameter'
	);
    exit(json_encode($result));
}

$sql .= " LIMIT 1";
$query = DB::query($sql);
$result['data'] = DB::fetch($query);

if($result['data'] != false){
    $result['data']['username'] = iconv("GBK", "UTF-8", $result['data']['username']);
    $result['data']['extgroupids'] = explode("\t", $result['data']['extgroupids']);
    $result['data']['avatar'] = $_G['siteurl'].'uc_server/avatar.php?uid='.$result['data']['uid'];
    $result['status'] = 'success';
}else{
    unset($result['data']);
    $result['status'] = 'fail';
    $result['error'] = 'no_user';
}

echo(json_encode($result));