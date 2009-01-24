<!--${$iconurl=JWTemplate::GetAssetUrl('/images/img.gif');}-->
<!--${$whom=$g_page_on?'此人':'自己';}-->
<ul class="jsbor txt_r">
	<form action="/wo/search/statuses" method="post" id="searchForm">
		<input type="hidden" id="InUser" name="u" value="{$g_page_user['nameScreen']}"/>
		<li class="jsbtn rt">
			<div class="at"></div><div class="bt"></div>
			<div id="seni_btn" class="tt">
				<a href="javascript:;" onClick="JWSsearch.click(event)" class="po_d_b"><img src="{$iconurl}" width="14" height="14" /></a>
				<input type="button" value="搜索" onClick="JWSsearch.toSearch()" />
			</div>
			<ul id="othSh">
				<li class="no"><a href="javascript:;" rel="搜索大家的叽歪">大家的</a></li>
				<li><a href="javascript:;" rel="搜索{$whom}的叽歪">{$whom}的</a></li>
				<li class="bt"></li><li class="at"></li>
			</ul>
			<div class="bt"></div><div class="at"></div>
		</li>
		<li class="gra_input">
			<input id="searchType" type="hidden" name="scope" value="0" />
			<input id="sValue" type="hidden" value="搜索大家的叽歪" />
			<input id="jwssch" type="text" name="q" value="搜索大家的叽歪" onFocus="clearValue(this)" onBlur="searchValue(this,$('sValue').value)" /> &nbsp;
		</li>
	</form>
</ul>
