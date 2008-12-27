<div id="set_bor">
	<div class="mar_20">&nbsp;</div>
	<form method="post" action="/wo/openid/">
	<dl class="w1">
		<dt>你现在的OpenID为：</dt>
		<dd>
			<h2>{$urlOpenid}</h2>
		</dd>
		<dt>设置你自己的OpenID：</dt>
		<dd>
			<div><input id="user_openid" name="user[openid]" size="30" type="text" style="width:200px" /></div>
			<div><a href="http://baike.baidu.com/view/832917.html" class="f_gra_l">什么是OpenID?</a></div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" name="" value="&nbsp; 保存修改 &nbsp;" /></div>
		</dd>
	</dl>
	<div class="clear mar_b50"></div>
	</form>
	<div class="mar_b8"><h3><b>当前允许的网站...</b></h3></div>
	<div>
	<!--{foreach $trusted_sites AS $tid=>$row}-->
		<p><a href="/wo/openid/trustsite/destroy/$db_row[id]">删除</a> <a href="{$row['urlTrusted']}" target="_blank"><strong>{$row['urlTrusted']}</strong></a></p>
	<!--{/foreach}-->
	</div>
</div>
<div class="clear"></div>
