<div id="set_bor">
	<div class="mar_20">&nbsp;</div>
	<form action="/wo/account/photos" method="post" enctype="multipart/form-data" >
	<dl class="w2">
		<dt><a href="/{$g_current_user['nameUrl']}/"><img src="${JWPicture::GetUrlById($g_current_user['idPicture'],'thumb96')}" title="{$g_current_user['nameScreen']}" width="96" height="96" /></a></dt>
		<dd>
			<div class="mar_b40"><h4>上传新头像... </h4></div>
			<div><input type="file" name="profile_image" /></div>
			<div class="f_gra">支持.jpg .gif .png格式，最大可以上传 2M 大小的图片</div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" name="" value="&nbsp; 保存修改 &nbsp;" /> &nbsp; <input type="reset" value="取消" /></div>
		</dd>
		<div class="clear"></div>
	</dl>	
	</form>
</div>
<div class="clear"></div>
