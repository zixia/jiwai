<!--{include header}-->

<h3>销毁分类账户</h3>
<b>输入 3-8 位分类代码：数字字母混合</b>
<form action='/destroyStockCategory.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');">

分类字母：<input type="text" name="nameScreen" value="" id="nameScreen" alt="分类字母" maxLength="6" minlength="3" maxLength="8" check="null" /><br/>

<input type="submit" value="提交"/>

</form>

<!--{include footer}-->
