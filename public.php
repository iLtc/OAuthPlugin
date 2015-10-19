<?php
if(!defined('IN_DISCUZ')){
	showError('Access Denied');
}

define('IS_GET', $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false);
define('IS_POST', $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);

loadcache('plugin');
$config = $_G['cache']['plugin']['iltc_open'];

if(IS_POST){
	//检查授权
	if(!isset($_G['gp_access_token'])) showError("Missing Parameter");
	
	$access_info = curlPost($config['token_info'], array('access_token' => $_G['gp_access_token']));
	$access_info = json_decode($access_info, true);
	
	if($access_info['status'] != 'success') showError("Can't confirm token: " . $access_info['error']);
	unset($access_info['status']);
	
	$scope = explode(",", $access_info['scope']);
	if(!in_array('bbs', $scope)) showError('No scope');
	
	$user_info = file_get_contents($_G['siteurl'] . 'plugin.php?id=iltc_open:userinfo&uid='.$access_info['uid']);
	$user_info = json_decode($user_info, true);
	$user_info = $user_info['data'];
	$user_info['username'] = iconv("UTF-8", "GBK", $user_info['username']);
}

function showResult($result, $status){
	global $_G;
	
	$result['status'] = $status;
	
	if(isset($_G['gp_format']) && $_G['gp_format'] == 'row'){
		var_dump($result);
		exit();
	}else{
		header('Content-type: application/json');
		echo json_encode($result);
		exit();
	}
}

function showError($error){
    $result['error'] = $error;
	showResult($result, 'error');
}

/*
 * curl POST请求
 */
function curlPost($url, $post){
    $ch = curl_init();//初始化curl

    curl_setopt($ch,CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//关闭SSL验证
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);//POST数据
    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}