<!--{include header}-->

<h3>销毁股票账户</h3>
<form action='/destroyStockAccount.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');">

股票代码：<input type="text" name="stockNum" value="" id="stockNum" alt="股票代码" maxLength="6" minlength="6" check="number" /><br/>

<input type="submit" value="提交"/>

</form>

<!--{include footer}-->
