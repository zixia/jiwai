<!--${$minuser = $g_page_on ? $g_page_user : $g_current_user;}-->
<!--{if $minuser}-->
<div class="f">
	<div class="usermsg mar_b8">
		<div class="lt">
			<div class="one">
				<div class="hd">
					<a href="/wo/account/profile"><em><img src="${JWPicture::GetUrlById($minuser['idPicture']);}" title="{$minuser['nameScreen']}" class="buddy" icon="{$minuser['id']}" /></em></a>
				</div>
			</div>
		</div>
		<div class="msg">
			<h1>{$minuser['nameScreen']}</h1>
		</div>
		<div class="clear"></div>
	</div>
</div>
<!--{/if}-->
