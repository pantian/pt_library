<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/4
 * Time: 22:16
 */

namespace PTLibrary\Config;

use PTLibrary\Cache\YacCache;
use PTLibrary\Log\Log;
use PTLibrary\Tool\Tool;



class Config {
	private static $configData = [];

	const config_key = 'configData';

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
	public static function get($key){
		$config = self::getConfigData();

		return Tool::getArrVal(  $key, $config );
	}
	public static function getConfigData(){
	   return self::loadConfig();
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


	public static function getAliYunOssConfig(){
		$config = self::getConfigData();

		return Tool::getArrVal( 'oss', $config );
	}

	/**
	 * @param string $key
	 *
	 * @return bool|null
	 */
	public static function getApiConfig(){
		$key='api';
		$config = self::getConfigData();
		return Tool::getArrVal($key,$config);

	}

	/**
	 * 从服务器加载配置数据
	 */
	public static function loadConfig(){
		$configData=self::getConfig();
		YacCache::getYacInstance()->set( self::config_key, $configData);
		return $configData;
	}

	public static function getConfig() {
		if(self::$configData)return self::$configData;
		$file=CONFIG_PATH.'/config.php';
		if(!is_file($file)){
			Log::log('配置文件不存在');
		    throw new \Exception('配置文件不存在',93300);
		}
		$configData = include $file;//Tool::httpGet( 'http://config.xxt100.cc/all' );
		self::$configData = $configData;
		return $configData;
	}


}