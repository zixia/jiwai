<!--{include header}-->
<h3>建立股票帐户与分类帐户的级联关系</h3>
    <form action='/subcreate.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');" >
        上级分类帐户代码：
        <input type="text" name="superior_tag" value="" id="superior_tag" alt="上级" maxLength="6" check="number" /><br />
        下级股票账户代码：
        <input type="text" name="tag" value="" id="tag" alt="下级" maxlength="6" check="number" /><br />
        <input type="submit" value="提交" />
    </form>

<!--{include footer}-->
