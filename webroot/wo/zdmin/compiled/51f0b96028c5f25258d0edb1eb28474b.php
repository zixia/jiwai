<?= $this->render("header") ?>
<h2>注册人数列表</h2>
<a href="?">所有月份</a>
<? if(is_array($this->mArray)){foreach($this->mArray as $this->m) { ?>
<a href="?m=<?= $this->m ?>"><?= $this->m ?></a>
<? }}?>
<table class="result" width="300">
	<tr>
		<th>日期</th>
		<th>注册人数</th>
	</tr>
	<? if(is_array($this->result)){foreach($this->result as $this->one) { ?>
	<tr <?= isWeekend($this->one['day']) ? 'style="background-color:#CD6;"' : '' ?>>
		<td><?= $this->one['day'] ?></td>
		<td><?= $this->one['count'] ?></td>
	</tr>
	<? }}?>
</table>

<?= $this->render("footer") ?>
