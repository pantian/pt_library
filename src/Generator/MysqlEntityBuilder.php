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
use PTLibrary\DocParse\ClassDocInfo;

class MysqlEntityBuilder {
	/**
	 * @param string $dbName
	 * @param string $tableName
	 * @param string $savePath
	 * @param string $prefix
	 * @param string $postfix
	 */
	public static function buildingEntityClass( $dbName, $tableName, $savePath = '', $prefix = '', $postfix = '' ) {
		if ( $tableName ) {
			if ( $file = self::createFile( $dbName, $tableName, $savePath, $prefix, $postfix ) ) {
				echo '已完成: ' . $file . PHP_EOL;
			}
		} else {
			$m = new BaseM();
			$m->init();
			$dbName && $m->setDBName( $dbName );
			$tables = $m->getTables();
			if ( $tables ) {
				$n = 0;
				foreach ( $tables as $table => $info ) {
					if ( $file = self::createFile( $dbName, $table, $savePath, $prefix, $postfix ) ) {
						$n ++;
						echo '已完成: ' . $file . PHP_EOL;
					}
				}
				echo '生成: ' . $n . '个实体类 ' . PHP_EOL;
			} else {
				var_dump('数据库 ' . $dbName . ' 下没有数据表' . PHP_EOL);
			}
		}
	}

	/**
	 * 创建文件
	 *
	 * @param string $dbName
	 * @param string $tableName
	 * @param string $savePath
	 * @param string $prefix
	 * @param string $postfix
	 *
	 * @return bool|string
	 */
	public static function createFile( $dbName, $tableName, $savePath = '', $prefix = '', $postfix = '' ) {

		try {
			$m = new BaseM();
			$m->init();
			$m->ignoreTablePrefix();
			$postfix || $postfix = 'Entity';
			$dbName && $m->setDBName( $dbName );
			$tableName && $m->setTable( $tableName );

			$createRes = $m->showCreateTable();
			$indexList=$m->getIndexs();
			$createInfo = '';
			if($createRes){
				$createInfo = $createRes['Create Table'];
				$createInfo=str_replace(PHP_EOL,'',$createInfo);

			}
//			$indexsJsonStr = '';
//			if($indexList){
//			    $indexsJsonStr=json_encode($indexList);
//			}

			$field = $m->PDO->getFields();
			if ( $field ) {
				$nameSpace         = 'App\\Entity\\' . self::getClassName( $dbName );
				$className         = $prefix . self::getClassName( $tableName ) . $postfix;
				$nameSpaceClass    = '\\' . $nameSpace . '\\' . $className;
				$_persist_property = '';
				//保留原来实体的 _开头的属性
				if ( class_exists( $nameSpaceClass ) ) {
					$_persist_property = self::get_persist_property( $nameSpaceClass );
				}
				$str = "<?php
namespace {$nameSpace};

use PTLibrary\\DB\\MysqlEntity;
/**
 *
 *@createTable $createInfo
 */

class {$className} extends MysqlEntity {
	public function __construct( \$id = null ) {
		\$this->_tableName = '{$tableName}';
		\$this->_dbName = '{$dbName}';
		parent::__construct( \$id );
	}
			";
				foreach ( $field as $key => $value ) {
					$type = self::getValType( $value );
					$typeInfo=json_encode($value);
					@$str .= "
	/**
	 * {$value['Comment']}
	 * @Type {$value['Type']}
	 * @TypeInfo {$typeInfo}
	 * @var {$type['type']}  
	 */
	public \${$key}{$type['default']};
";
				}
				$str .= "\n    {$_persist_property}
}";
				$savePath || $savePath = APP_PATH . '/Entity/' . self::getClassName( $dbName );
				if ( $_persist_property ) {
					echo $str;
				}
				Dir::create( $savePath );
				$fileName = $savePath . '/' . $className . '.php';
				if ( file_put_contents( $fileName, $str ) ) {
					return $fileName;
				}

				return false;
			}
		} catch ( Exception $e ) {
			$msg = $e->getMessage();
			echo "\033[1;31m异常：{$msg}\033[0m".PHP_EOL;
		}


	}


	/**
	 * 获取要保留的属性
	 *
	 * @param $class
	 *
	 * @return string
	 */
	public static function get_persist_property( $class ) {
		$persistProperty = ClassDocInfo::getPropertiesDocStr( $class );
		$resStr          = '';
		$defaultValue    = get_class_vars( $class );
		foreach ( $persistProperty as $key => $value ) {
			$default = $defaultValue[ $key ];
			$default = is_null( $default ) ? '' : ' = \'' . $default . '\'';
			if ( $key[0] == '_' ) {
				$resStr .= $value . PHP_EOL;
				$resStr .= "    public \${$key}{$default};\n";
			}
		}

		return $resStr;
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