<?php
/**
 * Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class RegisterException extends CakeException {

/**
 * Message string
 *
 * @var string
 */
	public $messageString;

/*/
 * Constructor
 *
 * @param string
 * @return \RegisterException
 */
	public function __construct($message) {
		$this->messageString = $message;
	}

}