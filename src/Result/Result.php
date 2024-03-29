<?php

namespace PTLibrary\Result;


use PTLibrary\Factory\InstanceFactory;
use PTLibrary\Tool\Context;
use PTLibrary\Tool\JSON;
use PTLibrary\Tool\Tool;

/**
 * json 输出对象
 * Class outJson
 *
 * @package PTPhp\Libs
 */
class Result {

	protected $data;
	protected static $instance = null;

	public function __construct() {
		$this->init();
	}

	public function __clone()
	{
		$this->init();
	}

	/**
	 *
	 * @return $this
	 */
	public function init() {
		$this->data = array( 'code' => 0, 'msg' => '', 'data' => '','_time'=>time() ,'_request_id'=>'');
		return $this;
	}

	/**
	 * @return string
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData( $data ) {
		$this->data['data'] = $data;
	}

	public function setRequestId($id){
	    $this->data['_request_id']=$id;
	}

	public function getReuestId(){
	    return $this->data['_request_id'];
	}

	/**
	 * 设置所有数据，全新的data
	 *
	 * @param array $data
	 */
	public function setAllData( array $data ) {
		$this->data = $data;
	}

	public function setAction($action){
	    $this->data['action']=$action;
	}
	/**
	 * 设置数据
	 * this->data[$key]=$val
	 *
	 * @param $key
	 * @param $val
	 */
	function set( $key, $val ) {
		$this->data[ $key ] = $val;
	}

	/**
	 * 设数据data数组下的数据
	 *
	 * $this->data['data'][$key] = $val;
	 *
	 * @param $key
	 * @param $val
	 */
	function setForData( $key, $val ) {
		$this->data['data'][ $key ] = $val;
	}

	function setCode( $code ) {
		$this->data['code'] = $code;
	}

	/**
	 * @version 2.1
	 *
	 * @param $msg
	 * @param $code
	 */
	public function setCodeMsg( $msg, $code ) {
		$this->setCode( $code );
		$this->setMsg( $msg );
	}


	/**
	 * @return int
	 */
	public function getErrCode() {
		return $this->data['code'];
	}

	/**
	 * @return string
	 */
	public function getMsg() {
		return $this->data['msg'];
	}

	/**
	 * @param string $msg
	 */
	public function setMsg( $msg ) {
		$this->data['msg'] = $msg;
	}

	/**
	 * 返回 json格式
	 *
	 * @return bool|string
	 */
	function getJson() {

		if ( is_array( $this->data ) ) {
			$this->data['_time'] = time();

			return JSON::encode( $this->data );
		}
		return '';
	}

	public function __toString() {
		return $this->getJson();
	}

	static $instanceKey='resultInstance';
	static $_instance=null;
	static function Instance() {
		if(!self::$_instance)self::$_instance=new self();

		$instance =Context::get(self::$instanceKey);
		if ( ! $instance ) {
			$instance=InstanceFactory::cloneInstance(self::class);
			Context::set(self::$instanceKey,$instance);
		}

		//$instance->set('cid',\Swoole\Coroutine::getCid());
		return $instance;
	}

	/**
	 * 协程结束，释放对象
	 * @return void
	 */
	public static function destroy(){
	    Context::destroy(self::$instanceKey);
	}

}