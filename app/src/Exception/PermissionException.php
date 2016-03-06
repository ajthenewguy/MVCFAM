<?php namespace MVCFAM\App\Exception;
/**
 * Permission Exception
 */

class PermissionException extends \Exception {

	const INSUFFICIENT = 1;

	const SUSPENDED = 2;
	
	
	public function __construct($message = '', $code = 0, Exception $previous = null) {
		switch ($code) {
			case 2:
				$message = 'Permissions are suspended'.($message ? ': '.$message : '');
				break;
			
			case 1:
			default:
				$message = 'Insufficient permissions'.($message ? ': '.$message : '');
				break;
		}
		parent::__construct($message, $code, $previous);
	}
}