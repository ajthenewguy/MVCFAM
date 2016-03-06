<?php namespace MVCFAM\App\Controller;
/**
 * Page Controller
 */

use MVCFAM\App\App;
use MVCFAM\App\Model\Model;

class Page_Controller extends Controller {

	protected $ViewName = 'Page/Page';


	public function __construct(\MVCFAM\App\Route $Route) {
		parent::__construct($Route);
	}
	
	public function index() {
		$routes = array();
		foreach(Route::$routes as $route_url => $route_controller) {
			if (false === stripos($route_url, 'post:')) {
				$routes[] = '<a href="'.\MVCFAM\App\url($route_url).'">'.$route_controller.'</a>';
			}
		}
		$Content = '<h2>Routes</h2>';
		$Content .= "\n<ul>\n<li>";
		$Content .= implode("</li>\n<li>", $routes);
		$Content .= "</li>\n</ul>\n";

		return $this->View(array(
			'Title' => $this->Title(),
			'Content' => $Content,
			'ListItems' => $this->ListItems()
		));
	}

	public function getTopLink() {
		$TopLink = new Model('TopLink', [ 'url' => '/', 'text' => 'HOME' ]);
		return $TopLink;
	}

	public function getLinks() {
		$Links = array();
		$Links[] = new Model('RoutesLink', [ 'url' => \MVCFAM\App\url('/about'), 'text' => 'About' ]);
		$Links[] = new Model('ProjectsLink', [ 'url' => \MVCFAM\App\url('/projects'), 'text' => 'Projects' ]);
		$Links[] = new Model('ContactLink', [ 'url' => \MVCFAM\App\url('/contact'), 'text' => 'Contact' ]);
		return $Links;
	}

	public function getListItems() {
		$ListItems = array();
		return $ListItems;
	}
}