<?php
if(!defined('IN_DISCUZ')){
    exit('Access Denied');
}

$view = $_G['gp_view'];
loadcache('forum_guide');
if(!in_array($view, array('hot', 'digest', 'new', 'my', 'newthread', 'sofa'))) {
    $view = 'hot';
}

$perpage = (isset($_G['perpage'])) ? $_G['perpage'] : 10;

$start = $perpage * ($_G['page'] - 1);

$data = get_guide_list($view, $start, $perpage);

$forumnames = $data['forumnames'];
foreach($forumnames as $fid => $forum){
    $data['forumnames'][$fid] = $forum['name'];
}

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

function guide_procthread($thread) {
    global $_G;
    $todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));
    $thread['lastposterenc'] = rawurlencode($thread['lastposter']);
    $thread['multipage'] = '';
    $topicposts = $thread['special'] ? $thread['replies'] : $thread['replies'] + 1;
    if($topicposts > $_G['ppp']) {
        $pagelinks = '';
        $thread['pages'] = ceil($topicposts / $_G['ppp']);
        for($i = 2; $i <= 6 && $i <= $thread['pages']; $i++) {
            $pagelinks .= "<a href=\"forum.php?mod=viewthread&tid=$thread[tid]&amp;extra=$extra&amp;page=$i\">$i</a>";
        }
        if($thread['pages'] > 6) {
            $pagelinks .= "..<a href=\"forum.php?mod=viewthread&tid=$thread[tid]&amp;extra=$extra&amp;page=$thread[pages]\">$thread[pages]</a>";
        }
        $thread['multipage'] = '&nbsp;...'.$pagelinks;
    }

    if($thread['highlight']) {
        $string = sprintf('%02d', $thread['highlight']);
        $stylestr = sprintf('%03b', $string[0]);

        $thread['highlight'] = ' style="';
        $thread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
        $thread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
        $thread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
        $thread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
        $thread['highlight'] .= '"';
    } else {
        $thread['highlight'] = '';
    }

    $thread['recommendicon'] = '';
    if(!empty($_G['setting']['recommendthread']['status']) && $thread['recommends']) {
        foreach($_G['setting']['recommendthread']['iconlevels'] as $k => $i) {
            if($thread['recommends'] > $i) {
                $thread['recommendicon'] = $k+1;
                break;
            }
        }
    }

    $thread['moved'] = $thread['heatlevel'] = $thread['new'] = 0;
    $thread['icontid'] = $thread['forumstick'] || !$thread['moved'] && $thread['isgroup'] != 1 ? $thread['tid'] : $thread['closed'];
    $thread['folder'] = 'common';
    $thread['weeknew'] = TIMESTAMP - 604800 <= $thread['dbdateline'];
    if($thread['replies'] > $thread['views']) {
        $thread['views'] = $thread['replies'];
    }
    if($_G['setting']['heatthread']['iconlevels']) {
        foreach($_G['setting']['heatthread']['iconlevels'] as $k => $i) {
            if($thread['heats'] > $i) {
                $thread['heatlevel'] = $k + 1;
                break;
            }
        }
    }
    $thread['istoday'] = $thread['dateline'] > $todaytime ? 1 : 0;
    $thread['dbdateline'] = $thread['dateline'];
    $thread['dateline'] = dgmdate($thread['dateline'], 'u', '9999', getglobal('setting/dateformat'));
    $thread['dblastpost'] = $thread['lastpost'];
    $thread['lastpost'] = dgmdate($thread['lastpost'], 'u');

    if(in_array($thread['displayorder'], array(1, 2, 3, 4))) {
        $thread['id'] = 'stickthread_'.$thread['tid'];
    } else {
        $thread['id'] = 'normalthread_'.$thread['tid'];
    }
    $thread['rushreply'] = getstatus($thread['status'], 3);
    return $thread;
}