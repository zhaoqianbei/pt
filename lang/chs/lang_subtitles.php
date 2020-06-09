<?php

$lang_subtitles = array
(
	'std_error' => "错误！",
	'std_must_login_to_upload' => "必须登录后才能上传字幕",
	'head_subtitles' => "字幕区",
	'std_nothing_received' => "<b>上传失败！</b> <br /><br />没有接受到文件！选择的文件可能太大。",
	'std_subs_too_big' => "<b>上传失败！</b> <br /><br />字幕文件太大！",
	'std_wrong_subs_format' => "<b>上传失败！</b> <br /><br />我不允许保存你上传的文件:|",
	'std_file_already_exists' => "<b>上传失败！</b> <br /><br />已存在该文件<font color=red><b>",
	'std_missing_torrent_id' => "<b>上传失败！</b> <br /><br />必须填写<b>种子ID</b>！",
	'std_invalid_torrent_id' => "<b>上传失败！</b> <br /><br />种子ID无效！",
	'std_no_permission_uploading_others' => "<b>上传失败！</b> <br /><br />你所在的用户等级不能上传他人种子的字幕！",
	'std_file_same_name_exists' => "该文件名的文件已存在",
	'std_must_choose_language' => "<b>上传失败！</b> <br /><br />请选择字幕的语言！",
	'std_failed_moving_file' => "无法移动上传的文件。请将该问题反应给管理员。",
	'std_this_file' => "该文件名<font color=red><b>",
	'std_is_invalid' =>"</b></font>在文件夹中无效。",
	'text_upload_subtitles' => "上传字幕 - 总上传量",
	'text_rules' => "规则：",
	'text_rule_one' => "1.上传字幕时请先搜索字幕区里有无此字幕，请不要上传重复的字幕！",
	'text_rule_two' => "2.上传的字幕必须与视频同步（这需要你有该视频文件并检查过该字幕）！经查明或被会员检举，整个字幕时间轴有10%的部分在0.3s以上（可以明显感觉误差）的字幕将会视为不合格而被删除。",
	'text_rule_three' => "3.影视合集的字幕请打包为zip或者rar上传，不要分开来上传。",
	'text_rule_four' => "4.命名的规则：",
	'text_rule_four_one' => "示例：One.Night.In.Taipei.2015.BluRay.720p.DD5.1.x264-HDBiger.chs",
	'text_rule_four_two' => "格式说明：",
	'text_rule_four_three' => "&nbsp;&nbsp;&nbsp;&nbsp;1）字幕的命名必须与对应的种子内的视频文件名相同[原则上是不推荐视频的名字是中文的，只有一些没有英文名字的视频字幕允许使用中文]。",
	'text_rule_four_four' => "&nbsp;&nbsp;&nbsp;&nbsp;2）不同的语种需要在文件名后加入语种标识符,例如简体中文为.chs、繁体中文为.cht、英文.eng、日文.jp等等。此时，要选择与字幕对应的语言类型。",
	'text_rule_four_five' => "&nbsp;&nbsp;&nbsp;&nbsp;3）字幕为多语种的，如字幕是简英的，加入的语种标识符为.chs&eng，其余类似加入标识符。此时，选择的语言类型为Other",
	'text_rule_four_six' => "&nbsp;&nbsp;&nbsp;&nbsp;4）允许上传的字幕格式为ass/ssa/srt",
	'text_rule_four_seven' => "&nbsp;&nbsp;&nbsp;&nbsp;5）如果以前上传的字幕时间轴有不对的，你上传的字幕时proper（正确版本）或re-synced（重新校对）的字幕，请在字幕文件末尾添加[PROPER]或[R3]。示例：One.Night.In.Taipei.2015.BluRay.720p.DD5.1.x264-HDBiger.chs[PROPER]",
	'text_rule_five' => "5.上传的字幕必须符合以上所有要求，否则将会被删除，同时相应的魔力值也会被收回。对于多次上传不合格字幕（恶意上传）的会员或者恶意检举他人字幕的会员将予以警告，严重者将会被封禁账号。",
	'text_rule_six' => "6.上传一个字幕奖励25魔力值，我们欢迎并鼓励上传合格的字幕",
	'text_red_star_required' => "<p >红星号(<font color=red>*</font>)标记的区域必须填写</p>\n",
	'text_uploading_subtitles_for_torrent' => "为种子上传字幕：",
	'row_file' => "文件",
	'text_maximum_file_size' => "文件最大限制：",
	'row_torrent_id' => "种子ID",
	'text_torrent_id_note' => "(种子详情页面网址末尾的数字。<br />如&nbsp<b>http://".$BASEURL."/details.php?id=16</b>&nbsp数字<b>16</b>即种子ID)",
	'row_title' => "标题",
	'text_title_note' => "(可选，不填则使用种子文件名)",
	'row_language' => "语言",
	'select_choose_one' => "(请选择)",
	'row_show_uploader' => "匿名上传",
	'hide_uploader_note' => "不要显示我的用户名。",
	'submit_upload_file' => "上传文件",
	'submit_reset' => "重置",
	'text_sorry' => "对不起",
	'text_nothing_here' => "对不起，暂无字幕:( ",
	'submit_search' => "给我搜",
	'text_prev' => "上一页",
	'text_next' => "下一页",
	'col_lang' => "语言",
	'col_title' => "标题",
	'title_date_added' => "添加时间",
	'title_size' => "大小",
	'col_hits' => "点击",
	'col_upped_by' => "上传者",
	'text_delete' => "[删除]",
	'text_anonymous' => "<i>匿名</i>",
	'std_delete_subtitle' => "删除字幕",
	'std_delete_subtitle_note' => "你将删除该字幕。",
	'text_reason_is' => "原因：",
	'submit_confirm' => "确定",
	'col_report' => "举报",
	'title_report_subtitle' => "举报该字幕",
	'select_all_languages' => "(所有语言)",
);

?>
