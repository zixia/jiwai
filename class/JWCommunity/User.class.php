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
		$options = array(
				'number'=> $number,
				'forceFilter' => 'Y',
				'deviceAllow' => 'sms,im,web',
				'friendOnly' => 'N',
			);

		return self::CreateUserWithConference($nameScreen, $nameFull, $options );
	}

	static public function CreateUserStockCategory($nameScreen, $nameFull){
		$options = array(
				'numberUseIdUser' => true,
				'forceFilter' => 'Y',
				'deviceAllow' => 'sms,im,web',
				'friendOnly' => 'N',
			);

		return self::CreateUserWithConference($nameScreen, $nameFull, $options );
	}

	static public function CreateUserWithConference($nameScreen, $nameFull, $options=array() ){

		$userArray = array(
			'nameScreen' => $nameScreen,
			'nameFull' => $nameFull,
		);
		$idUser = JWDB::SaveTableRow( 'User', $userArray );
		if( $idUser ) {

			$number = isset( $options['number'] ) ? $options['number'] : null;
			if( $number==null ) {
				$number = isset($options['numberUseIdUser'])  ? $idUser : null;
			}

			$forceFilter = isset( $options['forceFilter'] ) ? $options['forceFilter'] : 'N';
			$friendOnly = isset( $options['friendOnly'] ) ? $options['friendOnly'] : 'N';
			$deviceAllow = isset( $options['deviceAllow'] ) ? $options['deviceAllow'] : 'sms,im,web';

			$idConference = JWConference::Create( $idUser, $friendOnly, $deviceAllow, $number,
								array(
									'forceFilter' => $forceFilter,
							     ));
			JWUser::SetConference( $idUser, $idConference );

		}
		return $idUser;
	}
}
?>
