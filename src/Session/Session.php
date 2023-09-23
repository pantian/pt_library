<?php


namespace PTLibrary\Session;


use PTFramework\Config;
use PTLibrary\Factory\InstanceFactory;
use PTLibrary\Tool\Context;
use PTLibrary\Tool\Request;
use PTLibrary\Tool\Tool;
use Simps\DB\Redis;

class Session {
	private $session_id = '';

	private $data = [];

	private $sessionTimeOut = 86400;

	private $session_key = '';


	/**
	 * @return self
	 */
	public static function instance() {
		$instance = Context::get( 'session' );
		if ( ! $instance ) {
			$instance                 = InstanceFactory::cloneInstance( self::class );
			$appConfig                = Config::getInstance()->get( 'config' );
			$instance->session_key    = trim( Tool::getArrVal( 'session_key', $appConfig ) );
			$instance->sessionTimeout = Tool::getArrVal( 'session_timeout', $appConfig  );
			Context::set( 'session', $instance );
		}

		return $instance;
	}

	public function start( $session_id = null ) {
		$this->sessionId();
		$this->read();
	}

	/**
	 * 请session数据
	 */
	public function read(){
		if(!$this->session_id){$this->sessionId();}
		if($this->session_id){
			$contentStr = \PTLibrary\Cache\Redis::get( $this->session_id );
			if ( $contentStr ) {
				$this->data = unserialize( $contentStr );
			}
		}
	}

	/**
	 * 保存会话信息
	 */
	public function write(){
		//\PTLibrary\Cache\Redis::set( $this->session_id, serialize( $this->data ), (int) $this->sessionTimeOut );
		\PTLibrary\Cache\Redis::set( $this->session_id, serialize( $this->data ) );
	}

	public function __destruct() {
	}

	/**
	 * 会话ID，设置会话ID或生成ID
	 *
	 * @param null $session_id
	 *
	 * @return bool|string|null
	 */
	public function sessionId( $session_id = null ) {
		if ( $session_id ) {
			$this->session_id = $session_id;

			return true;
		} else {
			if ( ! $this->session_id ) {
				$request=Request::instance();
				$this->session_id=$request->getCookie($this->session_key);
				$this->session_id || $this->session_id = $request->getHeaher( $this->session_key );
				$this->session_id || $this->session_id = $request->get( $this->session_key );
				$this->session_id || $this->session_id = $this->createSessionId();

			}

//			var_dump( 'session_id='.$this->session_id );
			$res = \PTLibrary\Cache\Redis::get( $this->session_id );
//			var_dump($res);
			return $this->session_id;
		}
	}

	public function createSessionId() {
		return Tool::getRandChar( 12 );
	}

	/**
	 * 当前会话信息
	 *
	 * @return array
	 */
	public function getData(){
	    return $this->data;
	}

	public function expire(){

	    \PTLibrary\Cache\Redis::expire($this->session_id,$this->sessionTimeOut);
	}

	/**
	 * 删除会话
	 */
	public function destory($session_id=null){
		if($session_id){
			\PTLibrary\Cache\Redis::del( $session_id );
		}else if($this->session_id){
			\PTLibrary\Cache\Redis::del( $this->session_id );
		}
	}



	/**
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return mixed|bool|null
	 */
	public function session( $key, $value = null ) {

		$contentStr = \PTLibrary\Cache\Redis::get( $key );

		if ( $key ) {
			if ( is_null( $value ) ) {
				return $this->data[ $key ] ?? null;
			} else {
				$key                = (string) $key;
				$this->data[ $key ] = $value;

				return $this->write();
			}
		}

		return false;
	}
}
