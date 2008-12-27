<div class="side1">
	<div id="search" class="mar_b50">
		<div class="gra_input mar_b8">
		<form id="f2" target="_blank" name="f2" method="get" action="http://jiwai.com/wo/search/users" onsubmit="if($('search_user').value=='名字，Email，QQ号码，MSN帐号等') {alert('请输入查找内容');return false;}">
			<input type="text" id="search_user" onblur="searchValue(this,'名字，QQ，MSN，Email...')" onfocus="clearValue(this)" value="名字，QQ，MSN，Email..." name="q" />
			<input type="submit" value="谁在叽歪" class="def_btn"/>
		</form>
		</div>
		<div class="f_gra">输入朋友的用户名、Email、QQ号码、MSN账号、位置等进行搜索。</div>
	</div>
	<div class="side3">
		<ul class="h_2em">
			<li><h4><b>1、</b>${$windex==1?'':'<a href="/wo/wizard/1">'}手机写博客，发照片${$windex==1?'':'</a>'}</h4></li>
			<li><h4><b>2、</b>${$windex==2?'':'<a href="/wo/wizard/2">'}QQ与MSN,Gtalk聊天${$windex==2?'':'</a>'}</h4></li>
			<li><h4><b>3、</b>${$windex==3?'':'<a href="/wo/wizard/3">'}带个小跟班、小帮手${$windex==3?'':'</a>'}</h4></li>
			<li><h4><b>4、</b>${$windex==4?'':'<a href="/wo/wizard/4">'}密切关注活动现场${$windex==4?'':'</a>'}</h4></li>
		</ul>
	</div>
</div>
