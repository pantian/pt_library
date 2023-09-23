<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/17 0017
 * Time: 下午 9:23
 */

namespace PTLibrary\Verify;

use \PTLibrary\Error\ErrorHandler;


class InVerify implements Verify {
	/**
	 * @param \Verify\VerifyRule $verifyRule
	 *
	 * @return bool
	 * @throws \Bin\Exception\VerifyException
	 */
	public function doVerifyRule( VerifyRule $verifyRule ) {
		$verifyRule->chkDataType();
		$ruleValue = explode( ',', $verifyRule->ruleValue);
		if (!in_array($verifyRule->value,$ruleValue)) {
			$verifyRule->error || $verifyRule->error = $verifyRule->getDes(). '的值不在['.$verifyRule->ruleValue.']内';
			throw new VerifyException( ErrorHandler::VERIFY_BETWEEN_LENGTH,$verifyRule->error);
		}
		return true;
	}
}