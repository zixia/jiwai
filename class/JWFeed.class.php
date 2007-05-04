<?php
/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      zixia@zixia.net
 */

require_once(JW_ROOT . '/lib/FeedCreator/FeedCreator.class.php');
/**
 * JiWai.de Feed Class
 *
 */
class JWFeed {
    private $mFeed;

    const OPML = 'OPML';
    const ATOM = 'ATOM';
    const RSS20 = 'RSS2.0';
    const RSS10 = 'RSS1.0';
    const RSS092 = 'RSS0.92';

	/*
	 * set feed base info
	 *	@param	option	array, include setting of: title, url, desc(description), ttl.
 	 */
    function __construct ($option) {
        $this->mFeed = new UniversalFeedCreator();

        $this->mFeed->title 		= $option['title'];
        $this->mFeed->description 	= $option['desc'];
        $this->mFeed->link 			= $option['url'];

		if ( array_key_exists('ttl',$option) )
        	$this->mFeed->ttl 			= $option['ttl'];
		else
        	$this->mFeed->ttl 			= 60;

        $img				= new FeedImage();
        $img->url			= 'http://JiWai.de/dev/picture/thumb24';

        $img->link			= 'http://JiWai.de/';
        $img->title			= '叽歪de - 这一刻，你在做什么？';
        $img->width			= 48;
        $img->height		= 48;
        $img->description 	= '叽歪de - 这一刻，你在做什么？';

        $this->mFeed->image	= $img;



		$this->mFeed->language		= 'zh-CN';
		
		// FIXME IE 不认这两个？
        //$this->mFeed->cssStyleSheet = JWTemplate::GetAssetUrl('/css/feed_rss.css');
        //$this->mFeed->xslStyleSheet = JWTemplate::GetAssetUrl('/css/feed_rss.xsl');


    }

	/*
	 *
	 *	@param	array	item	a array include those keys: 
								title, desc, date, author, url, guid
	 */
    public function AddItem($itemInfo)
	{
//title, $url, $desc='', $date=null, $author='', $commentsUrl=null,$enclosure='',$guid='',$logo='', $source='') {
        $item = new FeedItem();

        $item->title 		= $itemInfo['title'];
        $item->description	= $itemInfo['desc'];
        $item->date 		= $itemInfo['date'] ? $itemInfo['date'] : time();
        $item->author		= $itemInfo['author'];
        $item->link 		= $itemInfo['url'];
        $item->guid			= $itemInfo['guid'];

//die(var_dump($img));

        $this->mFeed->addItem($item);
    }

    public function Export($type=JWFeed::RSS20) 
	{
        return $this->mFeed->createFeed($type);
    }

    public function OutputFeed($type=JWFeed::RSS20) 
	{
        $this->mFeed->outputFeed($type);
    }

}

?>
