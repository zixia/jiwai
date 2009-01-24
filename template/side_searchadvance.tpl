<!--${
	$ovalue = array(
		'web' => '网页',
		'qq' => 'QQ',
		'msn' => 'MSN',
		'gtalk' => 'GTalk',
		'skype' => 'Skype',
		'wap' => 'WAP',
		'fetion' => '飞信',
		'sms' => '手机短信',
	);
}-->
<div class="side1">
	<div class="pagetitle">
		<h3>高级搜索...</h3>
	</div>
	<dl class="more_search">
		<form action="/wo/search/statuses" method="POST">
		<dt>关键字：</dt><dd><input type="text" name="q" value="{$_GET['q']}" class="ch_w" /></dd>
		<dt>搜索范围：<br>(多个用户名请用逗号分隔)</dt><dd><textarea name="u"  class="ch_w" rows="5">{$_GET['u']}</textarea></dd>
		<dt>叽歪方式：</dt><dd><select name="s" class="ch_w">${JWUtility::Option($ovalue, $_GET['s'], '全部');}</select></dd>
		<dt>&nbsp;</dt><dd><input type="submit" value="搜索" /></dd>
		</form>
	</dl>
</div>
<div class="clear"></div>
