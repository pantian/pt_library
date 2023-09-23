<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/14 0014
 * Time: 12:16
 */

namespace PTLibrary\Verify;

use App\Library\Hash\FormHash;
use \PTLibrary\Error\ErrorHandler;


/**
 * 表单Hash校验
 * Class FormHashVerify
 *
 * @package Verify
 */
class FormHashVerify implements Verify {
	/**
	 * @param \PTLibrary\Verify\VerifyRule $verifyRule
	 *
	 * @return bool
	 * @throws \Bin\Exception\VerifyException
	 */
	public function doVerifyRule( VerifyRule $verifyRule ) {
		$verifyRule->chkDataType();
		$verifyRule->value=\PTLibrary\Tool\Request::instance()->input('_form_hash');
		//Log::log('---------------------'.$verifyRule);
		if(!FormHash::verifyHash($verifyRule->value,true)){
			$verifyRule->error || $verifyRule->error= $verifyRule->getDes(). '请求无效' ;
			throw new VerifyException( ErrorHandler::VERIFY_FORM_HASH, $verifyRule->error );
		}
		return true;
	}

}