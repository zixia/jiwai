<?php
require_once( dirname(__FILE__) . '/config.inc.php' );
require_once( dirname(__FILE__) . '/lib/Robot.class.php' );

function doPost(){
	if( $_POST ){
		$cmd = null;
		extract($_POST, EXTR_IF_EXISTS);
		$result = StockCmdRobot::Execute( $cmd ) ;

		if( $result == false ) {
			CmdResult("$cmd\n\n-- 非法的指令，请到帮助页仔细阅读指令 --");
		}else{
			CmdResult("$cmd\n\n-- 执行结果 --\n\n$result");
		}

		Header('Location: /cmd.php');
		exit;
	}
}

doPost();

$cmdResult = CmdResult();

JWRender::Display( 'cmd', array( 'result' => $cmdResult, ) );

?>
