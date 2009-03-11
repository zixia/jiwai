<!-- header begin -->
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
			<div id="nav" class="non wht">
					<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
					<ul class="f">
							<li><a href="/" class="sel">首页</a></li>
							<li><a href="/g/">随便逛逛</a> <a href="/t/小秘密/" class="act">小秘密</a></li>
							<li class="rt"><a href="/t/帮助留言板/" title="帮助留言板">帮助</a> &nbsp; &nbsp; <a href="/wo/account/create">注册</a>  &nbsp; &nbsp;  <a href="/wo/login">登录</a></li>
					</ul>
					<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
			</div>
        </div>
	<!--{/if}-->
</div>
<!-- header end -->
