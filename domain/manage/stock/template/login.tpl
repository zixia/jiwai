<!--{include header}-->

<h3>登录股票社区管理系统</h3>

<form action='' id='f' method="POST" onSubmit="return JWValidator.validate('f');">

用 户：<input type="text" name="user" value="{$user}" id="user" check="null" alt="用户"/><br/>
密 码：<input type="password" name="pass" id="pass" check="null" alt="密码"/><br/>

<input type="submit" value="提交"/>

</form>

<!--{include footer}-->
