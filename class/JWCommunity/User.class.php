<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author	shwdai@jiwai.de
 */

/**
 * JiWai.de JWCommunity_User Class
 */
class JWCommunity_User{
	/**
	 * 创建股票用户
	 */
	static public function CreateUserStock($stockNum, $nameFull, $number=null){
		$nameScreen = 'gp' . $stockNum;
		$userArray = array(
			'nameScreen' => $nameScreen,
			'nameFull' => $nameFull,
		);

		$idUser = JWUser::SaveTableRow( $userArray );
		if( $idUser ) {
			$idConference = JWConference::Create( $idUser, 'N', 'sms,im,web', $number,
								array(
									'forceFilter' => 'Y',
							     ));
			return JWUser::SetConference( $idUser, $idConference );
		}
		return false;
	}
}
?>
