<card title="叽歪de，迷你博客">

    账户：<input type="text" name="name"/><br/>
    密码：<input type="password" name="pass"/><br/>
    <anchor>登录叽歪<go href="${buildUrl('/wo/login/')}" method="post"><postfield name="name" value="$(name)"/><postfield name="pass" value="$(pass)"/></go></anchor><br/>

    <!--{include shortcut}-->

</card>
