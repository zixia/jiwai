<!--${$shortcutnum=0; $shortcut = $shortcut; settype( $shortcut, 'array' )}-->

<!--{if in_array( 'myfriends', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/wo/friends')}">我的朋友({$friendsNum})</a><br/>
<!--{/if}-->

<!--{if in_array( 'myfollowers', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/wo/followers')}">我的粉丝({$followersNum})</a><br/>
<!--{/if}-->

<!--{if in_array( 'public_timeline', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/public_timeline/')}">去叽歪广场看看</a><br/>
<!--{/if}-->

<!--{if in_array( 'register', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/wo/account/create')}">注册叽歪用户</a><br/>
<!--{/if}-->

<!--{if in_array( 'my', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/'.$loginedUserInfo['nameScreen'].'/')}">我的档案</a><br/>
<!--{/if}-->

<!--{if in_array( 'index', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/')}">回首页</a><br/>
<!--{/if}-->

<!--{if in_array( 'logout', $shortcut ) }-->
    ${$shortcutnum++} <a href="${buildUrl('/wo/logout/')}">退出</a><br/>
<!--{/if}-->
