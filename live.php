<?php
require 'include/bittorrent.php';
dbconn(true);
if(!can_access_special()) permissiondenied();
$sectiontype = $specialcatmode;
include 'torrents.php';