<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Search Class
 */
class JWSearchStatus {
	/**
	 * page size, offset
	 */
	private $pageSize = 20;	

	private $pageNo = 1; 

	private $totalSize = 0;

	/**
	 * site in, result in site
	 */
	private $inSite = null;

	/**
	 * statuses ids,user ids
	 */
	private $statusIds = array();
	private $userIds = array();

	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
	}
	
	/**
  	 * Set search site
	 */	 
	function setInSite($site){
		$this->inSite = $site;
	}

	/**
	 * Set page Size
	 */
	function setPageSize($size){
		$this->pageSize = $size;	
	}

	/**
	 * Set pageNo
	 */
	function setPageNo($pageNo){
		$this->pageNo = $pageNo;	
	}

	function getTotalSize(){
		return $this->totalSize;
	}

	function getPageSize(){
		return $this->pageSize;
	}

	function getStatusIds(){
		return $this->statusIds;
	}

	function getUserIds(){
		return $this->userIds;
	}


	function execute($key){
		$url = $this->buildQuery($key);
		$content = $this->readUrl($url);
		$content = mb_convert_encoding($content, "UTF8", "GBK,UTF8");

		//totalSize
		$flag = preg_match("/有 <b>([\d,]+)<\/b> 项符合/", $content, $matches);
		if( $flag ){
			//数字可能含有千位符号
			$number = str_replace(',','',$matches[1]);
			$this->totalSize = $number > 100 ?  100 : $matches[1];
		}

		//get statusid	
		if( $flag ){
			if( preg_match_all("/href=\"http:\/\/([\w\.]+)\/(\w+)\/statuses\/(\d+)\/?\"/", $content, $matches) ){

				$status_ids = $matches[3];
				$user_ids = $matches[2];
				$bind_ids = array_combine( $status_ids, $user_ids );

				arsort( $status_ids );
				krsort( $bind_ids );

				$offset = ($this->pageNo-1) > 0 ? ($this->pageNo-1)*$this->pageSize : 0;
				$sliced_ids = @array_slice($status_ids, $offset, $this->pageSize );

				$final_array = array();
				foreach($sliced_ids as $id){
					$final_array[$id] = $bind_ids[$id];
				}

				$this->statusIds = array_keys($final_array);
				$this->userIds = array_values($final_array);
			}
		}

	}


	private function buildQuery($key){
		$key .= $this->inSite ? " site:".$this->inSite : null;
		$queryUrl = "http://www.google.cn/search?q=".urlEncode($key);
		$queryUrl .= "&hl=zh-CN";
		//$queryUrl .= "&num=".$this->pageSize;
		$queryUrl .= "&num=100";
		//$queryUrl .= ($this->offset > 0) ? "&start=".$this->offset : null;
		$queryUrl .= "&filter=0";
		return $queryUrl;
	} 

	private function readUrl($url, &$info=false){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		if( $info ){
			$info = curl_getinfo($ch, $info);
		}
		curl_close($ch);
		return $data;
	}
}
?>
