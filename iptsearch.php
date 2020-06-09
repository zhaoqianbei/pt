<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < $userprofile_class) permissiondenied();

$ip = isset($_GET['ip']) ? $_GET['ip'] : false;
$type = isset($_GET['type']) && in_array($_GET['type'], array('active', 'all')) ? $_GET['type'] : 'active';

stdhead('Tracker IP 访问历史');
begin_main_frame();
echo '<h1 align="center">搜索Tracker IP历史</h1><form action="" method="get">';
printf(<<<EOT
<table align="center" border="1" cellspacing="0" width="115" cellpadding="5">
<tbody><tr><td class="rowhead nowrap" valign="top" align="right">IP匹配<font color="red">*</font></td><td class="rowfollow" valign="top" align="left"><input type="text" name="ip" size="40" value="%s"></td></tr>
<tr><td class="rowhead nowrap" valign="top" align="right"><nobr>状态</nobr></td><td class="rowfollow" valign="top" align="left"><input type="radio" name="type" value="active" id="type-active"%s /><label for="type-active">活动中</label><input type="radio" name="type" value="all" id="type-all"%s /><label for="type-all">全部</label></td></tr>
<tr><td align="right" colspan="2"><input type="submit" value="搜"></td></tr></tbody></table>
EOT
, htmlspecialchars($ip), $type == 'active' ? ' checked' : '', $type == 'all' ? ' checked' : '');
echo '</form>';
if($ip){
	if($type == 'active'){
		$table = 'peers';
	}elseif($type == 'all'){
		$table = 'iplog_announce';
	}
	$where = sprintf(" WHERE %s.`ip` LIKE '%s%%'", $table, mysql_real_escape_string($ip));
	if($type == 'active'){
		$sql = "SELECT u.*, peers.`ip` AS ipt, MIN(peers.`started`) AS `first`, MAX(peers.`last_action`) AS `last`, COUNT(peers.id) AS hits FROM `peers` LEFT JOIN `users` AS u ON peers.`userid` = u.`id` $where GROUP BY peers.userid";
	}elseif($type == 'all'){
		$sql = "SELECT u.*, iplog_announce.`ip` AS ipt, iplog_announce.`first`, iplog_announce.`last`, iplog_announce.`hits` FROM `iplog_announce` LEFT JOIN `users` AS u ON u.`id` = iplog_announce.`userid` $where";
	}
	$count = get_row_count($table, $where);
	list($pagertop, $pagerbottom, $limit) = pager(50, $count, sprintf('?ip=%s&type=%s&', urlencode($ip), urlencode($type)));
	$res = sql_query("$sql $limit") or sqlerr(__FILE__, __LINE__);
	echo $pagertop.'<table width="940" border="1" cellspacing="0" cellpadding="5" align="center">';
	echo EchoRow('colhead', '用户名', '注册时间', '最后访问', '等级', '上传量', '下载量', 'IP', '首次连接', '最近连接', '连接数');
	while($row = mysql_fetch_assoc($res)){
		echo EchoRow('rowfollow', get_username($row['id']), gettime($row['added']), gettime($row['last_access']), get_user_class_name($row['class'], false, true, true), mksize($row['uploaded']), mksize($row['downloaded']), htmlspecialchars($row['ipt']), gettime($row['first']), gettime($row['last']), number_format($row['hits']));
	}
	
	echo '</table>', $pagerbottom;
}
end_main_frame();
stdfoot();