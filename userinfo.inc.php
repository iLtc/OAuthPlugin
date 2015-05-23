<?php
	if(!defined('IN_DISCUZ')){
		exit('Access Denied');
	}
	
	$result = array();
	$sql = "SELECT uid, username, email, adminid, groupid, extgroupids, allowadmincp, credits, newpm FROM ".DB::table('common_member')." WHERE ";
	if(isset($_G['gp_username'])){
		$sql .= "username = '".$_G['gp_username']."'";
	}elseif(isset($_G['gp_email'])){
		$sql .= "email = '".$_G['gp_email']."'";
	}else{
		$sql .= "uid = '".$_G['gp_uid']."'";
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