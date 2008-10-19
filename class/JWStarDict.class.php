<?php

/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@jiwai.com
 * @date	  	2008/10/18
 */

/**
 * JiWai.de StarDict Class
 */
class JWStarDict implements JWDict_Interface {

    /**
     * Singleton
     */
    static private $msInstance;

    /**
     * Resource of the StarDict
     */
    private $mDictRes;

    /**
     * Directory of the dictionaries
     */
    private $mDictDir;

    private function __construct($dir = null) {
        $this->mDictDir = (null == $dir)
            ? '/usr/share/dict'
            : $dir;
        $this->mDictRes = sdict_open($this->mDictDir);
    }

    function __destruct() {
        if (is_resource($this->mDictRes)) {
            sdict_close($this->mDictRes);
        }
    }

    /**
     * Implement the JWDict_Interface
     * @param string query word
     * @return array of result
     */
    public function Query($word) {
        self::Instance();
        return sdict_query($this->mDictRes, $word);
    }

    /**
     * Get the instance
     */
    static public function &Instance() {
        if (null == self::$msInstance) {
            $class_name = __CLASS__;
            self::$msInstance = new $class_name;
        }
        return self::$msInstance;
    }
}

?>

