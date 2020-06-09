<?php

require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if(get_user_class() < UC_SYSOP) permissiondenied();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $action = $_POST['action'];
    if($action === 'start'){
        sql_query("UPDATE users SET exam_active = 1, exam_uploaded = uploaded, exam_downloaded = downloaded, exam_seedbonus = seedbonus, exam_seedtime = seedtime, exam_leechtime = leechtime WHERE exam_active = 0 AND exam_deadline = 0 AND class < " . UC_VIP . " AND donor = 'no' AND enabled = 'yes'") or sqlerr(__FILE__, __LINE__);
        $added = mysql_affected_rows();
        redirect("?added=$added");
    }elseif($action === 'remove'){
        $id = intval($_POST['id']);
        sql_query("UPDATE users SET exam_active = 0, exam_uploaded = 0, exam_downloaded = 0, exam_seedtime = 0, exam_leechtime = 0, exam_seedbonus = 0 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
        $removed = mysql_affected_rows();
        redirect("?removed=$removed");
    }
}else{
    stdhead('全体考核');
    begin_main_frame();
    begin_frame('进行中的考核');
    list($pagertop, $pagerbottom, $limit) = pager(30, $count = get_row_count('users', "WHERE exam_active = 1"), '?');
    if($count){
        $result = sql_query("SELECT id, uploaded, downloaded, seedbonus, seedtime, leechtime, exam_uploaded, exam_downloaded, exam_seedbonus, exam_seedtime, exam_leechtime FROM users WHERE exam_active = 1 $limit") or sqlerr(__FILE__, __LINE__);
        echo $pagertop;
        echo '<style>.exam </style><table width="100%" class="exam">';
        echo EchoRow('colhead', '用户', '上传增量/GB', '下载增量/GB', '魔力增量', 'SLTR', '操作');
        while($row = mysql_fetch_assoc($result)){
            $uploaded = round(($row['uploaded'] - $row['exam_uploaded']) / 1073741824, 1);
            $downloaded = round(($row['downloaded'] - $row['exam_downloaded']) / 1073741824, 1);
            $seedbonus = $row['seedbonus'] - $row['exam_seedbonus'];
            $leechDiff = $row['leechtime'] - $row['exam_leechtime'];
            $sltr = $leechDiff ? round(($row['seedtime'] - $row['exam_seedtime']) / $leechDiff, 2) : 0;
            $form = sprintf('<form action="" method="post"><input type="hidden" name="action" value="remove" /><input type="hidden" name="id" value="%u" /><button>通过考核</button></form>', $row['id']);
            echo EchoRow('rowfollow', get_username($row['id']), $uploaded, $downloaded, $seedbonus, $sltr, $form);
        }
        echo '</table>';
        echo $pagerbottom;
    }else{
        echo '<p>暂无会员参加考核</p>';
    }
    end_frame();

    begin_frame('开始考核');
    $deadline = $global_deadline_exam ? date('Y-m-d H:i:s', $global_deadline_exam) : '关闭';
    $button = $global_deadline_exam ? '<button>启动考核</button>' : '请先设置考核期限';
    echo <<<EOT
<table width="100%">
<tr>
<td class="embedded" valign="top">
<form action="" method="post">
<input type="hidden" name="action" value="start" />
<p>点击该按钮，将下列会员加入考核：</p>
<ol>
<li>没有参加任何考核</li>
<li>等级在VIP以下</li>
<li>没有黄星</li>
</ol>
$button
</form>
</td>
<td class="embedded">
<p>当前考核参数</p>
<ul>
<li>期限：{$deadline}</li>
<li>上传：{$global_upload_exam} GB</li>
<li>下载：{$global_download_exam} GB</li>
<li>魔力：{$global_bonus_exam}</li>
<li>SLTR：{$global_sltr_exam}</li>
</ul>
<form action="settings.php" method="post" target="_blank">
<input type="hidden" name="action" value="examsettings">
<input type="submit" value="修改参数">
</form>
</td>
</tr>
</table>
EOT;
;
    end_frame();

    end_main_frame();
    stdfoot();
}