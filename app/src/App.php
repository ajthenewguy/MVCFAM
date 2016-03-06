<?php namespace MVCFAM\App;
/**
 * App class
 */

use MVCFAM\App\Singleton;
use MVCFAM\App\Db\Db;

class App {

	use Singleton;

	private static $db;


	/**
	 * Get the $db properyy (Database)
	 */
	public static function db($config = null) {
		if (!isset(static::$db)) {
			if (is_null($config)) {
				throw new \Exception('Datastore Driver not set', 1);
			}
			static::set_db($config);
		}
		return static::$db;
	}

	/**
	 * Set the $db properyy (Database)
	 */
	public static function set_db($config = array()) {
		if (is_array($config) && !empty($config)) {
			Db::set_config($config);
			static::$db = Db::driver();
		} else {
			throw new \InvalidArgumentException('App::set_db() must be supplied with a valid Datastore Config array', 1);
		}
	}

	/**
	 * Return defined namespaces
	 * @param string
	 * @return array
	 */
	public static function namespaces($root = 'App') {
		$namespaces = [];
		foreach (get_declared_classes() as $name) {
			if (preg_match_all("@[^\\\]+(?=\\\)@iU", $name, $matches)) {
				$matches = $matches[0];
				$parent =& $namespaces;
				while (count($matches)) {
					$match = array_shift($matches);
					if (! isset($parent[$match]) && count($matches))
						$parent[$match] = array();
					$parent =& $parent[$match];

				}
			}
		}
		if (strlen($root) > 0) {
			$searchTree = function($search, $children) use (&$searchTree) {
				foreach ($children as $component => $_children) {
					if ($component == $search) {
						return $_children;
					}
				}
				return $searchTree($search, $_children);
			};
			$namespaces = $searchTree($root, $namespaces);
		}
		return $namespaces;
	}

	/**
	 * Return declared classes
	 * @return array
	 */
	public static function classes() {
		$declared_classes = [];
		foreach (get_declared_classes() as $name) {
			if (false !== strpos($name, trim(APP_NAMESPACE, '\\'))) {
				$declared_classes[] = $name;
			}
		}
		return $declared_classes;
	}
}