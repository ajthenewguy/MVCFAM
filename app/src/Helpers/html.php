<?php namespace MVCFAM\App;
/**
 * HTML Helpers
 */

/**
 * Escape for output
 */
if (!function_exists('_e')) {
	function _e($string = '') {
		if (!is_scalar($string)) {
			$string = var_export($string, true);
		}
		return ($string !== '' ? htmlspecialchars($string, ENT_COMPAT, 'UTF-8') : $string);
	}
}

/**
 * <button> helper
 */
if (!function_exists('html_button')) {
	function html_button($text, $href = false, $class = 'pure-button', $type = null, $extraParamString = '') {
		$html = '';
		if (is_null($type)) {
			// <a class="pure-button button-small" href="http://localhost/MVC/admin/route/edit/cG9zdDovYWRtaW4vc2V0dGluZy9jcmVhdGU=">Edit</a>
			$html .= '<a';

			if ($class) {
				$html .= ' class="'.$class.'"';
			}
			if ($href) {
				$html .= ' href="'.$href.'"';
			}
			$html .= (strlen($extraParamString) ? ' '.trim($extraParamString) : '').'>'.$text.'</a>';
		} else {
			// <button class="pure-button button-small" type="submit">Delete</button>
			$html .= '<button type="'.$type.'"';

			if ($class) {
				$html .= ' class="'.$class.'"';
			}
			if ($href && $type !== 'submit') {
				if ($type == 'modal') {
					$html .= ' onclick="'.$href.'"';
				} else {
					$html .= ' onclick="window.location.href=\''.$href.'\'"';
				}
			}
			$html .= (strlen($extraParamString) ? ' '.trim($extraParamString) : '').'>'.$text.'</button>';
		}
		return $html;
	}
}
