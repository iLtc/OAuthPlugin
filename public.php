<?php
if(!defined('IN_DISCUZ')){
	showError('Access Denied');
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