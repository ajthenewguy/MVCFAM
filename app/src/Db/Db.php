<?php namespace MVCFAM\App\Db;
/**
 * The Database/Datastore API
 */

use MVCFAM\App\Db\iDatastore;
use Aura\SqlQuery\QueryFactory;

class Db {

	private static $config;

	private function __construct() {}

	/**
	 * Set the config array
	 * @param Array $config
	 */
	public static function set_config($config = []) {
		static::$config = array_merge([
			'driver' => '',
			'host' => '',
			'name' => '',
			'user' => '',
			'pass' => ''
		], $config);
	}

	public static function driver($driver = null) {
		if (!isset(static::$config)) {
			throw new \Exception('Db configuration not set'); // @todo: ConfigurationException
		}
		if (is_null($driver)) {
			if (!empty(static::$config['driver'])) {
				$driver = static::$config['driver'];
			} else {
				throw new \RuntimeException('Datastore Driver not configured');
			}
		}
		return new Database($driver, static::$config);
	}

	public static function config() {
		return static::$config;
	}
}