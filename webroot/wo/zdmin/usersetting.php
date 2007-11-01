<?php
if(!defined('TPL_COMPILED_DIR')) define('TPL_COMPILED_DIR',dirname(__FILE__).'/compiled');
if(!defined('TPL_TEMPLATE_DIR')) define('TPL_TEMPLATE_DIR',dirname(__FILE__).'/template');
require_once('../../../jiwai.inc.php');
require_once('./function.php');

$id = null;
$password = null;
$oneResult = array();
$id = @trim($_POST['id']);
$un = @trim($_POST['un']);
$password = @trim($_POST['password']);
//extract($_POST,EXTR_IF_EXISTS);
if( $_POST ){
    if( $id && $un && $password){
        $id = JWDB::CheckInt($id);
        $oneResult = JWUser::GetUserInfo($un);
        if($oneResult){
            $outId = $oneResult['id'];    
        }
        if($id == $outId){           
        
        JWUser::ChangePassword($id,$password);
        setTips('修改'.$un.'的密码成功！');
        }
        else{
            setTips('用户ID与用户名不匹配，请重新输入!');    
        }
    }
    else{
        setTips('请输入完整!');
            
    }


 header("Location:usersetting");
 exit;
}
$render = new JWHtmlRender();
$render->display("usersetting", array(
     'menu_nav' => 'usersetting', 
			));
?>

