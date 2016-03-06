<?php namespace MVCFAM\App\Controller;
/**
 * Home Controller
 */

use MVCFAM\App\App;

class Home_Controller extends Controller {
	
	protected $ViewName = 'Page/Page';

	public function index() {
		return $this->View(array(
			'Title' => 'MVC From Anotha Motha',
			'Content' => '<p>The slimmest MVC yet.</p>'
		));
	}
}