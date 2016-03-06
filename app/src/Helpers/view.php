<?php namespace MVCFAM\App;
/**
 * View Helpers
 */

use MVCFAM\App\View\View;

/**
 * Factory function
 */
function View($filename, $vars = array()) {
	return new View(array($filename), $vars);
}