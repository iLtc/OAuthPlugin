<?php
include('./source/plugin/iltc_open/public.php');

$result = array();

$wzkk_xshow = $_G['cache']['plugin']['wzkk_xshow'];

$datapic = array();
$fids = $wzkk_xshow['only_pic'] ? 'AND t.fid IN('.$wzkk_xshow['only_pic'].')' : '';
$PicNums = $wzkk_xshow['pic_schnum'];
/**ljm�޸�ͼƬ��ʾ����ֹ��ʾ���԰���ͼƬ*/	
//echo $wzkk_xshow['not_show'];
$notshow = $wzkk_xshow['not_show'] ? 'AND t.fid not IN('.$wzkk_xshow['not_show'].')' : '';//����ʾ�İ��  all
//���뵽�����sql��
/**end ljm*/
if ($wzkk_xshow['pic_transfer'] == 1) { 
	$orderby = 'tid'; 
} else if ($wzkk_xshow['pic_transfer'] == 2) { 
	$orderby = 'tid'; 
} else { 
	$orderby = 'rand'; 
}
$orderby = $orderby != 'rand' ? 'attach.'.$orderby : 'rand()';
$query = DB::query("SELECT attach.attachment,t.tid, t.fid, t.subject FROM ".DB::table('forum_threadimage')." attach INNER JOIN ".DB::table('forum_thread')." t ON t.tid=attach.tid WHERE t.isgroup=0 AND t.displayorder>=0 $fids $notshow GROUP BY attach.tid ORDER BY $orderby DESC LIMIT 0, ".$PicNums);
while($pic = DB::fetch($query)) {
	$pics['picpics'] = $_G['setting']['attachurl'].'forum/'.$pic['attachment'];
	$pics['piclinks'] = 'forum.php?mod=viewthread%26tid='.$pic['tid'];
	$pics['pictexts'] = str_replace('\'', ' ',$pic['subject']);
	$pics['attaid'] = $pic['aid'];
	$datapic[] = $pics;
}

var_dump($wzkk_xshow, $query, $datapic);