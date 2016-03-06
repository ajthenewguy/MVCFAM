<?php namespace MVCFAM\App\Controller;
/**
 * Home Controller
 */

use MVCFAM\App\App;

class ProjectsPage_Controller extends Controller {
	
	protected $ViewName = 'Page/ProjectsPage';

	public function index() {
		return $this->View(array(
			'Title' => 'MVC From Anotha Motha',
			'Content' => '<p>Projects</p>'
		));
	}
}