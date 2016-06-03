<?php
/**
 * @author Threema GmbH
 * @copyright Copyright (c) 2015-2016 Threema GmbH
 */


namespace Threema\MsgApi\Commands;

use Threema\MsgApi\Commands\Results\Result;

interface CommandInterface {
	/**
	 * @return string
	 */
	function getPath();

	/**
	 * @return array
	 */
	function getParams();

	/**
	 * @param int $httpCode
	 * @param object $res
	 * @return Result
	 */
	function parseResult($httpCode, $res);
}
