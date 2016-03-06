<?php namespace MVCFAM\App;
/**
 * Settings wrapper class
 */

class Settings {
	
	protected static $_instance;

	protected $values = array();


	public function __construct() {
		$this->init();
	}

	public static function instance() {
		static $initialized = false;

		if(!$initialized) {
			self::$_instance = new Settings();
			$initialized = true;
		}

		return self::$_instance;
	}

	public function init() {
		if(!isset($this->values['BASE_URL'])) {
			if(defined('BASE_URL')) {
				$this->values['BASE_URL'] = BASE_URL;
			} else {
				$this->values['BASE_URL'] = sprintf(
					"%s://%s",
					isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
					$_SERVER['SERVER_NAME'].'/'.APP_DIRECTORY
				);
			}
			$this->values['URI'] = $_SERVER['REQUEST_URI'];
		}
	}

	public static function set($key, $value) {
		$this->values[$key] = $value;
	}

	public function __get($var) {
		$self = self::instance();

		return isset($self->values[$var]) ? $self->values[$var] : NULL;
	}

	public function __set($key, $value) {
		$self = self::instance();
		$self->values[$var] = $value;
	}
}