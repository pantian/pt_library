<?php


namespace PTLibrary\DB\Observer;


use SplObserver;

class MysqlSuboject  {
	protected $observer=[];

	public function __construct() {
		$this->observer=[];
	}

	/**
	 * 新增观察者
	 * @param \PTLibrary\DB\Observer\MysqlObserverInterface $observer
	 */
	public function attach(MysqlObserverInterface $observer){
		$this->observer[]=$observer;
	}

	public function clear(){
	    $this->observer=[];
	}

	public function getObserver(){
	    return $this->observer;
	}
}