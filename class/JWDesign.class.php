<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
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


	private	$mIsDesigned = false;

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
		$this->mBackgroundTile = 'Y'==$db_row['isTile'];
		$this->mTextColor = $db_row['colorText'];
		$this->mNameColor = $db_row['colorName'];
		$this->mLinkColor = $db_row['colorLink'];
		$this->mSidebarFillColor = $db_row['colorSidebarFill'];
		$this->mSidebarBorderColor = $db_row['colorSidebarBorder'];

		$this->mIsDesigned = true;
	}

	function InitDefaultColor()
	{
		$this->mBackgroundColor = '000000';
		$this->mUseBackgroundImage = null;
		$this->mBackgroundTile = false;
		$this->mTextColor = '333333';
		$this->mNameColor = '000000';
		$this->mLinkColor = '669900';
		$this->mSidebarFillColor = 'C3E169';
		$this->mSidebarBorderColor = '87BC44';
	}

	public function Destroy()
	{
		return JWDB::DelTableRow('Design', array('idUser'=>$this->mUserId));
	}

	private function GetDbRow()
	{
		return JWDB::GetTableRow('Design', array('idUser'=>$this->mUserId));
	}

	public function Save()
	{
		return JWDB::ReplaceTableRow('Design', array ( 
				 'idUser'				=> $this->mUserId
				,'colorBackground'		=> $this->mBackgroundColor
				,'idPictureBackground'	=> $this->mUseBackgroundImage
				,'isTile'				=> $this->mBackgroundTile ? 'Y' : 'N'
				,'colorText'			=> $this->mTextColor
				,'colorName'			=> $this->mNameColor
				,'colorLink'			=> $this->mLinkColor
				,'colorSidebarFill'		=> $this->mSidebarFillColor
				,'colorSidebarBorder'	=> $this->mSidebarBorderColor
			) );
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
		$this->mBackgroundTile = $isTile;
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

	public function GetStyleSheet()
	{
		if ( ! $this->mIsDesigned )
			return;
	
		$background_url_css = '';
		if ( $this->mUseBackgroundImage )
		{
			$background_url = JWPicture::GetUrlById($this->mUseBackgroundImage, 'origin');

			if ( $this->mBackgroundTile )
				$tile = " repeat ";
			else
				$tile = " no-repeat ";

			$background_url_css = <<<_CSS_
url($background_url) fixed $tile top left;
_CSS_;
		}

		return <<<_CSS_
<style type="text/css">
	a {color: #$this->mLinkColor;}
	#container {
		color: #$this->mTextColor;
	}
	#content {
		border-right: 0;
	}
	#header {
		background:transparent;
	}
	#footer { 
		color: #$this->mTextColor;
	}
	#navigation ul {
		background-color: #$this->mBackgroundColor;
	}
	#header a {
		color: #$this->mLinkColor;
	}
	body {
		color: #$this->mTextColor;
		background-color: #$this->mBackgroundColor;
		background: #$this->mBackgroundColor $background_url_css
	}
	
	#sidebar {
		background-color: #$this->mSidebarFillColor;
		border: 1px solid #$this->mSidebarBorderColor;
	}
	#side .notify {border: 1px solid #$this->mSidebarBorderColor;}
	#side .actions {border: 1px solid #$this->mSidebarBorderColor;}
	h2.thumb, h2.thumb a {color: #$this->mTextColor;}
</style>
_CSS_;
	}
}
?>
