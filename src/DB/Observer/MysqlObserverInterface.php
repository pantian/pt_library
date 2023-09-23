<?php


namespace PTLibrary\DB\Observer;


use PTLibrary\DB\MysqlEntity;
use SplSubject;

interface  MysqlObserverInterface {
	public function __construct() ;

	/**
	 * 更新操作
	 * @param \PTLibrary\DB\MysqlEntity|null $mysqlEntity
	 */
	public function update();

	/**
	 * 插入成功
	 * @param \PTLibrary\DB\MysqlEntity|null $mysqlEntity
	 */
	public function insert();

	/**
	 * 删除成功
	 * @param \PTLibrary\DB\MysqlEntity|null $mysqlEntity
	 */
	public function del();

}