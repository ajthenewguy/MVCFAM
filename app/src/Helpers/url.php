<?php namespace MVCFAM\App;
/**
 * URL Helpers
 */

/**
 * Getter for BASE_URL
 */
function base_url() {
	return Settings::instance()->BASE_URL;
}

/**
 * Generate a URL ready for output
 */
function url($append = '') {
	if(strlen($append) > 0) {
		if (0 === stripos($append, 'get:')) {
			$append = str_replace('get:', '', $append);
		}
		if (0 === stripos($append, 'post:')) {
			$append = str_replace('post:', '', $append);
		}
		$append = ltrim(trim($append), '/');
	}

	return base_url().'/'.$append;
}

/**
 * Issue HTTP request to effect a redirect
 */
function redirect($url, $message = []) {
	if (! empty($message)) {
		message($message[0], $message[1]);
	}
	header("Location: ".url($url));
	die();
}


/**
 * Set a message to flash memory
 */
function message($string, $class) {
	return Notification::add($string, $class);
	//return Session::instance()->flash('UI_message', [ $class, $string ]);
}


/**
 * Get a message from flash memory
 */
function get_messages() {
	return Notification::get();
	//return Session::instance()->flash('UI_message');
}

/**
 */
function get_message_icon($message_class = '') {
	$icon = null;
	switch($message_class) {
		case 'ban':
		case 'restricted':
			$icon = '<i class="fa fa-ban"></i>';
		break;
		case 'bug':
			$icon = '<i class="fa fa-bug"></i>';
		break;
		case 'check':
		case 'success':
			$icon = '<i class="fa fa-check"></i>';
		break;
		case 'comment':
			$icon = '<i class="fa fa-comment"></i>';
		break;
		case 'info':
		case 'error':
			$icon = '<i class="fa fa-exclamation"></i>';
		break;
		case 'flash':
			$icon = '<i class="fa fa-flash"></i>';
		break;
		case 'confirm':
		case 'question':
			$icon = '<i class="fa fa-question"></i>';
		break;
		case 'warn':
		case 'warning':
			$icon = '<i class="fa fa-warning"></i>';
		break;
	}
	return $icon;
}
