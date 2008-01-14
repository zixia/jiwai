<!--{include header}-->
<h3>删除股票帐户与分类帐户的级联关系</h3>
    <form action='/subdelete.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');" >
        要删除的股票帐户或分类帐户代码：
        <input type="text" name="stock_num" value="" id="stock_num" alt="帐户" maxLength="6" check="number" /><br />
        <input type="submit" value="提交" />
    </form> 
<!--{include footer}-->
