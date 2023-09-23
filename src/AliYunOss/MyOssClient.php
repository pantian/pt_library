<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/8
 * Time: 22:48
 */

namespace PTLibrary\AliYunOss;


use Exception\ThrowException;
use OSS\OssClient;

class MyOssClient {
	private static $ossClient;

	/**
	 * 获取oss客户端
	 * @return \OSS\OssClient
	 */
	public static function getClient() {
		if(!self::$ossClient){
			$accessKeyId     = AliYunOssConfig::getAccessKeyId();
			$accessKeySecret = AliYunOssConfig::getAccessKeySecret();
			$endpoint        = AliYunOssConfig::getEndpoint();
			if(!$accessKeyId || !$accessKeySecret || !$endpoint){
			    ThrowException::SystemException(29332,'图片OSS配置错误');
			}
			self::$ossClient = new OssClient( $accessKeyId, $accessKeySecret, $endpoint );
		}

		return self::$ossClient;
	}
}