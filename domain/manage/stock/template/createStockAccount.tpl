<!--{include header}-->

<h3>创建股票账户</h3>
<form action='/createStockAccount.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');">

股票代码：<input type="text" name="stockNum" value="" id="stockNum" alt="股票代码" maxLength="6" minlength="6" check="number" /><br/>
股票名称：<input type="text" name="nameFull" value="" id="nameFull" alt="股票名称" check="null" /><br/>

<input type="submit" value="提交"/>

</form>

<!--{include footer}-->
