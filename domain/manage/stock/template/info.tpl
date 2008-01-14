<!--{include header}-->
<h3>发布消息</h3>
    <form action='/info.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');">
        股票代码：
        <input type="text" name="stock_num" value="" id="stock_num" alt="股票代码" maxLength="6" minlength="3" check="number" />        <br />
        消息内容：
        <textarea name="jw_status" cols="30" rows="8"  value="" id="jw_status" alt="消息内容" ></textarea><br />
        <input type="submit" value="提交" />
    </form>

<!--{include footer}-->
