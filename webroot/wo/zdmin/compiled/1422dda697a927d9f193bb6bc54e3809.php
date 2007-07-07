<?= $this->render("header") ?>
<h2>根据Status的Id号删除一条更新</h2>
<?= $this->render("tips") ?>
<form action="statusdelete" method="POST">
更新的Id号：<input type="text" name="id" id="idStatus"/> <input type="submit" value="删除" onClick="return (idStatus.value) ? confirm('确认删除'+idStatus.value+'号更新?') : false;"/>
</form>
<?= $this->render("footer") ?>
