<?php
require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if(get_user_class() < UC_ADMINISTRATOR) permissiondenied();

if ($_SERVER["REQUEST_METHOD"] == 'POST' && isset($_POST['amount'])){
	$amount = (int) $_POST['amount'];
	if($amount <= 0) stderr('错误','邀请名额错误');
	if(isset($_POST['expired']) && !empty($_POST['expired'])){
		$expired = strtotime($_POST['expired']);
		if($expired <= 0) stderr('错误','邀请有效期不正确。');
		
		if(isset($_POST['username']) && strlen($_POST['username'])){
			$res = sql_query(sprintf("SELECT `id` FROM `users` WHERE `username` = %s AND status = 'confirmed' AND enabled = 'yes'", sqlesc($_POST['username']))) or sqlerr(__FILE__,__LINE__);
		}elseif(isset($_POST['class'])){
			$classlist = implode(',', array_map('intval', $_POST['class']));
			if(empty($classlist)) stderr('错误','至少选择一个用户等级。');
			$res = sql_query("SELECT `id` FROM `users` WHERE `class` IN ($classlist) AND status = 'confirmed' AND enabled = 'yes'") or sqlerr(__FILE__,__LINE__);
		}else{
			stderr('错误', '请输入用户名或选择一个用户等级。');
		}
		
		$invites = array();
		while($user = mysql_fetch_row($res)) $users[] = $user[0];
		$i = new Invitation();
		$i->addTempInvites($users, $amount, $expired);
	}elseif(isset($_POST['permanent']) && $_POST['permanent']){
		if(isset($_POST['username']) && strlen($_POST['username'])){
			sql_query(sprintf("UPDATE `users` SET `invites` = `invites` + %u WHERE `username` = %s AND status = 'confirmed' AND enabled = 'yes'", $amount, sqlesc($_POST['username']))) or sqlerr(__FILE__,__LINE__);
		}elseif(isset($_POST['class'])){
			$classlist = implode(',', array_map('intval', $_POST['class']));
			if(empty($classlist)) stderr('错误','至少选择一个用户等级。');
			$res = sql_query(sprintf("UPDATE `users` SET `invites` = `invites` + %u WHERE `class` IN (%s) AND status = 'confirmed' AND enabled = 'yes'", $amount, $classlist)) or sqlerr(__FILE__,__LINE__);
		}else{
			stderr('错误', '请输入用户名或选择一个用户等级。');
		}
	}else{
		stderr('错误', '未设置有效期。');
	}
	header('Location: massinvite.php?done=1');
}elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['MOD'])){
	foreach($_POST['tinvite'] as $id => $qty){
		$qty = intval($qty);
		$id = intval($id);
		if($id <= 0) continue;
		if($qty > 0){
			sql_query(sprintf('UPDATE `user_invitations` SET `qty` = %u WHERE `id` = %u', $qty, $id)) or sqlerr(__FILE__, __LINE__);
		}else{
			sql_query(sprintf('DELETE FROM `user_invitations` WHERE `id` = %u', $id)) or sqlerr(__FILE__, __LINE__);
		}
	}
	if(isset($_POST['uid'])) Invitation::purgeCache(intval($_POST['uid']));
	header('Location: '. $_SERVER['HTTP_REFERER']);
}else{
	stdhead('批量邀请');
	begin_main_frame();
	begin_frame('批量邀请',true);
	if($_GET['done']) echo '<h3>批量邀请完成。</h3>';
	echo '<form action="" method="post"><table cellpadding="2"><tr><td class="colhead">用户名</td><td><input type="text" name="username" value="" style="width: 100px" /> <b>填写用户名时，用户级别不起作用</b></td></tr><tr><td class="colhead">用户级别</td><td><table><tr>';
	for($i = UC_PEASANT;$i <= UC_STAFFLEADER;$i++){
		if($i > 0 && $i % 4 == 0) echo '</tr><tr>';
		printf('<td style="border: 0"><input type="checkbox" name="class[]" value="%d" />%s</td>',$i,get_user_class_name($i,true,true,true));
	}
	echo '</tr></table></td></tr><tr><td class="colhead">邀请数量</td><td><input type="text" name="amount" value="1" /></td></tr><tr><td class="colhead">邀请有效期</td>'.
	sprintf('<td><input type="text" name="expired" value="%s 00:00:00" />', date('Y-m-d', TIMENOW + 2592000)).'<input type="checkbox" name="permanent" value="1" id="i-permanent" /><label for="i-permanent">永久有效</label><br />格式YYYY-MM-DD HH:MM:SS</td></tr><tr><td colspan="2"><input type="submit" value="执行" /></td></tr></table></form>';
	end_frame();
	end_main_frame();
	echo <<<EOT
<script>
jQuery(function($){
	$("#i-permanent").click(function(){
		$("input[name=expired]").attr("disabled", this.checked);
	});
});
</script>
EOT;
	stdfoot();
}