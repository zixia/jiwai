<!--{include header}-->
<h2>手机绑定 反馈列表</h2>
<table width="750"  border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
  <tr align="center" bgcolor="#CCCCCC" style="font-weight:bold; line-height:24px; background-color:#e4e4e4; border:1px solid #fff; color:#333333;">
    <td width="12%">地区</td>
    <td width="12%">网络</td>
    <td width="12%">累积填入数</td>
    <td width="12%">累积成功数</td>
    <td width="12%">打开通知数</td>

    <td width="10%">大前天</td>
    <td width="10%">前天</td>
    <td width="10%">昨天</td>
    <td width="10%">今天</td>
  </tr>
<!--{foreach $result AS $id=>$one}-->
  <tr align="center" bgcolor="#FFFFFF">
    <td rowspan="3">{$location[$id]['name']}</td>
    <td>中国移动</td>
    <td>{$one['m']['total_set']}</td>
    <td>{$one['m']['total_bind']}</td>
    <td>{$one['m']['total_via']}</td>

    <td>{$one['m']['d-3-bind']}/{$one['m']['d-3-set']}</td>
    <td>{$one['m']['d-2-bind']}/{$one['m']['d-2-set']}</td>
    <td>{$one['m']['d-1-bind']}/{$one['m']['d-1-set']}</td>
    <td>{$one['m']['d-0-bind']}/{$one['m']['d-0-set']}</td>
  </tr>
  <tr align="center" bgcolor="#FFFFFF">
    <td>中国联通</td>
    <td>{$one['u']['total_set']}</td>
    <td>{$one['u']['total_bind']}</td>
    <td>{$one['u']['total_via']}</td>

    <td>{$one['u']['d-3-bind']}/{$one['u']['d-3-set']}</td>
    <td>{$one['u']['d-2-bind']}/{$one['u']['d-2-set']}</td>
    <td>{$one['u']['d-1-bind']}/{$one['u']['d-1-set']}</td>
    <td>{$one['u']['d-0-bind']}/{$one['u']['d-0-set']}</td>
  </tr>
  <tr align="center" bgcolor="#ECECEC">
    <td> - 合计 -</td>
    <td>{$one['t']['total_set']}</td>
    <td>{$one['t']['total_bind']}</td>
    <td>{$one['t']['total_via']}</td>

    <td>{$one['t']['d-3-bind']}/{$one['t']['d-3-set']}</td>
    <td>{$one['t']['d-2-bind']}/{$one['t']['d-2-set']}</td>
    <td>{$one['t']['d-1-bind']}/{$one['t']['d-1-set']}</td>
    <td>{$one['t']['d-0-bind']}/{$one['t']['d-0-set']}</td>
  </tr>
<!--{/foreach}-->

  <tr align="center" bgcolor="#FFFFFF">
    <td rowspan="3"> - 合计 - </td>
    <td>中国移动</td>
    <td>{$total['m']['total_set']}</td>
    <td>{$total['m']['total_bind']}</td>
    <td>{$total['m']['total_via']}</td>

    <td>{$total['m']['d-3-bind']}/{$total['m']['d-3-set']}</td>
    <td>{$total['m']['d-2-bind']}/{$total['m']['d-2-set']}</td>
    <td>{$total['m']['d-1-bind']}/{$total['m']['d-1-set']}</td>
    <td>{$total['m']['d-0-bind']}/{$total['m']['d-0-set']}</td>
  </tr>
  <tr align="center" bgcolor="#FFFFFF">
    <td>中国联通</td>
    <td>{$total['u']['total_set']}</td>
    <td>{$total['u']['total_bind']}</td>
    <td>{$total['u']['total_via']}</td>

    <td>{$total['u']['d-3-bind']}/{$total['u']['d-3-set']}</td>
    <td>{$total['u']['d-2-bind']}/{$total['u']['d-2-set']}</td>
    <td>{$total['u']['d-1-bind']}/{$total['u']['d-1-set']}</td>
    <td>{$total['u']['d-0-bind']}/{$total['u']['d-0-set']}</td>
  </tr>
  <tr align="center" bgcolor="#ECECEC">
    <td> - 合计 -</td>
    <td>{$total['t']['total_set']}</td>
    <td>{$total['t']['total_bind']}</td>
    <td>{$total['t']['total_via']}</td>

    <td>{$total['t']['d-3-bind']}/{$total['t']['d-3-set']}</td>
    <td>{$total['t']['d-2-bind']}/{$total['t']['d-2-set']}</td>
    <td>{$total['t']['d-1-bind']}/{$total['t']['d-1-set']}</td>
    <td>{$total['t']['d-0-bind']}/{$total['t']['d-0-set']}</td>
  </tr>

</table>
<!--{include footer}-->
