<?php
class JWCommunity_User{
	static public function CreateUserStock($stockNum, $nameFull, $number=null){
		$nameScreen = 'gp' . $stockNum;
		$userArray = array(
			'nameScreen' => $nameScreen,
			'nameFull' => $nameFull,
		);

		$idUser = JWUser::SaveTableRow( $userArray );
		if( $idUser ) {
			$idConference = JWConference::Create( $idUser, 'N', 'sms,im,web', $number );
			return JWUser::SetConference( $idUser, $idConference );
		}
		return false;
	}
}
?>
