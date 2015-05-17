<?php
if(!defined('IN_DISCUZ')) exit('Access Denied');

//���ز������
loadcache('plugin');
$config = $_G['cache']['plugin']['iltc_open'];
$state = $_G['gp_server_state'] ? $_G['gp_server_state'] : null;
$callback = $_G['gp_callback'] ? $_G['gp_callback'] : null;

//������
if(!$state || !$callback){
    showmessage('�Ƿ�����ȱ�ٲ�����', NULL, array(), array('alert' => 'error'));
    exit;
}

//���ص���ַ
$find = array('http://', 'https://');
$config['callback'] = str_replace($find, '', $config['callback']);
$callback_temp = str_replace($find, '', $callback);
if(strpos($callback_temp, $config['callback']) !== 0){
    showmessage('�Ƿ����󣨻ص���ַ������', NULL, array(), array('alert' => 'error'));
    exit;
}

if(!$_G['uid']){ //����Ƿ��ѵ�½
    showmessage('not_loggedin', NULL, array(), array('login' => 1));
    exit;
}else{
    $data = array(
        'uid' => $_G['member']['uid'],
        'username' => iconv("GBK", "UTF-8", $_G['member']['username']),
        'email' => $_G['member']['email'],
		'state' => $state
    );
    $params['query']['token'] = authcode(json_encode($data), 'ENCODE', $config['token'], 1800);
    $url = buildUri($callback, $params);
    header("Location: " . $url);
}

function buildUri($uri, $params) {
    $parse_url = parse_url($uri);

    // Add our params to the parsed uri
    foreach ($params as $k => $v) {
        if (isset($parse_url[$k]))
            $parse_url[$k] .= "&" . http_build_query($v);
        else
            $parse_url[$k] = http_build_query($v);
    }

    // Put humpty dumpty back together
    return
        ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
        . ((isset($parse_url["user"])) ? $parse_url["user"] . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
        . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
        . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
        . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
        . ((isset($parse_url["query"])) ? "?" . $parse_url["query"] : "")
        . ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "");
}