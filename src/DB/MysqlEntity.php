<?php

namespace PTLibrary\DB;


use PTLibrary\Tool\Context;

/**
 * mysql实体基类
 * Class MysqlEntity
 *
 * @package DB
 */
class MysqlEntity extends Entity {

	/**
	 * @var \PTLibrary\DB\BaseM
	 */
	private $_mod;


	/**
	 * MysqlEntity constructor.
	 *
	 * @param null $id
	 */
	public function __construct( $id = null ) {

	}

	public function __destruct() {

	}


	function __clone() {


	}
	/**
	 * 删除 container对象,在SOA之间传输的时候，pdo 对象不序列化
	 */
	public function unsetContainer(){
	    $this->_container=null;
	}

	public function init() {


	}

	/**
	 * 获取模型
	 * @return \PTLibrary\DB\BaseM
	 * @throws \PTLibrary\DB\DBException
	 */
	public function getMod(){
	    if(!$this->_mod){
	        $this->_mod=BaseM::instance();
	    }
	    //print_r($this->mod);
	    $this->_mod->init();
	    $this->_dbName && $this->_mod->setDBName($this->_dbName);
	    $this->_tableName && $this->_mod->setTable($this->_tableName);
	    $this->_mod->_entity=$this;
		return $this->_mod;
	}

	function getDBTable() {

		return $this->_dbName.'.'.$this->_tableName;
	}

	/**
	 * 更新
	 *
	 * @return bool|int
	 */
	public function update() {
		return $this->getContainer()->update();
	}

	/**
	 * 保存
	 *
	 * 参数Data 要保存的数据数组，并且要包含主键，主键成为保存的条件，如果为空，则是取实体数据
	 * 此方法不宜用于高并发下的数据操作
	 *
	 * @param array $data
	 *
	 * @return bool|int|mixed
	 */
	public function save( array $data = [] ) {
		if($data){
			$this->setData( $data );
		}
		if(!$this->_mod){
			$this->getMod();
		}
		$_d = $this->getDataToArr();
        print_r($_d);
		$res=$this->_mod->PDO->saveOrUpdate($_d);
		$this->_mod->updateAfter($_d);
		return $res;

	}

	/**
	 * 重置数据key
	 * @param $data
	 */
	public function resetData(&$data){
	    foreach ($data as $key=>$value){
	    	if(substr($key,0,3)==':w_'){
			    $k=substr($key,3);
			    $data[$k]=$value;
		    }else if(substr($key,0,1)==':'){
			    $k=substr($key,1);
			    $data[$k]=$value;
		    }
	    }
	}

	/**
	 * 设置主键值,同时查询数据库
	 * @param $id
	 *
	 * @return bool|void
	 * @throws \PTLibrary\DB\DBException
	 */
	public function setId( $id ) {
		if ( ! $id ) {
			return false;
		}
		if(!$this->_mod){

			$m    =$this->getMod();
		}else{
			$m = $this->_mod;
		}
		$info = $m->where( [ $m->getPK() => $id ] )->find();
		if ( $info ) {
			$this->setData( $info );
			unset( $info );
			return true;
		}

		return false;
	}

	function getProperty( $key = null, $default = null ) {
		if ( is_string( $key ) ) {
			if ( isset( $this->$key ) ) {
				return [ $key => $this->$key ];
			}
		} elseif ( is_array( $key ) ) {
			return $this->getPropertyByArray( $key, $default );
		} elseif ( is_null( $key ) ) {
			$arr = get_class_vars( get_class( $this ) );
			if ( $arr ) {
				foreach ( $arr as $key => $item ) {
					if ( substr( $key, 0, 1 ) == '_' ) {
						unset( $arr[ $key ] );
					}

				}
			}
			$data = $this->getPropertyByArray( $arr, $default );
			unset( $arr );
			return $data;
		}

		return false;
	}

	/**
	 * 以数组的形式返回实例数据
	 * @param bool $filter_null 是否过滤null值的字段
	 * @return array
	 */
	function getDataToArr( $filter_null = false ) {

		$data = $this->getPropertyByArray( $this->getProperty() );
		if ( $filter_null === true ) {
			foreach ( $data as $key => $value ) {
				if ( is_null( $value ) ) {
					unset( $data[ $key ] );
				}
			}
		}

		return $data;
	}

	/**
	 * 设置数据
	 * 如果没有数据，则把_id也设为空
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	function setData( $data ) {
		if ( $data ) {
			//$argData = $this->getProperty();
			foreach ( $data as $key => $val ) {
				//if ( isset($argData[$key]) ) {
				$this->$key = $val;
				//}
			}

			return true;
		}

		return false;
	}

	/**
	 * 通过一个字段值获取信息
	 * @param $field
	 * @param $filed_value
	 *
	 * @return bool|mixed
	 */
	public function getInfoBy( $field, $filed_value ) {
		$where[$field] = $filed_value;
		$info = $this->getMod()->where( $where )->find();
		if($info){
			$this->setData( $info );
			return $info;
		}
		return false;
	}

}
