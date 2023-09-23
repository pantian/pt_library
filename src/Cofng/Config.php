<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 22:16
 */

namespace PTLibrary\Config;


use Cache\YacCache;
use Log\Log;
use Tool\JSON;
use Tool\Tool;

class Config {
	private static $configData = [];

	const config_key = 'configData';

	/**
	 * API 相关域名配置
	 * $key 为空，则获取全部
	 * @param null $_key
	 *
	 * @return bool|mixed|null
	 */
	public static function ApiConfig($_key=null){
	    $key='apiconfig.'.CONF_KEY;
		$config = self::getConfigData();
		$config = Tool::getArrVal($key,$config);
		if($_key){
		    return Tool::getArrVal($_key,$config);
		}
		return $config;
	}

	/**
	 * 应用配置
	 *
	 * @param null $_key
	 *
	 * @return bool|array
	 */
	public static function getAppConfig($_key=null){
		$key='app_config.'.CONF_KEY;
		$config = self::getConfigData();
		$config = Tool::getArrVal($key,$config);
		if($_key){
			return Tool::getArrVal($_key,$config);
		}
		return $config;
	}

	public static function getConfigData(){
	    $config=YacCache::getYacInstance()->get( self::config_key);
	    if($config){
		    self::loadConfig();
	    }
	    return $config;
	}

	public static function getWxConfig($configKey=null){
		$config = self::getConfigData();
		$configKey || $configKey=CONF_KEY;
		return Tool::getArrVal( 'weixin.' . $configKey, $config );
	}

	public static function getRedis(){
		$config = self::getConfigData();
		return Tool::getArrVal( 'redis.' . CONF_KEY, $config );
	}

	/**
	 * 小程序模板消息
	 *
	 * @param $key
	 *
	 * @return bool|null
	 */
	public static function appletTemplate($key){
		$config = self::getConfigData();
		return Tool::getArrVal( 'appletTemplate.'. $key, $config );
	}



	public static function getMysql(){
		$config = self::getConfigData();
		if(!$config){
			$config=self::loadConfig();
		}
		return Tool::getArrVal( 'mysql.' . CONF_KEY.'.master', $config );
	}

	public static function getAliYunOssConfig(){
		$config = self::getConfigData();
		return Tool::getArrVal( 'oss.' . CONF_KEY, $config );
	}

	/**
	 * @param string $key
	 *
	 * @return bool|null
	 */
	public static function getApiConfig($key=''){
		$config = self::getConfigData();
		$_k='apiconfig.' . CONF_KEY ;
		$key && $_k.='.'.$key;
		return Tool::getArrVal( $_k,$config );
	}

	/**
	 * 从服务器加载配置数据
	 */
	public static function loadConfig(){
		$configData=self::getConfig();
		if($configData){
			Tool::S( self::config_key, Tool::serialize($configData ));
		}else{
			$configData=Tool::S( self::config_key );
			if( $configData){
				$configData = Tool::unserialize( $configData );
			}
		}
		//$jsonStr= Tool::PTDecrypt( $configData );
		//$configArray = JSON::decode( $jsonStr );
		YacCache::getYacInstance()->set( self::config_key, $configData);
		return $configData;
	}

	public static function getConfig() {
		$file=CONFIG_PATH.'/config.php';
		if(!is_file($file)){
			Log::log('配置文件不存在');
		    throw new \Exception('配置文件不存在',93300);
		}
		$configData = include $file;//Tool::httpGet( 'http://config.xxt100.cc/all' );

		return $configData;
	}


}