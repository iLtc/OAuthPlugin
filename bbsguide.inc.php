<?php
if(!defined('IN_DISCUZ')){
    exit('Access Denied');
}

$view = $_G['gp_view'];
loadcache('forum_guide');
if(!in_array($view, array('hot', 'digest', 'new', 'my', 'newthread', 'sofa'))) {
    $view = 'hot';
}
$perpage = 50;
$start = $perpage * ($_G['page'] - 1);
$data = array();

$data[$view] = get_guide_list($view, $start, $perpage);

var_dump($data);

function get_guide_list($view, $start = 0, $num = 50, $again = 0) {
    global $_G;
    $setting_guide = unserialize($_G['setting']['guide']);
    if(!in_array($view, array('hot', 'digest', 'new', 'newthread', 'sofa'))) {
        return array();
    }
    loadcache('forums');
    $cachetimelimit = ($view != 'sofa') ? 900 : 60;
    $cache = $_G['cache']['forum_guide'][$view.($view=='sofa' && $_G['fid'] ? $_G['fid'] : '')];
    if($cache && (TIMESTAMP - $cache['cachetime']) < $cachetimelimit) {
        $tids = $cache['data'];
        $threadcount = count($tids);
        $tids = array_slice($tids, $start, $num, true);
        $updatecache = false;
        if(empty($tids)) {
            return array();
        }
    } else {
        $dateline = 0;
        $maxnum = 50000;
        if($setting_guide[$view.'dt']) {
            $dateline = time() - intval($setting_guide[$view.'dt']);
        }

        if($view != 'sofa') {
            $maxtid = C::t('forum_thread')->fetch_max_tid();
            $limittid = max(0,($maxtid - $maxnum));
            if($again) {
                $limittid = max(0,($limittid - $maxnum));
            }
            $tids = array();
        }
        foreach($_G['cache']['forums'] as $fid => $forum) {
            if($forum['type'] != 'group' && $forum['status'] > 0 && !$forum['viewperm'] && !$forum['havepassword']) {
                $fids[] = $fid;
            }
        }
        if(empty($fids)) {
            return array();
        }
        if($view == 'sofa') {
            if($_GET['fid']) {
                $sofa = C::t('forum_sofa')->fetch_all_by_fid($_GET['fid'], $start, $num);
            } else {
                $sofa = C::t('forum_sofa')->range($start, $num);
                foreach($sofa as $sofatid => $sofathread) {
                    if(!in_array($sofathread, $fids)) {
                        unset($sofathread[$sofatid]);
                    }
                }
            }
            $tids = array_keys($sofa);
        }
        $updatecache = true;
    }
    $query = C::t('forum_thread')->fetch_all_for_guide($view, $limittid, $tids, $_G['setting']['heatthread']['guidelimit'], $dateline);
    $n = 0;
    foreach($query as $thread) {
        if(empty($tids) && ($thread['isgroup'] || !in_array($thread['fid'], $fids))) {
            continue;
        }
        if($thread['displayorder'] < 0) {
            continue;
        }
        $thread = guide_procthread($thread);
        $threadids[] = $thread['tid'];
        if($tids || ($n >= $start && $n < ($start + $num))) {
            $list[$thread[tid]] = $thread;
            $fids[$thread[fid]] = $thread['fid'];
        }
        $n ++;
    }
    if($limittid > $maxnum && !$again && count($list) < 50) {
        return get_guide_list($view, $start, $num, 1);
    }
    $forumnames = array();
    if($fids) {
        $forumnames = C::t('forum_forum')->fetch_all_name_by_fid($fids);
    }
    $threadlist = array();
    if($tids) {
        $threadids = array();
        foreach($tids as $key => $tid) {
            if($list[$tid]) {
                $threadlist[$key] = $list[$tid];
                $threadids[] = $tid;
            }
        }
    } else {
        $threadlist = $list;
    }
    unset($list);
    if($updatecache) {
        $threadcount = count($threadids);
        $data = array('cachetime' => TIMESTAMP, 'data' => $threadids);
        $_G['cache']['forum_guide'][$view.($view=='sofa' && $_G['fid'] ? $_G['fid'] : '')] = $data;
        savecache('forum_guide', $_G['cache']['forum_guide']);
    }
    return array('forumnames' => $forumnames, 'threadcount' => $threadcount, 'threadlist' => $threadlist);
}