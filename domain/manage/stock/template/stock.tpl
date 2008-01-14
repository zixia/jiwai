<!--{include header}-->
<h2>已登记的股票帐户</h2>
<!--{if $tag_rows}-->
<table border="1">
  <tr>
    <td>
      <h3>股票名称</h3>
    </td>
    <td>
      <h3>股票代码</h3>
    </td>
  </tr>
<!--{foreach $tag_rows as $rows}-->

  <tr>
    <td>
      {$rows['name']}
    </td>
    <td>
      {$rows['description']}
    </td>
  </tr>
<!--{/foreach}-->
</table>
<!--{/if}-->
<!--{include footer}-->
