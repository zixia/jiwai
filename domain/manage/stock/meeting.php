<?php
require_once( dirname(__FILE__).'/config.inc.php' );

JWTemplate::html_doctype();
JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$user_id = $user_info['idUser'];
$name_screen = $user_info['nameScreen'];

if( isset( $_GET['stock_num']) )
    $stock_num = $_GET['stock_num'];
elseif( isset( $_POST['stock_num'] ) )
    $stock_num = $_POST['stock_num'];
else
    $stock_num = NULL;

$tag_id = JWTag::GetIdByDescription( $stock_num );

$tag_info = JWTag::GetDbRowById( $tag_id );
$conference_id = $tag_info['idConference'];



if( $_POST )
{

    $enableConference = 'N';
    $conf = null;
    extract($_POST, EXTR_IF_EXISTS);
    if( 'Y' == $enableConference ) 
    {
        if( !isset( $conf['deviceAllow'] ) ) 
        {
            JWSession::SetInfo('error', '至少选择一个可发送设备');
        }
        else
        {
            $friend_only = isset( $conf['friendOnly'] ) ? 'Y' : 'N';
            $device_allow = implode(',', $conf['deviceAllow'] );
            $filter = isset( $conf['filter'] ) ? 'N' : 'Y';
            $notify = isset( $conf['notify'] ) ? 'Y' : 'N';

            $conference = JWConference::GetDbRowFromUser( $tag_id );
            if( empty( $conference ) )
            {
                $conference_id = JWConference::Create($tag_id, $friend_only, $device_allow );
                JWTag::SetConference($tag_id, $conference_id);
            }
            else
            {
                $conferenceNow_id = $conference['id'];
                JWConference::UpdateRow($conferenceNow_id, array(
                            'friendOnly' => $friend_only,
                            'deviceAllow' => $device_allow,
                            'notify' => $notify,
                            'filter' => $filter,
                            ));
                if( null == $conference_id ) 
                {
                    JWTag::SetConference($tag_id, $conferenceNow_id );
                }
            }
        }
    }
    else
    {
        if( $tag_info['idConference'] ) 
        {
            JWTag::SetConference($tag_id);
        }
    }

    Header("Location: /meeting.php?stock_num=$stock_num");

}

/* Confrence Information */

$conference_setting = array(
        'sms' => false,
        'im' => false,
        'web' => false,
        'friendOnly' => 'N',
        'notify' => 'Y',
        'filter' => 'N',
        );
$conference = JWConference::GetDbRowFromUser( $tag_info['id'] );
if( !empty( $conference ) )
{
    $conference_setting['friendOnly'] = $conference['friendOnly'];
    $conference_setting['notify'] = $conference['notify'];
    $conference_setting['filter'] = $conference['filter'];
    $device_allow = explode(',', $conference['deviceAllow']);
    $conference_setting['sms'] = in_array('sms', $device_allow) ? true : false;
    $conference_setting['im'] = in_array('im', $device_allow) ? true : false;
    $conference_setting['web'] = in_array('web', $device_allow) ? true : false;
}
?>
<html>

<head>
<style>
	input.cb{ width:24px; display:inline; }
</style>
<base target="_self"/>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="create">

<div id="container" class="subpage">

	<h2> <?php echo JWNotify::GetPrettySender( $user_info,'tag') ?> - 会议设置 </h2>
	<fieldset>
    
    
	<form method="POST" id='f' action='/meeting.php' onSubmit="return JWValidator.validate('f');">
		<table width="100%" cellspacing="3">
			<tr>
            
            <input type="hidden" name="stock_num" id="stock_num" value="<?php echo $stock_num ?>" />
           
				<th valign="top" width="200">
					<b>会议模式</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( null!==$conference_id ) echo ' checked="checked" ';?> id="enable_conference" name="enableConference" type="checkbox" value="Y" style="width:24px; display:inline;" /><label for="enable_conference">启动会议模式</label>
					<p> 使用方法：<br/>
						1、手机编辑短信，发送到 1066808866-40-<?php echo $stock_num; ?>  , 
						<br/>
						2、发消息时增加 "[<?php echo $tag_info['name'];  ?>]"
					</p>
				</td>
			</tr>
			<tr>
                
				<th valign="top" width="200">
					<b>高级设置</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( true==$conference_setting['sms']) echo ' checked="checked" ';?> id="conf_device_sms" name="conf[deviceAllow][]" type="checkbox" value="sms" style="width:24px; display:inline;" />
					<label for="conf_device_sms">允许手机短信发送</label>
					<br/>

					<input <?php if ( true==$conference_setting['im'] ) echo ' checked="checked" ';?> id="conf_device_im" name="conf[deviceAllow][]" type="checkbox" value="im" style="width:24px; display:inline;" />
					<label for="conf_device_im">允许聊天软件(IM)发送</label>
					<br/>

					<input <?php if ( true==$conference_setting['web'] ) echo ' checked="checked" ';?> id="conf_device_web" name="conf[deviceAllow][]" type="checkbox" value="web" style="width:24px; display:inline;" />
					<label for="conf_device_web">允许Web发送</label>
					<br/>
				</td>
			</tr>
			<tr>
				<th valign="top" width="200">
					<b>过滤设置</b>
				</th>
				<td style="padding-bottom:20px;">
					<br/>
					<input <?php if ( 'Y'==$conference_setting['friendOnly'] ) echo ' checked="checked" ';?> id="conf_friend_only" name="conf[friendOnly]" type="checkbox" value="Y" style="width:24px; display:inline;" />
					<label for="conf_friend_only">只允许好友回复给我</label>
					<br/>
					<input <?php if ( 'N'==$conference_setting['filter'] ) echo ' checked="checked" ';?> id="conf_filter" name="conf[filter]" type="checkbox" value="N" style="width:24px; display:inline;" />
					<label for="conf_filter">用户信息直接进入会议系统</label>
					<br/>
					<input <?php if ( 'Y'==$conference_setting['notify'] ) echo ' checked="checked" ';?> id="conf_notify" name="conf[notify]" type="checkbox" value="Y" style="width:24px; display:inline;" />
					<label for="conf_notify">更新自动通知订阅者</label>
					<br/>
				</td>
			</tr>
		</table>
		</fieldset>
	    <div style=" padding:24px 0 0 160px; height:50px;">
	    	<input type="image" src="<?php echo JWTemplate::GetAssetUrl('/images/org-but-save.gif'); ?>" alt="保存"/>&nbsp&nbsp&nbsp
           <p>&nbsp </p>
           <a class="button" href="javascript:history.go(-1);"><img src="<?php echo JWTemplate::GetAssetUrl('/images/org-text-back2.gif'); ?>" alt="返回" /></a>
        </div>            

	</form>
</div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>  
</div><!-- #container -->
<script>
	JWValidator.init('f');
</script>
</body>
</html>
