<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/9 0009
 * Time: 14:18
 */

namespace PTLibrary\Image;


use AliYunOss\AliYunOssConfig;
use App\Factory\HotEntityFactory;
use App\Library\AliYunOss\UploadFileOss;
use PTLibrary\Log\Log;

/**
 *
 * Class ImageUpload
 *
 * @package PTLibrary\Image
 */
class ImageUpload {
	/**
	 *
	 * @param   string     $file 原文件
	 * @param string $uid
	 *
	 * @return array
	 * @throws \Exception\DBException
	 * @throws \Exception\SystemException
	 */
	public static function saveImage( $file, $uid = '' ) {
		$returnArr = [];
		$image     = new Image();
		defined('DOCUMENT_ROOT') || define('DOCUMENT_ROOT',APP_PATH);
		$image->setSavePath( DOCUMENT_ROOT . '/images/' );
		$image->setMaxWidth( 1200 );
		$image->setImageFile( $file );
		$hashName = $image->getHashName();
		$config=\App\Library\AliYunOss\AliYunOssConfig::instance();
		$userImageMod = HotEntityFactory::UserImageEntity()->getMod();
		$imageMod      = HotEntityFactory::ImagesEntity()->getMod();
		$ImgInfo       = $imageMod->where( [ 'id' => $hashName ] )->find();
		$domain        = $config->getAccessDomain();
        var_dump($ImgInfo);
		if ( ! $ImgInfo ) {

			//图片保存处理
			$res = $image->saveImageByFile();
			var_dump($res);

			//上传oss
			if ( ! $res ) {
				return [];
			} else {

				$oss_file = UploadFileOss::uploadFile( $res, $hashName . $image->getFixTypeName() );
//				$oss_file='';
				$url                  = $domain . $oss_file;
				$dataImage['id']      = $hashName;
				$dataImage['url']     = $url;
				//$dataImage['domain']  = $domain;
				//$dataImage['path']    = $oss_file;
				$dataImage['created'] = time();
				$dataImage['file']    = $oss_file;
				$imageMod->add( $dataImage );
				$returnArr['url'] = $url;
				unlink( $oss_file );
				if ( $uid ) {
					$userImageData['uid']     = $uid;
					$userImageData['img_id']  = $hashName;
					$userImageData['created'] = time();

					$userImageMod->add($userImageData);
				}
			}

		} else {
			$returnArr['url']=$ImgInfo['url'];
		}

		return $returnArr;
	}
	

}