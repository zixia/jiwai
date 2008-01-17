<!--{include header}-->
<h3>显示帐户的详细信息</h3>
    <form action="/show.php" id='f' method="POST" onSubmit="return JWValidator.validate('f');">
        股票代码：
        <input type="text" name="stock_num" value="" id="stock_num" alt="股票代码" maxLength="6" minlength="3" check="number" />
        <input type="submit" value="提交"/>
    </form>
<!--{if $rows}-->
<table border="3">
    <tr>
        <td>股票名称</td>
        <td>股票代码</td>
        <td>类型</td>
        <td>订阅数</td>
        <td>信息数</td>
    </tr>
    <tr>
        <td>{$rows['name']}</td>
        <td>{$rows['description']}</td>
        <td>{$rows['type']}</td>
        <td>{$follower_num}</td>
        <td>{$rows['countPost']}</td>
    </tr>
</table>
<!--{/if}-->
<!--{include footer}-->
