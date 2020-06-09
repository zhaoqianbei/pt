<?php
require "include/bittorrent.php";
dbconn();

// ---------------- start: ajax ---------------- //
if(isset($_GET['ajax'])) {
	//Send some headers to keep the user's browser from caching the response.
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
	header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
	header("Cache-Control: no-cache, must-revalidate" ); 
	header("Pragma: no-cache" );
	header("Content-Type: text/plain; charset=utf-8");
	
	$torrentid = 0 + $_GET['torrentid'];
	if(isset($CURUSER))
	{
		$res_rss = sql_query("SELECT enable FROM torrents_myrss WHERE torrent_id=" . sqlesc($torrentid) . " AND user_id=" . sqlesc($CURUSER[id])) or sqlerr(__FILE__,__LINE__);
		if (mysql_num_rows($res_rss) == 1){
			if(mysql_result($res_rss, 0)){
				sql_query("DELETE from torrents_myrss where torrent_id=" . sqlesc($torrentid) . " AND user_id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__,__LINE__);
				$Cache->delete_value('user_'.$CURUSER['id'].'_rssdown_array');
				echo "deleted";
			}else{
				sql_query("update torrents_myrss set enable = 1 where torrent_id=" . sqlesc($torrentid) . " AND user_id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__,__LINE__);
				$Cache->delete_value('user_'.$CURUSER['id'].'_rssdown_array');
				echo "added";
			}
		}else{
			sql_query('insert into torrents_myrss (user_id,torrent_id) values('.sqlesc($CURUSER['id']).','.sqlesc($torrentid).') ');
			if(mysql_affected_rows() != 1){
				sql_query('update torrents_myrss set enable = 1 where user_id = '.sqlesc($CURUSER['id']).' and torrent_id = '.sqlesc($torrentid).' ') or sqlerr(__FILE__,__LINE__);
			}
			$Cache->delete_value('user_'.$CURUSER['id'].'_rssdown_array');
			echo "added";
		}
	}
	else echo "failed";
	
	exit;
}
// ----------------- end: ajax ----------------- //

require_once(get_langfile_path());
loggedinorreturn();
parked();

function bark($msg) {
	global $lang_myrss;
	stderr($lang_myrss['head_message'], $msg, false);
	exit;
}


$user_id = $CURUSER['id'];
$goto = '&nbsp;&nbsp;&nbsp;<a href="myrss.php">['.$CURUSER['username'].$lang_myrss['text_myrss_down'].']</a>';
if(isset($_GET['add'])){// Add torrent
	$add_id = 0 + $_GET['add'];
	
	$res = sql_query('select 1 from torrents where id = '.sqlesc($add_id)) or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) == 1){
		sql_query('insert into torrents_myrss (user_id,torrent_id) values('.sqlesc($user_id).','.sqlesc($add_id).') ');
		if(mysql_affected_rows() != 1){
			sql_query('update torrents_myrss set enable = 1 where user_id = '.sqlesc($user_id).' and torrent_id = '.sqlesc($add_id).' ');
			if(0 == mysql_affected_rows()){
				bark('torrent_id = '. $add_id . $lang_myrss['is_already_added'].$goto);
			}
		}
		$Cache->delete_value('user_'.$CURUSER['id'].'_rssdown_array');
		bark($lang_myrss['msg_torrent_added'].$goto);
	}else{
		bark('torrent_id = '. $add_id . ' is not exists.'.$goto);
	}
}elseif(isset($_GET['del'])){// Del torrent
	$del_id = 0 + $_GET['del'];
	
	sql_query('update torrents_myrss set enable = 0 where user_id = '.sqlesc($user_id).' and torrent_id = '.sqlesc($del_id).' ') or sqlerr(__FILE__, __LINE__);
	if(mysql_affected_rows() == 1){
		$Cache->delete_value('user_'.$CURUSER['id'].'_rssdown_array');
		bark($lang_myrss['msg_torrent_deleted'].$goto);
	}else{
		bark('torrent_id = '. $del_id . ' is not exists.'.$goto);
	}
}elseif(isset($_GET['batch'])){ // Batch Del
	if(is_array($_POST['rss'])){
		$UPDATES = array();
		foreach($_POST['rss'] as $del_id){
			$del_id = (int) $del_id;
			if($del_id > 0) $UPDATES[] = "torrent_id = $del_id";
		}
		if($UPDATES){
			$user_id = (int) $user_id;
			if(isset($_POST['remove'])){
				sql_query(sprintf('DELETE FROM torrents_myrss WHERE user_id = %d AND (%s)',$user_id,implode(' OR ',$UPDATES))) or sqlerr(__FILE__,__LINE__);
			}else{			
				sql_query(sprintf('UPDATE torrents_myrss SET enable = 0 WHERE user_id = %d AND (%s)',$user_id,implode(' OR ',$UPDATES))) or sqlerr(__FILE__,__LINE__);
			}
			if(mysql_affected_rows() > 0){
				$Cache->delete_value('user_'.$CURUSER['id'].'_rssdown_array');
				if(isset($_POST['remove'])){
					bark($lang_myrss['msg_torrent_deleted'].$goto);
				}else{
					bark($lang_myrss['msg_torrent_disabled'].$goto);
				}
			}else{
				bark('no record disabled.');
			}
		}else{
			bark($lang_myrss['msg_no_selected'].$goto);
		}
	}else{
		bark($lang_myrss['msg_no_selected'].$goto);
	}
}

// main body  -----------------------------------------------------------------
stdhead('Download Basket');

// ------------- start: My RSS list ------------------//
echo "<h1 align=\"center\">" . get_username($CURUSER['id']) . $lang_myrss['text_myrss_down'] . "</h1>";

echo $lang_myrss['text_rss_address'];
$passkey = $CURUSER['passkey'];
echo '<input type="text" onmouseover="this.select()" size="120" value="http://'.$BASEURL.'/torrentrss.php?myrss=1&linktype=dl&uid='.$user_id.'&passkey='.$passkey.'">';
echo ' <a style="color:blue; font-weight:bold;" href="#">'.$lang_myrss['text_how_to'].'</a>';

$sql = "SELECT count(1) FROM torrents_myrss t2 INNER JOIN torrents t1 ON t2.torrent_id = t1.id WHERE t2.user_id = $user_id";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$count = mysql_result($res, 0);
	
if ($CURUSER["torrentsperpage"])
  $torrentsperpage = (int)$CURUSER["torrentsperpage"];
elseif ($torrentsperpage_main)
  $torrentsperpage = $torrentsperpage_main;
else $torrentsperpage = 50;

if ($count){
	if ($addparam != ""){
		if ($pagerlink != ""){
			if ($addparam{strlen($addparam)-1} != ";"){ // & = &amp;
				$addparam = $addparam . "&" . $pagerlink;
			}else{
				$addparam = $addparam . $pagerlink;
			}
		}
	}else{
		$addparam = $pagerlink;
	}

	list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "?" . $addparam);
	
	$orderby = "ORDER BY t2.enable DESC, t1.pos_state DESC";
	$sql = "SELECT t1.* FROM torrents_myrss t2 INNER JOIN torrents t1 ON t2.torrent_id = t1.id WHERE t2.user_id = $user_id";
	$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
	
	print($pagertop);
	torrenttable($res, 'rssdown');
	print($pagerbottom);
}else{
	unset($res);
	stdmsg($lang_myrss['std_nothing_found'],$lang_myrss['std_no_active_torrents']);
}
            
// -------------- end: My RSS list -------------------//

stdfoot();
