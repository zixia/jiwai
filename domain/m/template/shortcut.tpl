<!--${$shortcut = $shortcut; settype( $shortcut, 'array' )}-->
<!--{if $shortcut }-->
<h2>叽歪直通车</h2>
<!--{/if}-->

<!--{if in_array( 'my', $shortcut ) }-->
<p>1 <a href="${buildUrl('/'.$loginedUserInfo['nameScreen'].'/')}" accesskey="1">我的档案</a></p>
<!--{/if}-->

<!--{if in_array( 'friends', $shortcut ) }-->
<p>2 <a href="${buildUrl('/wo/friends')}" accesskey="2">我的好友</a></p>
<!--{/if}-->

<!--{if in_array( '---------myfollowers', $shortcut ) }-->
<p>2 <a href="${buildUrl('/wo/followers')}" accesskey="2">我的粉丝({$followersNum})</a></p>
<!--{/if}-->

<!--{if in_array( 'public_timeline', $shortcut ) }-->
<p>3 <a href="${buildUrl('/public_timeline/')}" accesskey="3">去叽歪广场看看</a></p>
<!--{/if}-->

<!--{if in_array( '---------register', $shortcut ) }-->
<p>5 <a href="${buildUrl('/wo/account/create')}" accesskey="5">注册叽歪用户</a></p>
<!--{/if}-->

<!--{if in_array( 'message', $shortcut ) }-->
<p>8 <a href="${buildUrl('/wo/message/inbox')}" accesskey="8">悄悄话</a></p>
<!--{/if}-->

<!--{if in_array( 'index', $shortcut ) }-->
<p>9 <a href="${buildUrl('/')}" accesskey="9">回首页</a></p>
<!--{/if}-->

<!--{if in_array( 'logout', $shortcut ) }-->
<p>0 <a href="${buildUrl('/wo/logout')}" accesskey="0">退出</a></p>
<!--{/if}-->
