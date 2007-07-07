<?= $this->render("header") ?>
<h2>根据用户id或nameSreen查询其即时聊天设备地址</h2>
<form action="imquery" method="GET">
用户: <input type="text" name="un" id="un" value="<?= $this->un ?>"/>
<input type="submit" value="查询设备" onClick="return (un.value!='');"/>
</form>

<h2>根据用户聊天工具地址查询用户</h2>
<form action="imquery" method="GET">
设备: <input type="text" name="im" id="im" value="<?= $this->im ?>"/>
<input type="submit" value="查询用户" onClick="return (im.value!='');"/>
</form>

<? if($this->unResult){?>
	<hr>
	<h3>用户信息</h3>
	<table class="result" width="750">
		<tr>
			<th width="48">头像</th>
			<th>ID编号</th>
			<th>显示名称</th>
			<th>全名</th>
			<th>位置</th>
		</tr>
		<? if(is_array($this->unResult)){foreach($this->unResult as $this->one) { ?>
		<tr>
			<td><a href="http://jiwai.de/<?= $this->one['nameScreen'] ?>/"><img src="<?= JWPicture::GetUrlById($this->one['idPicture']) ?>" border="0"></a></td>
			<td><?= $this->one['id'] ?></td>
			<td><a href="http://jiwai.de/<?= $this->one['nameScreen'] ?>/"><?= $this->one['nameScreen'] ?></a></td>
			<td><?= $this->one['nameFull'] ?></td>
			<td><?= $this->one['location'] ?></td>
		</tr>
		<? }}?>
	</table>
	<h3>设备信息</h3>
	<table class="result" width="750">
		<tr>
			<th width="30">类型</th>
			<th width="140">地址</th>
			<th width="140">验证</th>
			<th>签名</th>
			<th width="30">记录</th>
		</tr>
		<? if(is_array($this->imResult)){foreach($this->imResult as $this->one) { ?>
		<tr>
			<td><?= $this->one['type'] ?></td>
			<td><?= $this->one['address'] ?></td>
			<td><?= $this->one['secret']?'N':'Y' ?></td>
			<td><?= $this->one['signature'] ?></td>
			<td><?= $this->one['isSignatureRecord'] ?></td>
		</tr>
		<? }}?>
	</table>

<? }?>

<?= $this->render("footer") ?>
