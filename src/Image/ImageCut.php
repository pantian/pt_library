<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/20
 * Time: 10:17
 */

namespace PTLibrary\Image;
use Dir\Dir;
use Tool\Tool;


/**
 * 图片剪切
 * Class imageCut
 *
 * @package ptphp\libs\image
 */
class ImageCut {
	/**
	 *剪切起点x坐标
	 *
	 * @var int
	 */
	public $startX = 0;
	/**
	 * 剪切起点y坐标
	 *
	 * @var int
	 */
	public $startY = 0;
	/**
	 * 剪切宽度
	 *
	 * @var int
	 */
	public $cutW = 0;
	/**
	 * 剪切高度
	 *
	 * @var int
	 *
	 */
	public $cutH = 0;
	/**
	 * 剪切区域宽度
	 *
	 * @var int
	 */
	public $cutArea_w = 0;
	/**
	 * 剪切区域高度
	 *
	 * @var int
	 */
	public $cutArea_h = 0;

	/**
	 * 图片信息
	 *
	 * @var array
	 */
	public $imageInfo = array();
	/**
	 * 错误信息
	 *
	 * @var array
	 */
	public $errorMsg = array();
	/**
	 * 图片对象
	 *
	 * @var null
	 */
	public $m = null;
	/**
	 * 图片类型
	 *
	 * @var string
	 *
	 */
	public $imageType = 'jpg';
	/**
	 * 剪切的原图宽度压缩后的最大值，如果图片实际宽度大于这个值，则算出比例
	 *
	 * @var int
	 */
	public $imgEase_max_width = 0;

	/**
	 * @param null $imageFileName 图片文件
	 */
	function __construct( $imageFileName = null ) {
		if ( $imageFileName ) {
			$this->openImage( $imageFileName );
		}
	}

	/**
	 *  打开图片
	 *
	 * @param string $imageFileName 图片文件名
	 *
	 * @return mixed
	 */
	function openImage( $imageFileName ) {
		if ( $this->getImageInfo( $imageFileName ) ) {
			switch ( $this->imageInfo[2] ) {
				case 1:
					$this->m         = imagecreatefromgif( $imageFileName );
					$this->imageType = 'gif';
					break;
				case 2:
					$this->imageType = 'jpg';
					$this->m         = imagecreatefromjpeg( $imageFileName );
					break;
				case 3:
					$this->imageType = 'png';
					$this->m         = imagecreatefrompng( $imageFileName );
					break;
				default:
					$this->errorMsg[] = '不支持的图片类型';
					break;
			}
		} else {
			$this->errorMsg[] = '获取图片信息失败';
		}

		return $this;
	}

	/**
	 * 获取图片信息
	 *
	 * @param string $imageFileName 图片文件
	 *
	 * @return array|bool
	 */
	public function getImageInfo( $imageFileName ) {
		if ( file_exists( $imageFileName ) ) {
			$this->imageInfo = getimagesize( $imageFileName );

			return $this->imageInfo;
		} else {
			$this->errorMsg[] = '图片不存在';
		}

		return false;
	}

	/**
	 * 实例化
	 *
	 * @param $imageFileName
	 *
	 * @return ImageCut
	 */
	static function instance( $imageFileName ) {
		return new ImageCut( $imageFileName );
	}


	/**
	 *  剪切操作
	 *
	 * @param null $saveToFile 新保存的文件名
	 *
	 * @return bool
	 */

	function cut( $saveToFile ) {

		if ( $this->m ) {
			if ( $this->imgEase_max_width && $this->imageInfo[0] > $this->imgEase_max_width ) {
				$proportion = $this->imageInfo[0] / $this->imgEase_max_width;
			} else {
				$proportion = 1;
			}

			$this->cutArea_w = $this->cutArea_w * $proportion;
			$this->cutArea_h = $this->cutArea_h * $proportion;
			$this->startX    = $this->startX * $proportion;
			$this->startY    = $this->startY * $proportion;
			$dst_image       = imagecreatetruecolor( $this->cutW, $this->cutH );

			imagecopyresized( $dst_image, $this->m, 0, 0, $this->startX, $this->startY, $this->cutW, $this->cutH, $this->cutArea_w,
				$this->cutArea_h );

			return $this->saveCut( $dst_image, $saveToFile );
		}

		return false;
	}

	/**
	 * 保存缩略图片
	 * 如果指定条边,则另一边自动按比例计算
	 *
	 * @param       $file
	 * @param array $opt
	 *
	 * @return bool
	 */
	public function thumb( $file, array $opt = array( 'width' => 200, 'height' => 0 ) ) {
		$file .= '.' . $this->imageType;
		$_src_w = $this->imageInfo[0];
		$_src_h = $this->imageInfo[1];
		$width  = Tool::getArrVal( 'width', $opt );
		$height = Tool::getArrVal( 'height', $opt );

		if ( $width > 0 && ! $height ) {
			$width      = $width < $_src_w ? $width : $_src_w;
			$proportion = $_src_h / $_src_w;
			$height     = (int) ( $width * $proportion );
		} elseif ( $height > 0 && ! $width ) {
			$height     = $height < $_src_h ? $height : $_src_h;
			$proportion = $_src_w / $_src_h;
			$width      = (int) ( $height * $proportion );
		}

		$dst_image = imagecreatetruecolor( $width, $height );
		$alpha = imagecolorallocatealpha($dst_image, 0, 0, 0, 127);
		imagefill($dst_image, 0, 0, $alpha);
		imagesavealpha($dst_image, true);

		imagecopyresampled( $dst_image, $this->m, 0, 0, 0, 0, $width, $height, $this->imageInfo[0],
			$this->imageInfo[1] );
		$res = $this->saveCut( $dst_image, DOCUMENT_ROOT.$file );
		if ( $res ) {
			$data['file'] = $file;
			return $data;
		} else {
			return false;
		}
	}


	/**
	 *  保存剪切的图片
	 *
	 * @param object $m    图片对象
	 * @param string $file 文件名
	 *
	 * @return bool
	 */
	private function saveCut( $m, $file ) {
		if ( $m ) {
			$path = dirname( $file );
			if(!is_dir( $path )){
				Dir::create( $path );
			}
			switch ( $this->imageInfo[2] ) {
				case 1:
					imagegif( $m, $file );
					break;
				case 2:
					imagejpeg( $m, $file );
					break;
				case 3:
					imagepng( $m, $file );
					break;
			}
			imagedestroy( $m );

			return true;
		}

		return false;
	}
}


