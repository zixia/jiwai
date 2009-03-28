<!--{include header}-->
<!--{include wo/update}-->

<h2><a href="/public_timeline/">叽歪广场</a>｜热门标签</h2>
<!--{foreach $tags as $one}-->
<a href="/t/${urlEncode($one['name'])}/">{$one['name']}</a>&nbsp;
<!--{/foreach}-->
{$pageString}

<!--{include shortcut}-->
<!--{include footer}-->
