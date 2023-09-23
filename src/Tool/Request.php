<?php


namespace PTLibrary\Tool;


use PTFramework\Context;
use PTLibrary\Factory\InstanceFactory;


class Request {

	/**
	 * @var \Swoole\Http\Request
	 */
	protected $swooleRequest;
    /**
     * 路由数据
     * @var
     */
    public $routeInfo;

	public function __construct(){

	}

	public function __destruct(){

	}

	/**
	 * 客户访问IP
	 * @return mixed|string
	 */
	public function getRemoteIp(){
		$ip=$this->swooleRequest->header['x-real-ip']??'';
		$ip || $ip=$this->swooleRequest->server['remote_addr']??'';
		return $ip;
	}

	public function requestMethod(){
		return $this->swooleRequest->server['request_method'];
	}

	public function getCookie(string $key){
		return $this->swooleRequest->cookie[$key]??null;
	}

	/**
	 * 获取GET数据
	 * @param string $key
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function get(string $key,$default=null){
		return $this->swooleRequest->get[$key]??$default;
	}

	/**
	 * 获取POST数据
	 * @param string $key
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function post(string $key,$default=null){

		return Tool::getArrVal($key,$this->swooleRequest->post,$default);
	}

	/**
	 * 设置request实例
	 * @param \Swoole\Http\Request|null $request
	 *
	 * @return void
	 */
	public static function setInstance(\Swoole\Http\Request $request=null){
		$key = '__request__';

		$instance=InstanceFactory::cloneInstance(self::class);
		if ( $instance instanceof self && $request ) {
			$instance->swooleRequest=$request;
		}
		Context::set( $key, $instance );

	}

	/**
	 * 获取实例对象
	 * @param \Swoole\Http\Request|null $request
	 *
	 * @return mixed|void
	 */
	public static function instance(){
		$key = '__request__';
		$instance=Context::get($key);
		if($instance){
			return $instance;
		}

	}

	public function setSwooleRequest($request){
	    $this->swooleRequest=$request;
	}
	/**
	 * @return \Swoole\Http\Server
	 */
	public function getHttpServerInstance(){
		return $GLOBALS['http_server'];
	}

	/**
	 * 返回请GET所有数据
	 * @return mixed
	 */
	public function allGet(){
		return $this->swooleRequest->get;
	}

	/**
	 * 返回所有POST数据
	 * @return mixed
	 */
	public function allPost(){

		return $this->swooleRequest->post;
	}

	public function rawContent(){
		return $this->swooleRequest->rawContent();
	}

    /**
     * 获取路由正则匹配的路由参数
     *
     * @param $key
     * @param $default
     *
     * @return bool|null
     */
    public function getRouterParam($key,$default=null){
        return Tool::getArrVal('2.'.$key,$this->swooleRequest->routeInfo);
    }
	/**
	 * 返回请求参数 自动判断GET或POST方法
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function input($key,$default=null){

		if($this->isGET()){
			return $this->get($key,$default);
		} else {
			return $this->post( $key, $default );
		}
	}

	/**
	 * @return mixed
	 */
	public function files(){
		return $this->swooleRequest->files;
	}

	/**
	 * @return \Swoole\Http\Request
	 */
	public function getSwooleRequest(){
		return $this->swooleRequest;
	}

	/**
	 * 返回服务信息
	 * @param string $key
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function getServerInfo(string $key,$default=null){
		return $this->swooleRequest->server[$key]??$default;
	}

	/**
	 * 返回 swoole request 对象
	 * @return \Swoole\Http\Request
	 */
	public function swooleRequest(){
		return $this->swooleRequest;
	}

	/**
	 * 返回请求域名
	 * @return mixed
	 */
	public function getHost(){
		return $this->swooleRequest->header['host'];
	}
	public function getHeaher(string  $key,$default=null){
		return $this->swooleRequest->header[$key]??$default;
	}
	public function getServerPort(){
		return $this->swooleRequest->server['server_port'];
	}

	public function getUri(){
		return $this->swooleRequest->server['request_uri'];
	}

	/**
	 * 返回完整的请url
	 *
	 * @return string
	 */
	public function getRequestUrl(){
		$host=$this->getHost();
		$query_string=$this->getServerInfo('query_string');
		$url = $host . $this->getUri().($query_string?'?'.$query_string:'');

		return $url;
	}

	public function isGET(){
		return strtolower($this->requestMethod())=='get';
	}

	public function isPOST(){
		return strtolower($this->requestMethod())=='post';
	}



}
