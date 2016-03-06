<?php namespace MVCFAM\App\Controller;
/**
 * Datastore Controller
 */

use MVCFAM\App\App;
use MVCFAM\App\Db\Db;
use MVCFAM\App\Model\Model;
use MVCFAM\App\Route;
use MVCFAM\App\Session;
/*
$result = App::db()->tables();
		$this->pushContent('<pre class="pure-u-1">$result: '.var_export($result,1).'</pre>');
*/

class Datastore_Controller extends Admin_Controller {
	
	/**
	 * {"route": "get:/admin/datastore"}
	 */
	public function index() {
		$this->Title = 'Datastore';
		$this->pushContent('<p>Working with Db</p>');
		return $this->View();
	}
}