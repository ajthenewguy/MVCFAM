<?php namespace MVCFAM\App;
/**
 * Object class
 */
class Object {

	/**
	 * Return the instance namespace
	 * @return string
	 */
	protected function _namespace() {
		return substr(get_class($this), 0, strrpos(get_class($this), '\\'));
	}

	/**
	 * Return the instance class name
	 * @return string
	 */
	protected function _class_name($namespace_decorator = '', $class_decorator = '') {
		$ObjectClass = get_class($this);
		if (strlen($namespace_decorator) > 0 || strlen($class_decorator) > 0) {
			$ObjectNamespace = $this->_namespace().'\\';
			$ObjectClassname = str_replace($ObjectNamespace, '', $ObjectClass);

			if (strlen($namespace_decorator) > 0) {
				$ObjectNamespace = '<'.$namespace_decorator.'>'.$ObjectNamespace.'</'.$namespace_decorator.'>';
			}
			if (strlen($class_decorator) > 0) {
				$ObjectClassname = '<'.$class_decorator.'>'.$ObjectClassname.'</'.$class_decorator.'>';
			}
			$ObjectClass = $ObjectNamespace.$ObjectClassname;
		}
		return $ObjectClass;
	}
}