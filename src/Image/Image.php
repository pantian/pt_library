<?php

namespace PTLibrary\Image;

use AliYunOss\UploadFileOss;
use PTLibrary\Dir\Dir;
use PTLibrary\Exception\ThrowException;


/**
 * 图片处理类
 * Class Image
 *
 * @package ptphp\libs\image
 */
class Image {
	/**
	 * 允许上传图片类型
	 *
	 * @var array
	 */
	public $allowType = [ 'gif', 'png', 'jpg', 'jpeg' ];
	/**
	 * 保存路径
	 *
	 * @var string
	 */
	private $savePath = '';
	/**
	 * 保存的文件名
	 *
	 * @var string
	 */
	private $fileName = '';
	/**
	 * 图片宽度
	 *
	 * @var int
	 */
	private $_width = 0;
	/**
	 * 图片高度
	 *
	 * @var int
	 */
	private $_height = 0;

	/**
	 * 文件类型
	 *
	 * @var string
	 */
	private $fileType = '';
	/**
	 * 错误记录
	 *
	 * @var array
	 */
	private $ErrLog = [];
	/**
	 * 上传最大文件
	 *
	 * @var int
	 */
	private $MaxSize = 2097152;

	protected $_image_file = '';

	private $_image_type = '';

	private $_fixType = '';
	/**
	 * 最大宽度
	 * @var int
	 */
	private $_maxWidth=0;


	/**
	 * @return int
	 */
	public function getMaxSize(): int {
		return $this->MaxSize;
	}

	/**
	 * @param int $MaxSize
	 */
	public function setMaxSize( int $MaxSize ) {
		$this->MaxSize = $MaxSize;
	}


	/**
	 * @return string
	 */
	public function getFileType() {
		return $this->fileType;
	}

	/**
	 * @param $fileType
	 *
	 * @return $this
	 */
	public function setFileType( $fileType ) {
		$this->fileType = $fileType;

		return $this;
	}

	/**
	 * 文件名类型
	 *
	 * @var string
	 */
	private $file_name_type = self::FILE_TYPE_DATE;

	const FILE_TYPE_DATE = 'date';
	const FILE_TYPE_UNIQID = 'uniqid';

	function setFileNameType( $type = self::FILE_TYPE_DATE ) {
		$this->file_name_type = $type;
	}


	/**
	 * @param bool   $tmp      临时文件
	 * @param string $fileName 原文件名,主要是用于生成hash
	 *
	 * @return string
	 *
	 */
	public function getFileName( $tmp = false, $fileName = null ) {
		if ( $tmp ) {
			$this->fileName = Tool::getArrVal( 'tmp_path', Tool::getConfig( 'upload' ) );
		} else {
			if ( $this->file_name_type == self::FILE_TYPE_DATE ) {
				$this->fileName = date( 'Y/m/d/' );
			}
		}
		if ( $fileName && is_file( $fileName ) ) {
			$hash = md5( file_get_contents( $fileName ) );
		} else {
			$hash = uniqid();
		}
		$this->fileName .= $hash . '.' . $this->getFileType();

		return $this->fileName;
	}

	/**
	 * @param string $fileName
	 *
	 * @return $this
	 */
	public function setFileName( $fileName ) {
		$this->fileName = $fileName;

		return $this;
	}


	/**
	 * 图片保存目录
	 *
	 * @return string
	 */
	public function getSavePath() {
		if(!$this->savePath){
			ThrowException::SystemException(2011,'没有设置保存目录');
		}
		$path=$this->savePath.(substr($this->savePath,-1,1)=='/'?'':'/').date('Y/m/d/');
		Dir::create( $path );
		if(!is_writeable($path)){
			ThrowException::SystemException(2012,$path.'目录不可写');
		}
		return $path;
	}

	/**
	 * @param string $savePath
	 */
	public function setSavePath( $savePath ) {
		if ( ! is_dir( $savePath ) ) {

			if ( ! Dir::create( $savePath ) ) {
				$this->ErrLog[] = '创建目录失败:' . $savePath;
			}
		}
		$this->savePath = $savePath;
	}

	/**
	 * @return array
	 */
	public function getAllowType() {
		return $this->allowType;
	}

	/**
	 * @param array $allowType
	 */
	public function setAllowType( $allowType ) {
		$this->allowType = $allowType;
	}

	/**
	 * 获取错误信息
	 *
	 * @return array
	 */
	function getErrLog() {
		return $this->ErrLog;
	}

	/**
	 * 重置大小的参数
	 *
	 * @var array
	 */
	protected $resizeOpt = array();

	public function resize( array $opt = array( 'width' => 200, 'height' => 0 ) ) {
		$this->resizeOpt = $opt;

		return $this;
	}

	/**
	 * 检测图片文件的类型限制
	 */
	public function chkAllowType() {
		if(!$this->_image_type){
		    $this->getImageInfo();
		}
		if(strtolower(substr($this->_image_type,0,5))!=='image'){
		    ThrowException::SystemException(2005,'不是图片类型');
		}
		$this->_fixType=strtolower(substr($this->_image_type,6));
		if(!in_array($this->_fixType,$this->allowType)){
		    ThrowException::SystemException(2006,'不允许上传的图片类型');
		}
	}

	/**
	 * 检测图片文件是否有效
	 */
	protected function chkFile() {
		if ( ! $this->_image_file ) {
			ThrowException::SystemException( 2001, '图片文件为空' );
		}
		if ( ! is_file( $this->_image_file ) ) {
			ThrowException::SystemException( 2002, '图片文件不存在' );
		}
	}

	/**
	 * 检测请允许的文件大小
	 */
	protected function chkAllowSize() {
		$size = filesize( $this->_image_file );
		if ( $size >$this->getMaxSize()) {
			ThrowException::SystemException(2004,'图片文件大小超过限制');
		}
	}

	/**
	 * 获取图片基本信息
	 */
	protected function getImageInfo() {
		$imgInfo = getimagesize( $this->_image_file );
		if ( $imgInfo ) {
			$this->_width      = $imgInfo[0];
			$this->_height     = $imgInfo[1];
			$this->_image_type = $imgInfo['mime'];
		} else {
			ThrowException::SystemException( 2003, '图片信息错误' );
		}
	}

	/**
	 * @return string
	 */
	public function getImageFile(): string {
		return $this->_image_file;
	}

	/**
	 * @param string $image_file
	 */
	public function setImageFile( string $image_file ) {
		$this->_image_file = $image_file;
	}

	public function getHashName() {
		$this->chkFile();
		$hashName=sha1(file_get_contents($this->_image_file));

		return $hashName;
	}

	/**
	 * @return string
	 */
	public function getSaveFileName( ) {
		if($this->_image_file){
			$file_name = $this->getHashName().$this->getFixTypeName();
			$toSavePath = $this->getSavePath().$file_name;

			return $toSavePath;
		}
		ThrowException::SystemException( 2010, '文件名不存在' );
	}

	public function getFixTypeName(){
	    return  '.' . ($this->_fixType=='jpeg'?'jpg':$this->_fixType);
	}

	/**
	 * 检测是否可剪切
	 * @return bool
	 */
	private function chkCut() {
		return $this->_width > $this->_maxWidth;
	}

	/**
	 * 剪切图片
	 * @param $saveFile
	 *
	 * @return bool|string
	 */
	protected function doCut($saveFile) {
		if($this->chkCut()){
			$_new_width=$this->_maxWidth;
			$_new_height=($_new_width/$this->_width)*$this->_height;
		}else{
			$_new_width=$this->_width;
			$_new_height = $this->_height;
		}
		$output = '';
		switch ($this->_fixType){
			case 'jpeg':
				$src = imagecreatefromjpeg($this->_image_file);
				$tmp = imagecreatetruecolor($_new_width,$_new_height);
				imagecopyresized($tmp, $src, 0, 0, 0, 0, $_new_width, $_new_height, $this->_width, $this->_height);
				$output = imagejpeg($tmp, $saveFile,90);
				break;
			case 'png':

				$src = imagecreatefrompng($this->_image_file);
				$tmp = imagecreatetruecolor($_new_width,$_new_height);
				imagecopyresampled($tmp, $src, 0, 0, 0, 0, $_new_width, $_new_height, $this->_width, $this->_height);
				$output = imagepng($tmp, $saveFile,9);
				break;
		}
		//var_dump( $output ,$this->_fixType,$saveFile);

		if(!$output){
		    ThrowException::SystemException(2013,'图片修剪保存失败');
		}
		return $saveFile;
	}

	/**
	 * @return int
	 */
	public function getMaxWidth(): int {
		return $this->_maxWidth;
	}

	/**
	 * @param int $maxWidth
	 */
	public function setMaxWidth( int $maxWidth ) {
		$this->_maxWidth = $maxWidth;
	}

	/**
	 * 保存文件,宽度自动修剪
	 * @param $file
	 *
	 * @return bool|string|void
	 */
	public function saveImageByFile( $file ='',$type=1) {
		if($file){
			$this->_image_file = $file;
		}
		$this->chkFile();
		$this->getImageInfo();
		$this->chkAllowSize();
		$this->chkAllowType();

		$file_name=$this->getSaveFileName();

		if($this->_fixType !='gif' && $this->chkCut()){
			$res=$this->doCut( $file_name );
		}else{
            var_dump($type);
            if($type==1){
                $res=move_uploaded_file( $this->_image_file, $file_name );//2022-7-26修改 pantian
                var_dump('move_uploaded_file',$res);
            }else{
                $res=rename( $this->_image_file, $file_name );
            }
		}
        var_dump($res);
		if($res){
			return $file_name;
		}

		return $res;
	}


	/**
	 * 对上传时保存文件
	 *
	 * @param      $fileData
	 * @param bool $tmp
	 *
	 * @return bool
	 */
	function save( $fileData ) {
		$file=$fileData['tmp_name'] ;
		$res=$this->saveImageByFile($file);

	}











}


