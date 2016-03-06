<?php namespace MVCFAM\App\Controller;
/**
 * Base Controller
 */

use MVCFAM\App\App;
use MVCFAM\App\Object;
use MVCFAM\App\Model\Model;
use MVCFAM\App\View;
use MVCFAM\App\Route;

class Controller extends Object {

	protected $Model;

	protected $View;

	protected $Route;

	protected $GET;

	protected $POST;

	protected $TopLink;

	protected $Links;

	protected $Title = 'Pages';

	protected $Form;

	protected $Subtitle;

	protected $Description;

	protected $Content;

	protected $ListItems;

	protected $ViewName = 'Page/Page';


	public function __construct(\MVCFAM\App\Route $Route, $ViewName = null) {
		$this->Route = $Route;
		if (!is_null($ViewName)) {
			$this->ViewName = $ViewName;
		}
		$this->Model = $this->Route->getModel();
		$this->View = $this->getView();
	}

	public function Request() {
		$Request = new \stdClass;
		$Request->uri = $this->Route->uri();

		return $Request;
	}
	
	protected function get() {
		if (!isset($this->GET)) {
			$GET = [];
			if (isset($_GET)) {
				$GET = $_GET;
				unset($_GET);
			}
			$this->GET = $GET;
		}
		return $this->GET;
	}
	
	protected function post() {
		if (!isset($this->POST)) {
			$POST = [];
			if (isset($_POST)) {
				$POST = $_POST;
				unset($_POST);
			}
			$this->POST = $POST;
		}
		return $this->POST;
	}

	public function getView() {
		if (isset($this->ViewName)) {
			return new View\PageView([ $this->ViewName ]);
		}
		return $this->Route->getView();
	}

	public function getViewName() {
		return $this->ViewName;
	}

	public function TopLink() {
		if ( ! isset($this->TopLink)) {
			$this->TopLink = $this->getTopLink();
		}
		return $this->TopLink;
	}

	public function getTopLink() {
		$TopLink = new Model('TopLink', [ 'url' => '/', 'text' => 'HOME' ]);
		return $TopLink;
	}

	public function Links() {
		if ( ! isset($this->Links)) {
			$this->Links = $this->getLinks();
		}
		return $this->Links;
	}

	public function getLinks() {
		$Links = array();
		$Links[] = new Model('AboutLink', [ 'url' => \MVCFAM\App\url('/about'), 'text' => 'About' ]);
		$Links[] = new Model('ProjectsLink', [ 'url' => \MVCFAM\App\url('/projects'), 'text' => 'Projects' ]);
		$Links[] = new Model('ContactLink', [ 'url' => \MVCFAM\App\url('/contact'), 'text' => 'Contact' ]);
		return $Links;
	}

	public function Title() {
		if ( ! isset($this->Title)) {
			$this->Title = $this->getTitle();
		}
		return $this->Title;
	}

	public function getTitle() {
		return 'Page';
	}

	public function Subtitle() {
		if ( ! isset($this->Subtitle)) {
			$this->Subtitle = $this->getSubtitle();
		}
		return $this->Subtitle;
	}

	public function getSubtitle() {
		return 'Untitled';
	}

	public function Breadcrumb($url = '/', $text = 'Home', $prepend = false) {
		$name = preg_replace('/\s+/', '', ucwords($text));
		$ListItems = $this->ListItems();
		$Model = new Model($name, [ 'url' => \MVCFAM\App\url($url), 'text' => $text ]);
		if ($prepend) {
			array_unshift($ListItems, $Model);
		} else {
			$ListItems[] = $Model;
		}
		
		$this->ListItems = $ListItems;
		return $this;
	}

	public function ListItems() {
		if (! isset($this->ListItems)) {
			$this->ListItems = $this->getListItems();
		}
		return $this->ListItems;
	}

	public function getListItems() {
		return array();
	}

	public function Description() {
		if (! isset($this->Description)) {
			$this->Description = $this->getDescription();
		}
		return $this->Description;
	}

	public function getDescription() {
		return 'Description';
	}

	public function Content() {
		if (! isset($this->Content)) {
			$this->Content = $this->getContent();
		}
		return $this->Content;
	}

	public function getContent() {
		return '';
	}

	public function Form() {
		if (! isset($this->Form)) {
			$this->Form = $this->getForm();
		}
		return $this->Form;
	}

	public function getForm() {
		return '';
	}

	public function pushContent($string, $join = "\n") {
		$this->Content = $this->Content().$join.$string;
	}

	public function View($vars = []) {
		$vars = array_merge(array(
			'_uri' => \MVCFAM\App\url($this->Request()->uri),
			//'Route' => $this->Route,
			'TopLink' => $this->TopLink(),
			'Links' => $this->Links(),
			'Title' => $this->Title(),
			'Content' => $this->Content(),
			'Form' => $this->Form(),
			'ListItems' => $this->ListItems(),
		), $vars);

		return $this->getView()->setVars($vars);
	}
}