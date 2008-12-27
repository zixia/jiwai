<?php

define('__QQWRY__' , dirname(__FILE__)."/QQWry.Dat");

/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@jiwai.com
 * @version		$Id$
 */

/**
 * JiWai.de Geocode Class
 */
class JWGeocode_QQWry {

    private static $msMissingCountry = '未知';

    private static $msMissingLocal = '未知';

    private static $StartIp=0;

    private static $EndIp=0;

    private static $EndIpOff=0;

    private static $Country='';

    private static $Local='';

    private static $CountryFlag=0; 

    private static $fp;

    private static $FirstStartIp=0;

    private static $LastStartIp=0;

    private static function getStartIp($RecNo){
        $offset=self::$FirstStartIp+$RecNo * 7 ;
        @fseek(self::$fp,$offset,SEEK_SET) ;
        $buf=fread(self::$fp ,7) ;
        self::$EndIpOff=ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])* 256*256);
        self::$StartIp=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
        return self::$StartIp;
    }

    private static function getEndIp(){
        @fseek ( self::$fp , self::$EndIpOff , SEEK_SET ) ;
        $buf=fread ( self::$fp , 5 ) ;
        self::$EndIp=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
        self::$CountryFlag=ord ( $buf[4] ) ;
        return self::$EndIp ;
    }

    private static function getCountry(){
        switch ( self::$CountryFlag ) {
            case 1:
            case 2:
                self::$Country=self::getFlagStr ( self::$EndIpOff+4) ;
                self::$Local=( 1 == self::$CountryFlag )? '' : self::getFlagStr ( self::$EndIpOff+8);
                break ;
            default :
                self::$Country=self::getFlagStr (self::$EndIpOff+4) ;
                self::$Local=self::getFlagStr ( ftell ( self::$fp )) ;
        }
    }

    private static function getFlagStr ($offset){
        $flag=0 ;
        while(1){
            @fseek(self::$fp ,$offset,SEEK_SET) ;
            $flag=ord(fgetc(self::$fp ) ) ;
            if ( $flag == 1 || $flag == 2 ) {
                $buf=fread (self::$fp , 3 ) ;
                if ($flag==2){
                    self::$CountryFlag=2;
                    self::$EndIpOff=$offset - 4 ;
                }
                $offset=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])* 256*256);
            }
            else{
                break ;
            }

        }
        if($offset<12)
            return '';
        @fseek(self::$fp , $offset , SEEK_SET ) ;

        return self::getStr();
    }

    private static function getStr ( )
    {
        $str='' ;
        while ( 1 ) {
            $c=fgetc ( self::$fp ) ;

            if(ord($c[0])== 0 )
                break ;
            $str.= $c ;
        }
        return $str ;
    }


    public static function qqwry ($dotip='') {
        if(!$dotip)return;
        if(ereg("^(127)",$dotip)){self::$Country='本地网络';return;}
        elseif(ereg("^(192\.168|10\.)",$dotip)) {self::$Country='局域网';return;}

        $nRet;
        $ip=self::IpToInt( $dotip );
        self::$fp= fopen(__QQWRY__, "rb");
        if (self::$fp == NULL) {
            $szLocal= "OpenFileError";
            return 1;

        }
        @fseek ( self::$fp , 0 , SEEK_SET ) ;
        $buf=fread ( self::$fp , 8 ) ;
        self::$FirstStartIp=ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
        self::$LastStartIp=ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])*256*256) + (ord($buf[7])*256*256*256);

        $RecordCount= floor( ( self::$LastStartIp - self::$FirstStartIp ) / 7);
        if ($RecordCount <= 1){
            self::$Country="FileDataError";
            fclose(self::$fp) ;
            return 2 ;
        }

        $RangB= 0;
        $RangE= $RecordCount;
        while ($RangB < $RangE-1)
        {
            $RecNo= floor(($RangB + $RangE) / 2);
            self::getStartIp ( $RecNo ) ;

            if ( $ip == self::$StartIp )
            {
                $RangB=$RecNo ;
                break ;
            }
            if ($ip>self::$StartIp)
                $RangB= $RecNo;
            else
                $RangE= $RecNo;
        }
        self::getStartIp ( $RangB ) ;
        self::getEndIp ( ) ;

        if ( ( self::$StartIp <= $ip ) && ( self::$EndIp >= $ip ) ){
            $nRet=0 ;
            self::getCountry ( ) ;
            self::$Local=str_replace(self::$msMissingCountry, "", self::$Local);
        }
        else{
            $nRet=3 ;
            self::$Country  = self::$msMissingCountry;
            self::$Local    = self::$msMissingLocal;
        }
        fclose ( self::$fp );
        self::$Country=preg_replace("/(CZ88.NET)|(纯真网络)/", self::$msMissingCountry, self::$Country);
        self::$Local=preg_replace("/(CZ88.NET)|(纯真网络)/", self::$msMissingLocal, self::$Local);

        return array(
                'country' => self::$Country,
                'local'   => self::$Local);
    }

    private static function IpToInt($Ip) {
        $array=explode('.',$Ip);
        $Int=($array[0] * 256*256*256) + ($array[1]*256*256) + ($array[2]*256) + $array[3];
        return $Int;
    }
}

include_once('/opt/beta.jiwai.de/jiwai.inc.php');
var_dump(JWGeocode_QQWry::qqwry('60.28.194.48'));
var_dump(JWGeocode_QQWry::qqwry('59.66.116.1'));
var_dump(JWGeocode_QQWry::qqwry('166.111.18.88'));
var_dump(JWGeocode_QQWry::qqwry('162.105.10.8'));
var_dump(JWGeocode_QQWry::qqwry('72.13.4.4'));
var_dump(JWGeocode_QQWry::qqwry('202.212.88.88'));

?>
