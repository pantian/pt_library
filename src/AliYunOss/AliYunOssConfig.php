<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/8 0008
 * Time: 16:22
 */

namespace AliYunOss;
include_once __DIR__.'/aliyun-oss-php-sdk-2.2.4/autoload.php';

use Config\Config;
use Tool\Tool;

class AliYunOssConfig {

	public static function getAccessKeyId(){
		$config=Config::getAliYunOssConfig();

		return Tool::getArrVal( 'AccessKeyId', $config );
	}

	public static function getUploadPath(){
		$config=Config::getAliYunOssConfig();

		return Tool::getArrVal( 'uploadpath', $config );
	}
	public static function getBucketName(){
		$config=Config::getAliYunOssConfig();

		return Tool::getArrVal( 'bucketName', $config );
	}



	public static function getAccessKeySecret(){
		$config=Config::getAliYunOssConfig();

		return Tool::getArrVal( 'AccessKeySecret', $config );
	}

	/**
	 * 访问OSS域名
	 * @return bool|null
	 */
	public static function getEndpoint(){
		$config=Config::getAliYunOssConfig();

		return Tool::getArrVal( 'Endpoint', $config );
	}

	/**
	 * 外部
	 * @return bool|null
	 */
	public static function getAccessDomain(){
		$config=Config::getAliYunOssConfig();

		return Tool::getArrVal( 'access_domain', $config );
	}

}