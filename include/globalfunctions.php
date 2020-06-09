<?php
if(!defined('IN_TRACKER'))
die('Hacking attempt!');

function get_global_sp_state()
{
	global $Cache;
	static $global_promotion_state;
	if (!$global_promotion_state){
		if (!$global_promotion_state = $Cache->get_value('global_promotion_state')){
			$res = mysql_query("SELECT * FROM torrents_state");
			$row = mysql_fetch_assoc($res);
			$global_promotion_state = $row["global_sp_state"];
			$Cache->cache_value('global_promotion_state', $global_promotion_state, 57226);
		}
	}
	return $global_promotion_state;
}

// IP Validation
function validip($ip)
{
	if (!ip2long($ip)) //IPv6
		return true;
	if (!empty($ip) && $ip == long2ip(ip2long($ip)))
	{
		// reserved IANA IPv4 addresses
		// http://www.iana.org/assignments/ipv4-address-space
		$reserved_ips = array (
		array('192.0.2.0','192.0.2.255'),
		array('192.168.0.0','192.168.255.255'),
		array('255.255.255.0','255.255.255.255')
		);

		foreach ($reserved_ips as $r)
		{
			$min = ip2long($r[0]);
			$max = ip2long($r[1]);
			if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
		}
		return true;
	}
	else return false;
}

function getip() {
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
			$ip = getenv('HTTP_CLIENT_IP');
		} else {
			$ip = getenv('REMOTE_ADDR');
		}
	}

	return $ip;
}

function sql_query($query)
{
	global $query_name;
	$query_name[] = $query;
	return mysql_query($query);
}

function sqlesc($value) {
		$value = "'" . mysql_real_escape_string($value) . "'";
	return $value;
}

function sqlnow(){
	return sqlesc(date('Y-m-d H:i:s'));
}

function hash_pad($hash) {
    return str_pad($hash, 20);
}

function hash_where($name, $hash) {
	$shhash = preg_replace('/ *$/s', "", $hash);
	return "($name = " . sqlesc($hash) . " OR $name = " . sqlesc($shhash) . ")";
}

function is_require_hitrun($added)
{	
	global $release_days_hnr;
	if(!is_int($added)) $added = strtotime($added);
	return $added > TIMENOW - $release_days_hnr * 86400;
}

function get_category_row($catid = NULL)
{
    global $Cache;
    static $rows;
    if (!$rows && !$rows = $Cache->get_value('category_content')){
        $res = sql_query("SELECT categories.*, searchbox.name AS catmodename FROM categories LEFT JOIN searchbox ON categories.mode=searchbox.id");
        while($row = mysql_fetch_array($res)) {
            $rows[$row['id']] = $row;
        }
        $Cache->cache_value('category_content', $rows, 126400);
    }
    if ($catid) {
        return $rows[$catid];
    } else {
        return $rows;
    }
}

function can_access_special($userClass = null)
{
    global $access_secondary_zone_class;
    return ($userClass ?: get_user_class()) >= $access_secondary_zone_class;
}

function can_access_category($categoryID, $userClass = null)
{
    global $specialcatmode;
    if(!$specialcatmode) return true;
    if($category = get_category_row($categoryID)){
        return $category['mode'] == $specialcatmode ? can_access_special($userClass) : true;
    }else{
        return false;
    }
}