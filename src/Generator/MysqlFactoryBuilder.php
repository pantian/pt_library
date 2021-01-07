<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/13
 * Time: 22:44
 */

namespace PTLibrary\Generator;

use PTLibrary\DB\BaseM;
use PTLibrary\Dir\Dir;

/**
 * Class MysqlFactoryBuilder
 *
 * @package Generator
 */
class MysqlFactoryBuilder {
	/**
	 * @param        $dbName
	 * @param string $savePath
	 * @param string $classPath
	 */
	public static function buildingFactoryClass( $dbName, $savePath = '', $classPath = '' ) {

		$classPath || $classPath = APP_PATH . '/Entity/' . self::getClassName( $dbName );//实体所在目录
		$_classFile = Dir::tree( $classPath );
		if ( $_classFile ) {
			if ( $file = self::createFile( $dbName, $_classFile, $savePath ) ) {
				echo '已完成: ' . $file . PHP_EOL;
			}
		}

	}

	/**
	 * 创建文件
	 */
	public static function createFile( $dbName, $_classFiles, $savePath = '' ) {

		$saveClassName = self::getClassName( $dbName ) . 'EntityFactory';
		$savePath || $savePath = APP_PATH . '/Factory/';
		Dir::create( $savePath );
		$saveClassFile = $savePath . $saveClassName . '.php';

		$_db_nameSpace = self::getClassName( $dbName );


		$str
			= "<?php

namespace App\Factory;

use PTLibrary\Exception\DBException;
use PTLibrary\Error\ErrorHandler;
use PTLibrary\Factory\EntityFactoryBase;
class {$saveClassName} extends EntityFactoryBase {
";

		foreach ( $_classFiles as $_fileInfo ) {
			list( $className, $pix ) = explode( '.', $_fileInfo['filename'] );
			$className = self::getClassName( $className );
			$str.= "   /**
	* 
	* 			
	* @param mixed \$id
	* @return \\App\Entity\\{$_db_nameSpace}\\{$className}
	* @throws \\PTLibrary\\Exception\\DBException
	*/
	public static function {$className}(){
		\$instance=parent::instance(\\App\\Entity\\{$_db_nameSpace}\\{$className}::class);
		if(!\$instance){
			throw new DBException(ErrorHandler::GET_CONTROL_INSTANCE_EXCEPTION,'\\App\\Entity\\{$_db_nameSpace}\\{$className} 生成实例失败');
		}
		return \$instance;
	}
";
		}
		$str
			.= "
}";

		
		if ( file_put_contents( $saveClassFile, $str ) ) {
			return $saveClassFile;
		}

		return false;

	}


	/**
	 * @param $fieldData
	 *
	 * @return array
	 */
	public static function getValType( $fieldData ) {
		$type    = $fieldData['Type'];
		$default = $fieldData['Null'] == 'NO' ? $fieldData['Default'] : '';

		if ( strpos( $type, 'int' ) !== false ) {
			if ( $fieldData['Extra'] == 'auto_increment' ) {
				$default = 0;
			}

			$needType = 'int';
		} else if ( strpos( $type, 'float' ) !== false || strpos( $type, 'decimal' ) !== false ) {
			$needType = 'float';
		} else {
			$default  = "'$default'";
			$needType = 'string';
		}

		return [
			'default' => strlen( $default ) > 0 ? ' = ' . $default : '',
			'type'    => $needType,
		];
	}

	public static function getClassName( $tableName ) {
		if ( $tableName ) {
			$str1 = preg_replace( '/\_+/', ' ', $tableName );
			$str2 = ucwords( $str1 );

			return preg_replace( '/\s+/', '', $str2 );
		}
	}
}