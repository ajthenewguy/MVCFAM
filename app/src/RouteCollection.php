<?php namespace MVCFAM\App\Route;

use \ArrayObject;
use MVCFAM\App\Route;

class RouteCollection extends ArrayObject {

	public function __construct($input = []) {
		parent::__construct($input, 0, "ArrayIterator");
	}

	public function __call($func, $argv) {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_') {
            throw new BadMethodCallException(__CLASS__.'->'.$func);
        }
        return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }
}