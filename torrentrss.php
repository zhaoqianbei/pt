<?php
require "include/bittorrent.php";
dbconn();
function hex_esc($matches) {
	return sprintf("%02x", ord($matches[0]));
}
$dllink = false;
$passkey = $_GET['passkey'];
$where = "";
if ($passkey){
	$res = sql_query("SELECT id, enabled, parked FROM users WHERE passkey=". sqlesc($passkey)." LIMIT 1");
	$user = mysql_fetch_array($res);
	if (!$user)
		die("invalid passkey");
	elseif ($user['enabled'] == 'no' || $user['parked'] == 'yes')
		die("account disabed or parked");
	elseif ($_GET['linktype'] == 'dl')
		$dllink = true;
	$inclbookmarked=0+$_GET['inclbookmarked'];
	if($inclbookmarked == 1)
	{
		$bookmarkarray = return_torrent_bookmark_array($user['id']);
		if ($bookmarkarray){
			$whereidin = implode(",", $bookmarkarray);
			$where .= ($where ? " AND " : "") . "torrents.id IN(" . $whereidin . ")";
		}
	}
}else{
	if(isset($_GET["myrss"])){
		die('missing passkey');
	}
}
$searchstr = mysql_real_escape_string(trim($_GET["search"]));
if (empty($searchstr))
	unset($searchstr);
if (isset($searchstr)){
	$search_mode = 0 + $_GET["search_mode"];
	if (!in_array($search_mode,array(0,1,2)))
	{
		$search_mode = 0;
	}
	switch ($search_mode)
	{
		case 0:	// AND, OR
		case 1	:
			$searchstr = str_replace(".", " ", $searchstr);
			$searchstr_exploded = explode(" ", $searchstr);
			$searchstr_exploded_count= 0;
			foreach ($searchstr_exploded as $searchstr_element)
			{
				$searchstr_element = trim($searchstr_element);	// furthur trim to ensure that multi space seperated words still work
				$searchstr_exploded_count++;
				if ($searchstr_exploded_count > 10)	// maximum 10 keywords
					break;
				$like_expression_array[] = " LIKE '%" . $searchstr_element. "%'";
			}
			break;
		case 2	:	// exact
		{
			$like_expression_array[] = " LIKE '%" . $searchstr. "%'";
			break;
		}
	}

	$ANDOR = ($search_mode == 0 ? " AND " : " OR ");	// only affects mode 0 and mode 1
	foreach ($like_expression_array as &$like_expression_array_element)
		$like_expression_array_element = "(torrents.name" . $like_expression_array_element.($_GET['ismalldescr'] ? " OR torrents.small_descr". $like_expression_array_element : "").")";
	$wherea[] = implode($ANDOR, $like_expression_array);
	$where .= ($where ? " AND " : "") . implode(" AND ", $wherea);
}

$limit = "";
$startindex = 0+$_GET['startindex'];
if ($startindex)
$limit .= $startindex.", ";
$showrows = 0+$_GET['rows'];
if($showrows < 1 || $showrows > 50)
	$showrows = 10;
$limit .= $showrows;

function get_where($tablename = "sources", $itemname = "source", $getname = "sou")
{
	global $where;
	$items = searchbox_item_list($tablename);
	$whereitemina = array();
	foreach ($items as $item)
	{
		if ($_GET[$getname.$item[id]])
		{
			$whereitemina[] = $item[id];
		}
	}
	if (count($whereitemina) >= 1){
		$whereitemin = implode(",",$whereitemina);
		$where .= ($where ? " AND " : "") . $itemname." IN(" . $whereitemin . ")";
	}
}
if($enablespecial && !can_access_special()){
	$categories = searchbox_item_list('categories');
	$userdefines = [];
	foreach($categories as $category){
		$userdefine = "cat{$category['id']}";
		if($category['mode'] == $specialcatmode){
            unset($_GET[$userdefine]);
		}elseif(isset($_GET[$userdefine])){
            $userdefines = false;
		}elseif(is_array($userdefines)){
			$userdefines[] = $userdefine;
		}
	}
	if($userdefines){
		foreach($userdefines as $userdefine) $_GET[$userdefine] = 1;
	}
}
get_where("categories", "category", "cat");
get_where("sources", "source", "sou");
get_where("media", "medium", "med");
get_where("codecs", "codec", "cod");
get_where("standards", "standard", "sta");
get_where("processings", "processing", "pro");
get_where("teams", "team", "tea");
get_where("audiocodecs", "audiocodec", "aud");

if ($where){
	$where = "WHERE banned = 'no' AND visible = 'yes' AND " . $where;
}else{
	$where = "WHERE banned = 'no' AND visible = 'yes'";
}
if(isset($_GET["myrss"])){
	$query = "SELECT t.*, users.username AS username, categories.id AS cat_id, categories.name AS cat_name FROM torrents_myrss t2 INNER JOIN torrents t ON t.id = t2.torrent_id LEFT JOIN users ON t.owner = users.id LEFT JOIN categories ON t.category = categories.id $where AND t2.enable = 1 and t2.user_id = {$user['id']} ORDER BY t.added DESC LIMIT $limit";
}else{
	$query = "SELECT torrents.id, torrents.category, torrents.name, torrents.small_descr, torrents.descr, torrents.info_hash, torrents.size, torrents.added, torrents.anonymous, users.username AS username, categories.id AS cat_id, categories.name AS cat_name FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where ORDER BY torrents.added DESC LIMIT $limit";
}
$res = sql_query($query) or sqlerr(__FILE__, __LINE__);

$url = get_protocol_prefix().$BASEURL;
$year = substr($datefounded, 0, 4);
$yearfounded = ($year ? $year : 2007);
$copyright = "Copyright (c) ".$SITENAME." ".(date("Y") != $yearfounded ? $yearfounded."-" : "").date("Y").", all rights reserved";
header ("Content-type: text/xml");
print("<?xml version=\"1.0\" encoding=\"utf-8\"?>");
//The commented version passed feed validator at http://www.feedvalidator.org
/*print('
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">');*/
print('
<rss version="2.0">');
print('
	<channel>
		<title>' . addslashes($SITENAME.' Torrents'). '</title>
		<link><![CDATA[' . $url . ']]></link>
		<description><![CDATA[' . addslashes('Latest torrents from '.$SITENAME.' - '.htmlspecialchars($SLOGAN)) . ']]></description>
		<language>zh-cn</language>
		<copyright>'.$copyright.'</copyright>
		<managingEditor>'.$SITEEMAIL.' ('.$SITENAME.' Admin)</managingEditor>
		<webMaster>'.$SITEEMAIL.' ('.$SITENAME.' Webmaster)</webMaster>
		<pubDate>'.date('r').'</pubDate>
		<generator>'.PROJECTNAME.' RSS Generator</generator>
		<docs><![CDATA[http://www.rssboard.org/rss-specification]]></docs>
		<ttl>60</ttl>
		<image>
			<url><![CDATA[' . $url.'/pic/rss_logo.jpg'. ']]></url>
			<title>' . addslashes($SITENAME.' Torrents') . '</title>
			<link><![CDATA[' . $url . ']]></link>
			<width>100</width>
			<height>100</height>
			<description>' . addslashes($SITENAME.' Torrents') . '</description>
		</image>');
/*print('
		<atom:link href="'.$url.$_SERVER['REQUEST_URI'].'" rel="self" type="application/rss+xml" />');*/
print('
');
while ($row = mysql_fetch_array($res))
{
	$title = "";
	if ($row['anonymous'] == 'yes')
		$author = 'anonymous';
	else $author = $row['username'];
	$itemurl = $url."/details.php?id=".$row['id'];
	if ($dllink)
		$itemdlurl = $url."/download.php?id=".$row['id']."&amp;passkey=".rawurlencode($passkey);
	else $itemdlurl = $url."/download.php?id=".$row['id'];
	if ($_GET['icat']) $title .= "[".$row['cat_name']."]";
	$title .= $row['name'];
	if ($_GET['ismalldescr'] && $row['small_descr']) $title .= "[".$row['small_descr']."]";
	if ($_GET['isize']) $title .= "[".mksize($row['size'])."]";
	if ($_GET['iuplder']) $title .= "[".$author."]";
	$content = format_comment($row['descr'], true, false, false, false);
	print('		<item>
			<title><![CDATA['.$title.']]></title>
			<link>'.$itemurl.'</link>
			<description><![CDATA['.($dllink?'':$content).']]></description>
');
//print('			<dc:creator>'.$author.'</dc:creator>');
print('			<author>'.$author.'@'.$_SERVER['HTTP_HOST'].' ('.$author.')</author>');
print('
			<category domain="'.$url.'/torrents.php?cat='.$row['cat_id'].'">'.$row['cat_name'].'</category>
			<comments><![CDATA['.$url.'/details.php?id='.$row['id'].'&cmtpage=0#startcomments]]></comments>
			<enclosure url="'.$itemdlurl.'" length="'.$row['size'].'" type="application/x-bittorrent" />
			<guid isPermaLink="false">'.preg_replace_callback('/./s', 'hex_esc', hash_pad($row['info_hash'])).'</guid>
			<pubDate>'.date('r',strtotime($row['added'])).'</pubDate>
		</item>
');
}
print('	</channel>
</rss>');
?>
