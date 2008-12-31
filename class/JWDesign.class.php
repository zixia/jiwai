<?php
/**
 * @package	JiWai.de
 * @copyright	AKA Inc.
 * @author  	zixia@zixia.net
 */

/**
 * JiWai.de File Class
 */
class JWDesign {
	/**
	 * Instance of this singleton
	 *
	 * @var JWDesign
	 */
	private $mBackgroundColor;
	private $mUseBackgroundImage;
	private $mBackgroundTile;
	private $mTextColor;
	private $mNameColor;
	private $mLinkColor;
	private $mSidebarFillColor;
	private $mSidebarBorderColor;
	private $mTimeStamp;
	private $mDesignChoice;


	public $mIsDesigned = false;

	private $mUserId	= null;
	/**
	 * Constructing method, save initial state
	 *
	 *	@param	int	$idUser		
	 */
	function __construct($idUser)
	{
		$this->mUserId = JWDB::CheckInt($idUser);

		$db_row = self::GetDbRow();

		if ( empty($db_row) )
		{
			$this->mIsDesigned = false;
			$this->InitDefaultColor();
			return;
		}

		$this->mBackgroundColor = $db_row['colorBackground'];
		$this->mUseBackgroundImage = $db_row['idPictureBackground'];
		$this->mBackgroundTile = strtolower($db_row['isTile']);
		$this->mTextColor = $db_row['colorText'];
		$this->mNameColor = $db_row['colorName'];
		$this->mLinkColor = $db_row['colorLink'];
		$this->mSidebarFillColor = $db_row['colorSidebarFill'];
		$this->mSidebarBorderColor = $db_row['colorSidebarBorder'];
		$this->mTimeStamp = $db_row['timeStamp'];
		$this->mDesignChoice = explode(',',$db_row['designChoice']);
		$this->InitDesignChoice($this->mDesignChoice);

		$this->mIsDesigned = true;
	}

	private function InitDesignChoice(&$choice) {
		if ( empty($choice[0]) ) {
			$choice[0] = 'index';
		}
		return $choice;
	}

	function InitDefaultColor()
	{
		$this->mBackgroundColor = 'FFFFFF';
		$this->mUseBackgroundImage = null;
		$this->mBackgroundTile = 'left';
		$this->mTextColor = '333333';
		$this->mNameColor = 'FFFFFF';
		$this->mLinkColor = '669900';
		$this->mSidebarFillColor = 'C3E169';
		$this->mSidebarBorderColor = '87BC44';
		$this->mTimeStamp = null;
		$this->mDesignChoice = null;
	}

	public function Destroy()
	{
		return JWDB::DelTableRow('Design', array(
					'idUser' => $this->mUserId,
					));
	}

	private function GetDbRow()
	{
		return JWDB::GetTableRow('Design', array(
					'idUser' => $this->mUserId,
					));
	}

	public function Save($user_id=null)
	{
		if ( !$user_id )
			$user_id = $this->mUserId;

		JWDB::DelTableRow( 'Design', array ( 
					'idUser' => $user_id )); 

		return JWDB::SaveTableRow('Design', array ( 
			'idUser' => $user_id
			,'colorBackground' => $this->mBackgroundColor
			,'idPictureBackground' => $this->mUseBackgroundImage
			,'isTile' => $this->mBackgroundTile
			,'colorText' => $this->mTextColor
			,'colorName' => $this->mNameColor
			,'colorLink' => $this->mLinkColor
			,'colorSidebarFill' => $this->mSidebarFillColor
			,'colorSidebarBorder' => $this->mSidebarBorderColor
			,'designChoice' => join(',', $this->mDesignChoice)
			));
	}

	public function GetBackgroundColor(&$color)
	{
		$color = $this->mBackgroundColor;
	}

	public function SetBackgroundColor($color)
	{
		$color = str_replace('#','',$color);
		$this->mBackgroundColor = $color;
	}

	public function GetUseBackgroundImage(&$idPicture)
	{
		$idPicture = $this->mUseBackgroundImage;
	}

	public function SetUseBackgroundImage($idPicture)
	{
		$this->mUseBackgroundImage = $idPicture;
	}

	public function GetBackgroundTile(&$color)
	{
		$color = $this->mBackgroundTile;
	}

	public function SetBackgroundTile($isTile)
	{
		$this->mBackgroundTile = strtolower($isTile);
	}

	public function GetTextColor(&$color)
	{
		$color = $this->mTextColor;
	}

	public function SetTextColor($color)
	{
		$color = str_replace('#','',$color);
		$this->mTextColor = $color;
	}

	public function GetNameColor(&$color)
	{
		$color = $this->mNameColor;
	}

	public function SetNameColor($color)
	{
		$color = str_replace('#','',$color);
		$this->mNameColor = $color;
	}

	public function GetLinkColor(&$color)
	{
		$color = $this->mLinkColor;
	}

	public function SetLinkColor($color)
	{
		$color = str_replace('#','',$color);
		$this->mLinkColor = $color;
	}

	public function GetSidebarFillColor(&$color)
	{
		$color = $this->mSidebarFillColor;
	}

	public function SetSidebarFillColor($color)
	{
		$color = str_replace('#','',$color);
		$this->mSidebarFillColor = $color;
	}

	public function GetSidebarBorderColor(&$color)
	{
		$color = $this->mSidebarBorderColor;
	}

	public function SetSidebarBorderColor($color)
	{
		$color = str_replace('#','',$color);
		$this->mSidebarBorderColor = $color;
	}

	public function GetDesignChoice(&$choice)
	{
		$choice = $this->mDesignChoice;
	}

	public function SetDesignChoice($choice)
	{
		$this->mDesignChoice = $choice;
	}

	public function GetTimeStamp(&$time) 
	{
		$time = $this->mTimeStamp;
	}

	public function IsDesigned() {
		return $this->mIsDesigned;
	}

	public function GetStyleUrl() 
	{
		if ( !$this->mIsDesigned ) return;
		$url = "/system/{$this->mUserId}.css";
		$time_stamp = $this->GetLastModifiedTime();
		//$url .= "?$time_stamp";
		return JWTemplate::GetAssetUrl($url, false);
	}

	public function GetLastModifiedTime() 
	{
		$last_mod_time = strtotime($this->mTimeStamp);
		foreach( $this->mDesignChoice AS $one ) 
		{
			$css_file = "{$css_path}/{$one}.css";
			if (!file_exists($css_file) ) continue;
			$last_mod_time = ($last_mod_time>filemtime($css_file)) 
				? $last_mod_time : filemtime($css_file);
		}
		return $last_mod_time;
	}

	public function GetStyleSheet()
	{
		if ( ! $this->mIsDesigned )
			return;
	
		$css_path = JW_ROOT . 'domain/asset/css';

		$background_url_css = '';
		if ( $this->mUseBackgroundImage )
		{
			$background_url = JWPicture::GetUrlById($this->mUseBackgroundImage, 'origin');
			switch( $this->mBackgroundTile ) {
				case 'center': 
					$tile = 'center top fixed no-repeat';
					break;
				case 'repeat':
					$tile = 'left top fixed';
					break;
				case 'left':
				default:
					$tile = 'left top fixed no-repeat';
			}
			$background_url_css = "url({$background_url}) {$tile}";
		}

		$background_url_css = "\nhtml{background: #{$this->mBackgroundColor} {$background_url_css};}\n";

		$styles = $index = null;
		foreach( $this->mDesignChoice AS $one ) {
			$css_file = "{$css_path}/{$one}.css";
			if (!file_exists($css_file) ) continue;
			$styles .= file_get_contents($css_file);
			if ( $one=='index') $index = true;
		}

		if ( $styles && $this->mUseBackgroundImage ) {
			$styles .= $background_url_css;
		}elseif ( !$styles || $index ) {
			$styles .= $background_url_css;
		}

		return $styles;
	}

	static public function UpdateStyle() {
		return JWDB::Execute('UPDATE Design SET timeStamp=NULL');
	}
}
?>
