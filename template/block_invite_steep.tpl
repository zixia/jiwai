<!--${
	$count_reg = count($buddies[2]);
}-->
<div class="block">
	<div class="mar_b20">你共有&nbsp;{$count_reg}&nbsp;个联系人不在叽歪上，你可以邀请他们加入叽歪。</div>
	<form id="f1" action="/wo/invite/steeep/{$cache_key}" method="post">
	<div class="mar_b8">
		<input type="button" onclick="$('f1').submit();" value="&nbsp;完成&nbsp;" />
	</div>
	<div class="clear"></div>
	<div class="gray mar_b8">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t">
			<h4><input type="checkbox" name="" onclick="CheckAll('use_list',this.checked)" /> 选择所有联系人</h4>
		</div>
		<div id="use_list" class="f">
			<div>
				<div class="clear"></div>
			<!--{foreach $buddies[2] AS $buddy}-->
			<!--${list($e,$n)=explode(',',$buddy);}-->
				<div class="one line">
					<div class="lt"><input type="checkbox" name="not_reg[]" value="{$e}" /></div>
					
					<div class="bgall">
						<h4 class="lt"><a href="mailto:{$e}?subject={$g_current_user['nameFull']}邀请你加入叽歪网">{$n}</a></h4>
						<div align="right">
							&lt;{$e}&gt;
						</div>
					</div>
					<div class="clear"></div>
				</div>
			<!--{/foreach}-->
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
	<div>
		<input type="button" onclick="$('f1').submit();" value="&nbsp;完成&nbsp;" />
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>
