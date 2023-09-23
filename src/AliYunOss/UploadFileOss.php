<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/8
 * Time: 22:45
 */

namespace AliYunOss;


use Error\ErrorHandler;
use Exception\ThrowException;
use Log\Log;

class UploadFileOss {
	/**
	 *
	 * @param $file
	 * @param $toFileName
	 *
	 * @return string
	 * @throws \Exception\SystemException
	 */
	public static function uploadFile( $file, $toFileName ) {

			$ossClient = MyOssClient::getClient();
			$bucket    = AliYunOssConfig::getBucketName();

			$path      = AliYunOssConfig::getUploadPath().date( '/Y/m/d/' ) . $toFileName;
			$content   = file_get_contents( $file );
			if ( ! $content ) {
				ThrowException::SystemException( ErrorHandler::NOT_DATA, 'OSS上传文件内容为空' );
			}
			if(!$ossClient->putObject( $bucket, $path, $content )){
				Log::error( '图片上传失败' );
			}
			return $path;


	}
}