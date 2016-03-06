<?php namespace MVCFAM\App;

use MVCFAM\App\Session;

class Notification {

	private static $namespace = '__FLASH__';

	public static function add($message, $class = 'info') {
		if ( ! static::Session()->is_set(static::$namespace)) {
			static::reset();
		}
		$notifications = static::Session()->get(static::$namespace);
		$notifications[] = [ 'message' => $message, 'class' => $class ];
		static::Session()->set(static::$namespace, $notifications);
	}

	public static function get() {
		$return = null;
		$Session = Session::instance();
		if (static::Session()->is_set(static::$namespace)) {
			$return = static::Session()->get(static::$namespace);
		}
		static::reset();
		return $return;
	}

	public static function Session() {
		return Session::instance();
	}

	public static function reset() {
		static::Session()->set(static::$namespace, []);
	}

	public static function test() {
		return 'TEST';
	}
}