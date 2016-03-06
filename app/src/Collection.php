<?php namespace MVCFAM\App;

class Collection extends \ArrayObject {
	
    public static $debugLevel = 0;

    private static $type;


	public function __construct($input = [], $strict = false, $iterator_class = "ArrayIterator", $flags = 0) {
		/*
		$flags:
		1	ArrayObject::STD_PROP_LIST
		2	ArrayObject::ARRAY_AS_PROPS
		*/
		$flags = ($flags ?: parent::STD_PROP_LIST|parent::ARRAY_AS_PROPS);
		if ($strict) {
			foreach ($input as $item) {
				if (! isset(static::$type)) {
					static::$type = static::get_type($item);
				} else {
					if (static::get_type($item) !== static::$type) {
						throw new \InvalidArgumentException('Each item in collection must be of the same type');
					}
				}
			}
		}
		parent::__construct($input, $flags, $iterator_class);
	}

	protected static function get_type($item) {
		$type = null;
		if (is_object($item)) {
			$type = get_class($item);
		} else {
			$type = gettype($item);
		}
		return $type;
	}

	/**
	 * Now you can do this with any array_* function:
	 * <?php
	 * $yourObject->array_keys();
	 * ?>
	 * - Don't forget to ommit the first parameter - it's automatic!
	 */
	public function __call($func, $argv) {
        if (!is_callable($func) || substr($func, 0, 6) !== 'array_') {
            throw new \BadMethodCallException(__CLASS__.'->'.$func);
        }
        return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
    }

	static public function sdprintf() { 
		if (static::$debugLevel > 1) { 
			call_user_func_array("printf", func_get_args()); 
		} 
	} 

	public function offsetGet($name) { 
		//self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args())); 
		return call_user_func_array(array('parent', __FUNCTION__), func_get_args()); 
	} 
	public function offsetSet($name, $value) { 
		//self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args())); 
		return call_user_func_array(array('parent', __FUNCTION__), func_get_args()); 
	} 
	public function offsetExists($name) { 
		//self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args())); 
		return call_user_func_array(array('parent', __FUNCTION__), func_get_args()); 
	} 
	public function offsetUnset($name) { 
		//self::sdprintf("%s(%s)\n", __FUNCTION__, implode(",", func_get_args())); 
		return call_user_func_array(array('parent', __FUNCTION__), func_get_args()); 
	}
}