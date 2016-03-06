<?php namespace MVCFAM\App\Controller;
/**
 * AdminPage Controller
 */

use MVCFAM\App\App;
use MVCFAM\App\Model\Model;
use MVCFAM\App\Route;
use MVCFAM\App\Session;

class AdminPage_Controller extends Admin_Controller {

	protected $ViewName = 'Page/AdminPage';
	
	public function __construct(\MVCFAM\App\Route $Route) {
		parent::__construct($Route);
	}
	
	/**
	 * route:/admin
	 */
	public function index() {

		// DEPRECATED


		$routes = array();
		foreach(Route::$routes as $route_url => $route_controller) {
			if (false === stripos($route_url, 'post:')) {
				$routes[] = '<a href="'.\MVCFAM\App\url($route_url).'">'.$route_controller.'</a>';
			}
		}
		$Content = '<h2>Dashbaord</h2>';

		return $this->View(array(
			'Title' => $this->Title(),
			'Content' => $Content
		));
	}
	
	/**
	 * {"route": "get:/admin/settings"}
	 */
	public function settings() {
		$this->Title = 'Settings';

		$Controls[] = new Model('AddNewRouteButton', [ 'url' => \MVCFAM\App\url('/admin/setting/create'), 'text' => 'New Setting' ]);
		
		return $this->View([ 'Controls' => $Controls ]);
	}

	/**
	 * {"route": "get:/admin/setting/create", "action": "AdminPage/setting_create"}
	 */
	public function setting_create() {
		$this->Title = 'New Setting';
		$setting_types = [
			'bool' => 'True/False',
			'int' => 'Integer',
			'string' => 'String',
			'float' => 'Float'
		];
		$this->pushContent('<div class="pure-u-1 "><form action="'.\MVCFAM\App\url($this->Route->uri()).'" name="Setting:create" method="post" class="pure-form pure-form-aligned">');
		$this->pushContent('<fieldset>');
		$this->pushContent('<div class="pure-control-group"><label for="Name">Name</label><input type="text" name="Name" id="Name" /></div>');
		$this->pushContent('<div class="pure-control-group">');
		$this->pushContent('<label for="Type">Type</label>');
		$this->pushContent('<select name="Type" id="Type">');
		foreach ($setting_types as $value => $display) {
			$this->pushContent('<option value="'.$value.'">'.$display.'</option>');
		}
		$this->pushContent('</select>');
		$this->pushContent('</div>');
		$this->pushContent('<div class="pure-control-group"><label for="init">Initial Value</label><input type="text" name="init" id="init" /></div>');
		$this->pushContent('<div class="pure-controls"><button type="submit" class="pure-button pure-button-primary">Create</button></div>');
		$this->pushContent('</fieldset>');
		$this->pushContent('</form></div>');

		return $this->View();
	}

	/**
	 * {"route": "post:/admin/setting/create", "action": "AdminPage/process_setting_create"}
	 */
	public function process_setting_create() {
		$POST = $this->post();
		//die(print_r($POST,1));
		//\App\message('Setting: '.json_encode($POST), 'info');

		return \MVCFAM\App\redirect('/admin/settings');
	}
	
	/**
	 * route:/admin/routes
	 */
	public function routes() {
		
		$this->Title = 'Routes';
		$Controls = array();
		$Controls[] = new Model('AddNewRouteButton', [ 'url' => \MVCFAM\App\url('/admin/route/create'), 'text' => 'New Route' ]);

		$route_table_html = '<div class="pure-u-1 "><div class="table-responsive"><table class="mq-table pure-table" width="100%">
		    <thead>
		        <tr>
		            <th>#</th>
		            <th>HTTP Method</th>
		            <th>Route</th>
		            <th>Controller/Method</th>
		            <th>View</th>
		            <th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody>';
		$count = 0;
		foreach(Route::$routes as $route_url => $route_controller) {
			$count++;
			$delete_link = $edit_link = '';
			$method_exists = false;
			$ViewName = false;
			$parsed_uri = Route::parse_method($route_url);
			$route_controller_parts = explode('/', $route_controller);
			$is_admin_route = (bool)preg_match('@/admin/route@i', $route_url);
			$Controller = '';
			if ( ! $is_admin_route) {
				if ($route_url == '/admin') {
					$is_admin_route = true;
				}
			}
			if (count($route_controller_parts) == 2) {
				$Controller = '\\MVCFAM\\App\\Controller\\'.$route_controller_parts[0].'_Controller';
				if (class_exists($Controller)) {
					$method_exists = method_exists($Controller, $route_controller_parts[1]);
					$Controller = new $Controller($this->Route);
					if ($Controller instanceof Controller) {
						$ViewName = '<span class="'.($Controller->getView()->exists() ? 'alert-success' : 'alert-error').'">'.$Controller->getViewName().'</span>';
					}
				}
			}
			
			$route_controller_html = '<span class="'.($method_exists ? 'alert-success' : 'alert-error').'">'.$route_controller.'</span>';
			if ( ! $is_admin_route && $route_url !== '/') {
				$uri_method = Route::parse_method($route_url);
				$edit_link = \MVCFAM\App\html_button('Edit', \MVCFAM\App\url('/admin/route/edit/'.Route::id_encode($uri_method['uri'], $uri_method['method'])), 'pure-button button-xsmall');
				
				$delete_link = '<form class="pure-form" action="'.\MVCFAM\App\url('/admin/route/delete').'" method="post" onsubmit="return confirm(\'Are you sure?\')" style="display:inline-block;margin:0 auto;">
					<input type="hidden" name="route_uri" value="'.Route::id_encode($route_url, $parsed_uri['method']).'" />'
					.\MVCFAM\App\html_button('Delete', false, 'pure-button button-xsmall', 'submit').
					'</form>';
			}

			$Route = new Model(base64_encode($route_url), [
				'method' => $parsed_uri['method'],
				'uri' => $parsed_uri['uri'],
				'action' => $route_controller_html
			]);

			// @todo: move the View column to a Controllers admin screen (eg. /admin/controllers)

			$route_table_html .= '<tr'.($count % 2 == 0 ? ' class="pure-table-odd"' : '').'>
	            <td>'.$count.'</td>
	            <td>'.$Route->method.'</td>
	            <td>'.$Route->uri.'</td>
	            <td>'.$Route->action.'</td>
	            <td>'.($ViewName && $Route->method == 'GET' ? $ViewName : '&nbsp;').'</td>
	            <td>'.implode(' &nbsp;', [ $edit_link, $delete_link ]).'</td>
	        </tr>';
		}
		$route_table_html .= '</tbody></table></div></div>';

		$this->pushContent($route_table_html);
		$this->pushContent("</ul>\n");

		return $this->View([ 'Controls' => $Controls ]);
	}

	/**
	 * 
	 */
	public function route_create() {
		$this->Title = 'New Route';
		$request_methods = [
			'get:' => Route::METHOD_GET,
			'post:' => Route::METHOD_POST
		];

		$this->pushContent('<div class="pure-u-1 "><form action="'.\MVCFAM\App\url($this->Route->uri()).'" name="Route:create" method="post" class="pure-form pure-form-aligned">');
		$this->pushContent('<fieldset>');
		$this->pushContent('<div class="pure-control-group">');
		$this->pushContent('<label for="HttpMethod">HTTP Method</label>');
		$this->pushContent('<select name="HttpMethod" id="HttpMethod">');
		foreach ($request_methods as $value => $display) {
			$this->pushContent('<option value="'.$value.'">'.$display.'</option>');
		}
		$this->pushContent('</select>');
		$this->pushContent('</div>');
		$this->pushContent('<div class="pure-control-group"><label for="route_uri">URI Route Fragment</label><input type="text" name="route_uri" id="route_uri" placeholder="/product/share/(:id)" /></div>');
		$this->pushContent('<div class="pure-control-group"><label for="Action">Action</label><input type="text" name="Action" id="Action" placeholder="ProductPage/share" /></div>');
		$this->pushContent('<div class="pure-controls"><button type="submit" class="pure-button pure-button-primary">Create</button></div>');
		$this->pushContent('</fieldset>');
		$this->pushContent('</form></div>');

		return $this->View();
	}

	public function process_route_create() {
		if ($POST = $this->post()) {
			$http_method = $POST['HttpMethod']; // HttpMethod: 'post:'
			$route_uri = $POST['route_uri']; // route_uri: '/admin/route/edit/(:any)'
			$controller_method = $POST['Action']; // Action: 'AdminPage/process_edit'

			// check if route exists
			if ( ! Route::exists($http_method.$route_uri)) {
				// update
				try {
					if ($created = Route::create($http_method.$route_uri, $controller_method)) {
						return \MVCFAM\App\redirect('/admin/routes');
						\MVCFAM\App\message('Route created', 'success');
					} else {
						\MVCFAM\App\message('Error creating Route', 'error');
					}
				} catch(Exception $e) {
					\MVCFAM\App\message($e->getMessage(), 'error');
				}
			} else {
				\MVCFAM\App\message('Route already exists', 'error');
			}
		} else {
			\MVCFAM\App\message('All fields required', 'error');
			\MVCFAM\App\redirect('/admin/route/create');
		}

		\MVCFAM\App\redirect('/admin/routes');
	}

	/**
	 * {"route": "/admin/route/delete/(:any)", "action" : "AdminPage/process_route_delete"}
	 */
	public function process_route_delete() {
		if ($POST = $this->post()) {
			$route_url = base64_decode($POST['route_uri']);
			$http_method = $POST['HttpMethod']; // HttpMethod: 'post:'
			$route_uri = $POST['route_uri']; // route_uri: '/admin/route/edit/(:any)'
			$controller_method = $POST['Action']; // Action: 'AdminPage/process_edit'

			// check if route exists
			if ( ! Route::exists($http_method.$route_uri)) {
				// update
				try {
					if ($created = Route::create($http_method.$route_uri, $controller_method)) {
						return \MVCFAM\App\redirect('/admin/routes');
						$Content .= '<p>Ceated</p>';
					} else {
						die('error creating');
					}
				} catch(\Exception $e) {
					die($e->getMessage());
				}
				

				// ---> writes eg. $routes['post:/admin/route/edit/(:any)'] = 'AdminPage/process_edit';
			} else {
				die('Route exists');
				throw new \Exception('Route already exists');
			}
		} else {
			die('NO POST');
			\MVCFAM\App\redirect('/admin/route/create');
		}

		\MVCFAM\App\redirect('/admin/routes');
	}

	public function route_edit($encoded_route_url) {
		$route_url = base64_decode($encoded_route_url);
		if (array_key_exists($route_url, Route::$routes)) {
			$route = Route::$routes[$route_url];
			if (false === strpos($route_url, 'get:')) {
				$route_url = 'get:'.$route_url;
			}
		} elseif (array_key_exists('get:'.$route_url, Route::$routes)) {
			if (false === strpos($route_url, 'get:')) {
				$route_url = 'get:'.$route_url;
			}
			$route = Route::$routes[$route_url];
		} elseif (array_key_exists('post:'.$route_url, Route::$routes)) {
			if (false === strpos($route_url, 'post:')) {
				$route_url = 'post:'.$route_url;
			}
			$route = Route::$routes[$route_url];
		} else {
			// get:post:/admin/setting/create
			die($route_url);
		}
		
		$parsed_uri = Route::parse_method($route_url);
		$route_url = $parsed_uri['uri'];

		$request_methods = [
			'get:' => Route::METHOD_GET,
			'post:' => Route::METHOD_POST
		];

		$this->Title = 'Edit Route';
		$this->Subtitle = 'Edit Route';
		$this->pushContent('<div class="pure-u-1 "><form action="'.\MVCFAM\App\url($this->Route->uri()).'" name="Page:create" method="post" class="pure-form pure-form-aligned">');
		$this->pushContent('<fieldset>');
		//$this->pushContent('<legend>Edit this route</legend>');
		$this->pushContent('<div class="pure-control-group">');
		$this->pushContent('<label for="HttpMethod">HTTP Method</label>');
		$this->pushContent('<select name="HttpMethod" id="HttpMethod">');
		foreach ($request_methods as $value => $display) {
			$this->pushContent('<option value="'.$value.'"'.($display == $parsed_uri['method'] ? ' selected' : '').'>'.$display.'</option>');
		}
		$this->pushContent('</select>');
		$this->pushContent('</div>');
		$this->pushContent('<div class="pure-control-group"><label for="route_uri">URI Route Fragment</label><input type="text" name="route_uri" id="route_uri" value="'.$route_url.'" /></div>');
		$this->pushContent('<div class="pure-control-group"><label for="Action">Action</label><input type="text" name="Action" id="Action" value="'.$route.'" /></div>');
		$this->pushContent('<div class="pure-controls"><button type="submit" class="pure-button pure-button-primary">Save</button></div>');
		$this->pushContent('</fieldset>');
		$this->pushContent('<input type="hidden" name="original_route_uri" value="'.$route_url.'" />');
		$this->pushContent('</form></div>');

		return $this->View();
	}

	public function process_route_edit() {
		$this->Title = 'Pages:process_edit()';
		$Content = '<p>POSTed Data</p>';
		if ($POST = $this->post()) {
			$original_route_uri = $POST['original_route_uri'];
			$http_method = $POST['HttpMethod']; // HttpMethod: 'post:'
			$route_uri = $POST['route_uri']; // route_uri: '/admin/route/edit/(:any)'
			$controller_method = $POST['Action']; // Action: 'AdminPage/process_edit'

			// check if route exists
			if (Route::exists($http_method.$original_route_uri)) {
				// update
				
				if ($updated = Route::update($http_method.$original_route_uri, [ $http_method.$route_uri, $controller_method ])) {
					\MVCFAM\App\message('Route updated', 'success');
					return \MVCFAM\App\redirect('/admin/routes');
				}
			} else {
				\MVCFAM\App\message('Invalid Route', 'error');
				return \MVCFAM\App\redirect('/admin/route/edit/'.base64_encode($original_route_uri));
			}
		} else {
			\MVCFAM\App\message('All fields required', 'error');
			return \MVCFAM\App\redirect('/admin/route/edit/'.base64_encode($original_route_uri));
		}

		return \MVCFAM\App\redirect('/admin/routes');
	}
}