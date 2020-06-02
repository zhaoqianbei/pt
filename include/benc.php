<?php
set_time_limit(0);
function benc($obj) {
	if (!is_array($obj) || !isset($obj["type"]) || !isset($obj["value"]))
		return;
	$c = $obj["value"];
	switch ($obj["type"]) {
		case "string":
			return benc_str($c);
		case "integer":
			return benc_int($c);
		case "list":
			return benc_list($c);
		case "dictionary":
			return benc_dict($c);
		default:
			return;
	}
}
function benc_str($s) {
	return strlen($s) . ":$s";
}
function benc_int($i) {
	return "i" . $i . "e";
}
function benc_list($a) {
	$s = "l";
	foreach ($a as $e) {
		$s .= benc($e);
	}
	$s .= "e";
	return $s;
}
function benc_dict($d) {
	$s = "d";
	$keys = array_keys($d);
	sort($keys);
	foreach ($keys as $k) {
		$v = $d[$k];
		$s .= benc_str($k);
		$s .= benc($v);
	}
	$s .= "e";
	return $s;
}
function bdec_file($f, $ms) {
	$fp = fopen($f, "rb");
	if (!$fp)
		return;
	$e = fread($fp, $ms);
	fclose($fp);
	return bdec($e);
}
/**
 * https://tautcony.xyz/2019/08/02/high-performance-bdec-for-nexusphp/
 * Bencode decoder for NexusPHP by TautCony(https://github.com/tautcony)
 * Reference from https://github.com/OPSnet/bencode-torrent
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *
 * @param string $data
 * @param int $pos
 * @return mixed
 */
function bdec($data, &$pos = 0) {
    $is_root = $pos === 0;
    $type = $data[$pos];
    $ret = array();
    switch ($type) {
        case "d": // dict
            ++$pos;
            $dict = array();
            while ($data[$pos] !== "e") {
                $key = bdec($data, $pos);
                $value = bdec($data, $pos);
                if ($key === null || $value === null) {
                    break;
                }
                if ($key["type"] !== "string") {
                    throw new RuntimeException("Invalid key type, must be string: " . $key["type"]);
                }
                $dict[$key["value"]] = $value;
            }
            ++$pos;
            // ksort($dict);
            $ret["type"] = "dictionary";
            $ret["value"] = $dict;
            break;
        case "l": // list
            ++$pos;
            $list = array();
            while($data[$pos] !== "e") {
                $value = bdec($data, $pos);
                $list[] = $value;
            }
            ++$pos;
            $ret["type"] = "list";
            $ret["value"] = $list;
            break;
        case "i": // integer
            ++$pos;
            $digits = strpos($data, "e", $pos) - $pos;
            if ($digits < 0) {
                throw new RuntimeException("Could not fully decode integer value");
            }
            $integer = substr($data, $pos, $digits);
            if ($integer === "-0") {
                throw new RuntimeException("Cannot have integer value -0");
            }
            if (preg_match("/^-?\d+$/", $integer) === 0) {
                throw new RuntimeException("Cannot have non-digit values in integer number: " . $integer);
            }
            $pos += $digits + 1;
            $ret["type"] = "integer";
            $ret["value"] = (int) $integer;
            break;
        default: // string
            $digits = strpos($data, ":", $pos) - $pos;
            if ($digits < 0) {
                throw new RuntimeException("Could not fully decode string value's length");
            }
            $len = (int) substr($data, $pos, $digits);
            $pos += $digits + 1; // $digits + len(":")
            if (strlen($data) < $pos + $len) {
                throw new RuntimeException("Could not fully decode string value");
            }
            $ret["type"] = "string";
            $ret["value"] = substr($data, $pos, $len);
            $pos += $len;
            break;
    }
    if ($is_root && $pos !== strlen($data)) {
        throw new RuntimeException("Could not fully decode bencode string");
    }
    return $ret;
}
function bdec_list($s) {
	if ($s[0] != "l")
		return;
	$sl = strlen($s);
	$i = 1;
	$v = array();
	$ss = "l";
	for (;;) {
		if ($i >= $sl)
			return;
		if ($s[$i] == "e")
			break;
		$ret = bdec(substr($s, $i));
		if (!isset($ret) || !is_array($ret))
			return;
		$v[] = $ret;
		$i += $ret["strlen"];
		$ss .= $ret["string"];
	}
	$ss .= "e";
	return array('type' => "list", 'value' => $v, 'strlen' => strlen($ss), 'string' => $ss);
}
function bdec_dict($s) {
	if ($s[0] != "d")
		return;
	$sl = strlen($s);
	$i = 1;
	$v = array();
	$ss = "d";
	for (;;) {
		if ($i >= $sl)
			return;
		if ($s[$i] == "e")
			break;
		$ret = bdec(substr($s, $i));
		if (!isset($ret) || !is_array($ret) || $ret["type"] != "string")
			return;
		$k = $ret["value"];
		$i += $ret["strlen"];
		$ss .= $ret["string"];
		if ($i >= $sl)
			return;
		$ret = bdec(substr($s, $i));
		if (!isset($ret) || !is_array($ret))
			return;
		$v[$k] = $ret;
		$i += $ret["strlen"];
		$ss .= $ret["string"];
	}
	$ss .= "e";
	return array('type' => "dictionary", 'value' => $v, 'strlen' => strlen($ss), 'string' => $ss);
}
?>