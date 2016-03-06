<?php namespace MVCFAM\App;
/**
 * Session wrapper
 */

class Session {
	
	protected static $_instance;

	protected $data;

	private $namespace;


	public function __construct($data = []) {
		$this->namespace = self::get_namespace();
		if (isset($_SESSION[$this->namespace])) {
			if ($unserialized = unserialize($_SESSION[$this->namespace])) {
				$data = array_merge($unserialized, $data);
			}
		}
		$this->data = $data;
	}

	public static function get_namespace() {
		return (defined('SESSION_NAMESPACE') ? SESSION_NAMESPACE : str_replace('/', '_', __DIR__));
	}

	public static function instance($data = []) {
		if( ! isset(static::$_instance)) {
			static::$_instance = new Session($data);
		}
		return static::$_instance;
	}

	public function clear($name) {
		$self = self::instance();
		if (isset($self->data[$name])) {
			$self->data[$name] = null;
		}
		return $this;
	}

	public function delete($name) {
		$self = self::instance();
		if (isset($self->data[$name])) {
			unset($self->data[$name]);
		}
		return $this;
	}

	public function reset() {
		$self = self::instance();
		$self->data = [];
		return $this;
	}

	public function __destruct() {
		$this->save();
	}

	public static function init() {
		session_start();
	}

	public function save() {
		$_SESSION[$this->namespace] = serialize($this->data);
		return $this;
	}

	public function __set($name, $value) {
		$self = self::instance();
		$self->data[$name] = $value;
	}

	public function __get($name) {
		$self = self::instance();

		return isset($self->data[$name]) ? $self->data[$name] : NULL;
	}

	public function __isset($name) {
		$self = self::instance();

		return isset($self->data[$name]);
	}

	public function set($name, $value) {
		$self = self::instance();
		$self->data[$name] = $value;
	}

	public function get($name) {
		$self = self::instance();

		return isset($self->data[$name]) ? $self->data[$name] : NULL;
	}

	public function data() {
		return $this->data;
	}

	public function is_set($name) {
		$self = self::instance();

		return isset($self->data[$name]);
	}
}