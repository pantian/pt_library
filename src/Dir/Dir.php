<?php
/**
 * Created by pantian.
 * User: pantian
 * Date: 2016/6/7
 * Time: 16:46
 */

namespace PTLibrary\Dir;

/**
 * 目录处理类
 *
 * @package     tools_class
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
final class Dir
{

	/**
	 * @param string $dir_name 目录名
	 *
	 * @return mixed|string
	 */
	static public function dirPath($dir_name)
	{
		$dirname = str_ireplace("\\", "/", $dir_name);

		return substr($dirname, "-1") == "/" ? $dirname : $dirname . "/";
	}

	/**
	 * 获得文件名扩展名
	 *
	 * @param string $file 文件名
	 *
	 * @return string
	 */
	static public function getExt($file)
	{
		return strtolower(substr(strrchr($file, "."), 1));
	}

	/**
	 * 遍历目录内容
	 *
	 * @param string $dirName 目录名
	 * @param string $exts    读取的文件扩展名
	 * @param int    $son     是否显示子目录
	 * @param array  $list
	 *
	 * @return array
	 */
	static public function tree($dirName = null, $exts = '', $son = 0, $list = array())
	{
		if ( is_null($dirName) ) {
			$dirName = '.';
		}
		$dirPath = self::dirPath($dirName);
		static $id = 0;
		if ( is_array($exts) ) {
			$exts = implode("|", $exts);
		}
		foreach ( glob($dirPath . '*') as $v ) {
			$id++;
			if ( is_dir($v) || !$exts || preg_match("/\.($exts)/i", $v) ) {
				$list [$id] ['type']      = filetype($v);
				$list [$id] ['filename']  = basename($v);
				$path                     = str_replace("\\", "/", realpath($v)) . ( is_dir($v) ? '/' : '' );
				$list [$id] ['path']      = $path;
				$list [$id] ['spath']     = ltrim(str_replace(dirname($_SERVER['SCRIPT_FILENAME']), '', $path), '/');
				$list [$id] ['filemtime'] = filemtime($v);
				$list [$id] ['fileatime'] = fileatime($v);
				$list [$id] ['size']      = is_file($v) ? filesize($v) : self::get_dir_size($v);
				$list [$id] ['iswrite']   = is_writeable($v) ? 1 : 0;
				$list [$id] ['isread']    = is_readable($v) ? 1 : 0;
			}
			if ( $son ) {
				if ( is_dir($v) ) {
					$list = self::tree($v, $exts, $son = 1, $list);
				}
			}
		}

		return $list;
	}

	static public function get_dir_size($f)
	{
		$s = 0;
		foreach ( glob($f . '/*') as $v ) {
			$s += is_file($v) ? filesize($v) : self::get_dir_size($v);
		}

		return $s;
	}

	/**
	 * 只显示目录树
	 *
	 * @param null  $dirName 目录名
	 * @param int   $son
	 * @param int   $pid     父目录ID
	 * @param array $dirs    目录列表
	 *
	 * @return array
	 */
	static public function treeDir($dirName = null, $son = 0, $pid = 0, $dirs = array())
	{
		if ( !$dirName ) {
			$dirName = '.';
		}
		static $id = 0;
		$dirPath = self::dirPath($dirName);
		foreach ( glob($dirPath . "*") as $v ) {
			if ( is_dir($v) ) {
				$id++;
				$dirs [$id] = array( "id" => $id, 'pid' => $pid, "dirname" => basename($v), "dirpath" => $v );
				if ( $son ) {
					$dirs = self::treeDir($v, $son, $id, $dirs);
				}
			}
		}

		return $dirs;
	}

	/**
	 * 遍历目录下的所有文件
	 *
	 * @param null $dirName
	 *
	 * @return array
	 */
	static public function getAllFile($dirName = null)
	{
		if ( !$dirName ) {
			$dirName = '.';
		}
		$files=[];
		$dirPath = self::dirPath($dirName);
		if($handle = opendir($dirPath)){
			while (false !== ($file = readdir($handle))){
				if($file!='.'&&$file!='..'){
					$_f=$dirPath.$file;
					if(is_dir($_f)){
						$files=array_merge($files,self::getAllFile($_f));
					}elseif(is_file($_f)){
						$files[]=$_f;
					}
				}

			}
			closedir($handle);
		}

		return $files;
	}



	/**
	 * 删除目录及文件，支持多层删除目录
	 *
	 * @param string $dirName 目录名
	 *
	 * @return bool
	 */
	static public function del($dirName)
	{
		if ( is_file($dirName) ) {
			unlink($dirName);

			return true;
		}
		$dirPath = self::dirPath($dirName);
		if ( !is_dir($dirPath) ) {
			return true;
		}
		foreach ( glob($dirPath . "*") as $v ) {
			is_dir($v) ? self::del($v) : unlink($v);
		}

		return @rmdir($dirName);
	}

	/**
	 * 批量创建目录
	 *
	 * @param string $dirName 目录名
	 * @param int    $auth    权限
	 *
	 * @return bool
	 */
	static public function create($dirName, $auth = 0755)
	{
		$dirPath = self::dirPath($dirName);
		if ( is_dir($dirPath) ) {
			return true;
		}
		$dirs = explode('/', $dirPath);
		$dir  = '';
		foreach ( $dirs as $v ) {
			$dir .= $v . '/';
			if ( is_dir($dir) ) {
				continue;
			}
			mkdir($dir, $auth);
		}

		return is_dir($dirPath);
	}

	/**
	 * 复制目录
	 *
	 * @param string $oldDir      原目录
	 * @param string $newDir      目标目录
	 * @param bool   $strip_space 去空白去注释
	 *
	 * @return bool
	 */
	static public function copy($oldDir, $newDir, $strip_space = false)
	{
		$oldDir = self::dirPath($oldDir);
		$newDir = self::dirPath($newDir);
		if ( !is_dir($oldDir) ) {
			return false;
		}
		if ( !is_dir($newDir) ) {
			self::create($newDir);
		}
		foreach ( glob($oldDir . '*') as $v ) {
			$to = $newDir . basename($v);
			if ( is_file($to) ) {
				continue;
			}
			if ( is_dir($v) ) {
				self::copy($v, $to, $strip_space);
			} else {
				if ( $strip_space ) {
					$data = file_get_contents($v);
					file_put_contents($to, strip_space($data));
				} else {
					copy($v, $to);
				}
				chmod($to, "0755");
			}
		}

		return true;
	}

	/**
	 * 目录下创建安全文件
	 *
	 * @param      $dirName   操作目录
	 * @param bool $recursive 为true会递归的对子目录也创建安全文件
	 */
	static public function safeFile($dirName, $recursive = false)
	{
		$file = HDPHP_TPL_PATH . '/index.html';
		if ( !is_dir($dirName) ) {
			return;
		}
		$dirPath = self::dirPath($dirName);
		/**
		 * 创建安全文件
		 */
		if ( !is_file($dirPath . 'index.html') ) {
			copy($file, $dirPath . 'index.html');
		}
		/**
		 * 操作子目录
		 */
		if ( $recursive ) {
			foreach ( glob($dirPath . "*") as $d ) {
				is_dir($d) and self::safeFile($d);
			}
		}
	}

}