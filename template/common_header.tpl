<!--${
	if ( $g_current_user_id ) {
		$user = JWUser::GetUserInfo( $g_current_user_id );
		$mcount = JWDB_Cache_Message::GetNewMessageNum($g_current_user_id);
	} else {
		$nonclass = 'non ';
	}
}-->
<script>var popKey = ${JWFarrago::GetPopkey();};</script>
<div id="header"> 
	<h2 id="logo"><a class="header" href="/"><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" alt="叽歪de" /></a></h2>
	<!--{if !$nonav}-->
	<div class="hdnav">
		<ul id="othSh" style="display:none;"><li></li></ul>
		<ul class="jsbor txt_r">
			<form action="/wo/search/statuses" method="get" id="searchForm">
			<li class="jsbtn rt">
				<div class="at"></div><div class="bt"></div>
				<div id="seni_btn" class="tt">
					<input type="button" value="搜索" onClick="JWSsearch.toSearch()" />
				</div>
				<div class="bt"></div><div class="at"></div>
			</li>
			<li class="gra_input">
				<input id="searchType" type="hidden" name="scope" value="0" />
				<input id="sValue" type="hidden" value="搜索大家的叽歪" />
				<input id="jwssch" type="text" name="q" value="搜索大家的叽歪" onFocus="clearValue(this)" onBlur="searchValue(this,$('sValue').value)" mission="JWSsearch.toSearch();" onKeyDown="JWAction.onEnterSubmit(event,this);" /> &nbsp;
			</li>
			<input type="hidden" id="InUser" name="u" value=""/>
			</form>
		</ul>
		<div id="nav" class="{$nonclass}wht">
			<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
			<ul class="f">
				<li><a href="${$g_current_user_id?'/wo/':'/'}" class="sel">首页</a></li>
				<!--{if $g_current_user_id}-->
				<li><a href="/{$user['nameUrl']}/">我的叽歪</a></li>
				<!--{/if}-->
				<li><a href="/g/">随便逛逛</a> <!--a href="javascript:;" onClick="popShow(this,event,popKey)" class="po_d_b">流行</a--></li>
				<!--{if $g_current_user_id}-->
				<li><a href="/wo/invite/">找朋友</a></li>
				<li><a href="/wo/gadget/">窗可贴</a></li>
				<li class="rt"><!--{if $mcount}--><a href="/wo/direct_messages/" title="你有{$mcount}条新消息"><img src="${JWTemplate::GetAssetUrl('/images/new_ico.gif')}" width="18" height="12" /></a><!--{/if}--><a href="/wo/account/profile">{$user['nameScreen']}的设置</a>&nbsp;&nbsp;<a href="/t/帮助留言板/">帮助</a>&nbsp;&nbsp;<a href="/wo/logout">退出</a></li>
				<!--{else}-->
				<li class="rt"><a href="/t/帮助留言板/" title="帮助留言板">帮助</a>&nbsp;&nbsp;<a href="/wo/account/create">注册</a>&nbsp;&nbsp;<a href="/wo/login">登录</a></li>
				<!--{/if}-->
			</ul>
			<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
		</div>
	</div>
	<!--{/if}-->
</div>
