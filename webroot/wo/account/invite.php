<?php
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

//var_dump($_REQUEST);

$user_info		= JWUser::GetCurrentUserInfo();
$has_photo		= !empty($user_info['idPicture']);
$protected      = $user_info['protected'] == 'Y';
$idInvited      = JWUser::GetIdEncodedFromIdUser( $user_info['id'] );

 if ( $has_photo ){
    // we have photo
    $photo_url = JWPicture::GetUserIconUrl($user_info['id'],'thumb48');
}else{
    // we have no photo
    $photo_url = JWTemplate::GetAssetUrl('/img/stranger.gif');
}

//echo "<pre>"; die(var_dump($user_info));
//var_dump($file_info);
if ( isset($_POST['invite_email_x'] ) ) {
    $emails = $_POST['email_addresses'];
    $subject = $_POST['subject'];
    $emails = split('/[，, ]/', $emails);
    $count = 0;
    foreach( $emails as $email ) {
        if( JWMail::SendMailInvitation( $user_info, $email, $subject, $idInvited ) )
            $count ++;
    }

    if( $count )
        JWSession::SetInfo('notice', '你的邀请，我们已经通过Email发给你的朋友们了，他们注册后会自动成为你的好友！');
    else
        JWSession::SetInfo('notice', '对不起，你所填写的朋友的Email地址不合法，我们无法帮你邀请你的的朋友！');

    Header("Location: /wo/");
    exit;
}

if ( isset($_POST['invite_sms_x'] ) ) {
    JWSession::SetInfo('notice', '你的邀请，我们已经通过手机短信发给你的朋友们了，他们注册后会自动成为你的好友！');
    Header("Location: /wo/");
    exit;
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head() ?>
<script type="text/javascript">
function shifttab(id){
    switch(id){
        case 1:
            $('invite_msn').style.display='block';
            $('tab_msn').className='now';
            $('invite_email').style.display='none';
            $('tab_email').className='';
      //      $('invite_sms').style.display='none';
       //     $('tab_sms').className='';
        break;
        case 2:
            $('invite_msn').style.display='none';
            $('tab_msn').className='';
            $('invite_email').style.display='block';
            $('tab_email').className='now';
    //        $('invite_sms').style.display='none';
     //       $('tab_sms').className='';
        break;
        case 3:
            $('invite_msn').style.display='none';
            $('tab_msn').className='';
            $('invite_email').style.display='none';
            $('tab_email').className='';
  //          $('invite_sms').style.display='block';
   //         $('tab_sms').className='now';
        break;
    }
}
</script>
</head>

<body class="account" id="friends">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container">

<h2>邀请朋友一起来叽歪</h2>
<p>如果暂时不想邀请朋友，可以点击进入<a href="/wo/">我的首页</a></p>

<p class="subtab"><a id="tab_msn" href="javascript:shifttab(1);">通过MSN邀请</a><a id="tab_email" href="javascript:shifttab(2);" class="now">通过Email邀请</a><!--a id="tab_sms" href="javascript:shifttab(3);">通过短信邀请</a--></p>

<div id="invite_msn" style="display:none;">
    <div class="tabbody">
        <div>把下面的网址通过MSN发送给朋友</div>
        <div>
                <input type="text" size="50" value="http://JiWai.de/wo/invitations/i/<?php echo $idInvited; ?>"/>
        </div>
        <div style="margin-bottom:20px;">朋友注册后你们自动成为叽歪上的好友。</div>
    </div>
</div>

<div id="invite_sms" style="display:none">
    <div  class="tabbody">
    <form id="f" method="post" name="f">
    <fieldset>
    <table width="548" border="0" cellpadding="0" cellspacing="10">
    <tr>
        <td align="right"><label for="email_addresses"><nobr>好友手机号：</nobr></label>              </td>
        <td>
            <table width="420" border="0" cellspacing="0" cellpadding="0" style="margin-top:0; margin-left:0;">
            <tr>
                <td><textarea cols="30" id="emails" name="sms_addresses" onchange="onEmailChange();" rows="3" style="width:200px;"></textarea></td>
                <td><small>多个收件人用回车或者逗号分隔。每次最多发送两人</small></td>
            </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="right">短信内容：</td>
        <td>我在叽歪de建立了自己的一句话博客，发布自己的动向，你回复就可以关注我的动向。（可以随时停止关注）</td>
    </tr>
    <tr>
        <td align="right">短信署名：</td>
        <td><input type="text" name="sig" value="<?php echo $user_info['nameScreen'];?>"/> <small>你的好友收到并回复1，就可以通过手机接收并回复你新的叽歪消息了。</small></td>
    </tr>
    </table>
    </fieldset>
    </div>
    <div class="but">
        <input name="invite_sms" type="image" src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-sure.gif'); ?>" alt="确定" width="112" height="33" border="0" />　　<a href="/wo/"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-skip.gif'); ?>" alt="跳过" width="112" height="33" border="0" /></a>
    </div>
    </form>
</div>

<div id="invite_email" style="display:block">
    <div  class="tabbody">
    <form id="f" method="post" name="f">
    <fieldset>
    <table width="548" border="0" cellpadding="0" cellspacing="10">
    <tr>
        <td><label for="email_addresses"><nobr>收件人：</nobr></label><br/></td>           
        <td>
            <table width="440" border="0" cellspacing="0" cellpadding="0" style="margin-top:0; margin-left:0;">
            <tr>
                <td><textarea cols="30" id="emails" name="email_addresses" rows="3"></textarea></td>
                <td><small>多个收件人用回车或者（,）分隔。</small></td>
            </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>发件人：</td>
        <td><input type="text" name="replyTo" value="<?php echo $user_info['email'];?>"/></td>
    </tr>
    <tr>
        <td>主　题：</td>
        <td><input type="text" name="subject" value="<?php echo $user_info['nameScreen'];?>邀请您一起加入叽歪"/></td>
    </tr>
    <tr>
        <td>内　容：</td>
        <td>
            <pre id="invite_preview">您好！
我在叽歪上建立了自己的一句话博客，我以后会不停的在上面发布
自己的动向，希望你能来看看我。


请点击这里接受邀请，注册后直接成为<?php echo $user_info['nameScreen'];?>的好友：：
<a href="http://JiWai.de/wo/invitations/i/<?php echo $idInvited;?>">http://JiWai.de/wo/invitations/i/<?php echo $idInvited;?></a>

或您可以在这里关注 <?php echo $user_info['nameScreen']; ?> (<?php echo $user_info['nameFull'];?>) 的最新动态：
<a href="http://JiWai.de/<?php echo $user_info['nameScreen'];?>/">http://JiWai.de/<?php echo $user_info['nameScreen'];?>/</a>

叽歪de能让你用一句话建立自己的博客，用只言片语记录生活轨迹。

<strong><?php echo $user_info['nameScreen'];?> <?php echo Date("m/d/Y");?></strong>
            </pre>
        </td>
    </tr>
    </table>
    </fieldset>
    </div>
    <div class="but">
        <input name="invite_email" type="image" src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-sure.gif'); ?>" alt="确定" width="112" height="33" border="0" />　　<a href="/wo/"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-skip.gif'); ?>" alt="跳过" width="112" height="33" border="0" /></a>
    </div>
    </form>
</div>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>          
</div>
<!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
