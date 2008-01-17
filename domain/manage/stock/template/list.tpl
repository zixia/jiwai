<!--{include header}-->
<h3>显示股票帐户与分类帐户的级联关系</h3>
    <form action="/list.php" id='f' method="POST" onSubmit="return JWValidator.validate('f');" >
        股票代码：
        <input type="text" name="stock_num" value="" id="stock_num" alt="代码" maxLength="6" minLength="3" check="number" /></br />
        <input type="submit" value="提交" />
    </form>
<p>{$rows}</p>
<!--{include footer}-->
