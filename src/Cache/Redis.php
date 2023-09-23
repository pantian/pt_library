<?php


namespace PTLibrary\Cache;
use PTLibrary\Config\Config;
use PTLibrary\Tool\Tool;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
use function Co\run;

/**
 * Redis缓存
 * Class Redis
 *
 * @package PTLibrary\Cache
 */
class Redis {

	static $redisPool = null;

	public static function createConnection(){
		try{
			$config= \PTFramework\Config::getInstance()->get('redis');
//			print_r($config);
			self::$redisPool = new RedisPool((new RedisConfig)
				->withHost(Tool::getArrVal('host', $config,'localhost'))
				->withPort(Tool::getArrVal('port', $config,6379))
				->withAuth(Tool::getArrVal('auth', $config,''))
				->withDbIndex(Tool::getArrVal('db_index', $config,0))
				->withTimeout(Tool::getArrVal('time_out', $config,3))
			);

//			print_r(self::$redisPool);

		}catch(Exception $e){
		    var_dump($e->getMessage());
		}

	}

	/**
	 * @return RedisPool
	 */
	public static function getReids(){
	    if(!self::$redisPool){
			run(function (){
				self::createConnection();
			});

	    }
		return self::$redisPool->get();
	}

	public static function put($redis){
		if(!self::$redisPool){
			self::createConnection();
		}
		return self::$redisPool->put($redis);
	}
	public static function get($key){

//		$redisInstance=\Simps\DB\Redis::getInstance();
		$redis=self::getReids();//$redisInstance->getConnection();
		$res = $redis->get( $key );
		self::put($redis);
//		$redisInstance->close($redis);
		return $res;

	}

	public static function set($key,$value,$timeout=null){

//		$redisInstance=\Simps\DB\Redis::getInstance();
		$redis=self::getReids();//$redisInstance->getConnection();
		$res = $redis->set( $key ,$value ,$timeout);
//		$redisInstance->close($redis);
		self::put($redis);
		return $res;


	}

	public static function del($key){
		//$redisInstance=\Simps\DB\Redis::getInstance();
		$redis=self::getReids();//$redisInstance->getConnection();
		$res = $redis->del( $key );
//		$redisInstance->close($redis);
		self::put($redis);
		return $res;
	}

	/**
	 * 设置有效时间
	 *
	 * @param      $key
	 * @param null $timeout
	 *
	 * @return bool
	 */
	public static function expire($key,$timeout=null){
//		$redisInstance=\Simps\DB\Redis::getInstance();
		$redis=self::getReids();//$redisInstance->getConnection();
		$res = $redis->expire( $key  ,$timeout);
//		$redisInstance->close($redis);
		self::put($redis);
	}
}