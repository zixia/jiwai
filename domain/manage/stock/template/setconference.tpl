<!--{include header}-->
<h3>修改股票帐户属性</h3>

    <form action='/setconference.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');" >
        股票帐户代码：
        <input type="text" name="stock_num" value="" id="stock_num" alt="上级" maxLength="6" minLength="3" check="number" />
        <br />
        <input type="submit" value="提交" />
    </form>


<!--{include footer}-->
