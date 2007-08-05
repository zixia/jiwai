<?php
require_once( '../config.inc.php');

$name = $pass = null;
extract( $_REQUEST, EXTR_IF_EXISTS );

$idUser = JWUser::GetUserFromPassword($name, $pass);
if ( $idUser ) {
	JWLogin::Login($idUser, false);
	header("Location: /wo/");
	exit(0);
}else{
    JWTemplate::wml_doctype();
    JWTemplate::wml_head();
    echo <<<_CARD_
    <card title="登录失败">
		用户名/Email 与密码不匹配。<br/>
    </card>
_CARD_;
    JWTemplate::wml_foot();
}
?>
