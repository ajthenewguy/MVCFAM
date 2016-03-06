<?php namespace MVCFAM\App;
/**
 * Routing
 */

use MVCFAM\App\Model;
use MVCFAM\App\View;
//use MVCFAM\App\Route\RouteCollection;

class Route {

	private $debug = false;

	public static $routes;

	protected $uri;

	private $Model;

	private $View;

	private $Controller;

	private $method;

	private $parameters;

	private $request_method;

	const METHOD_GET = 'GET';

	const METHOD_POST = 'POST';

	private static $route_file_contents;


	public function __construct($uri = '') {
		if(empty($uri)) {
			$uri = static::get_uri();
		}
		$allow_variable_parameters = false;
		$matched_route = false;
		$this->uri = $uri;
		$this->request_method = $_SERVER['REQUEST_METHOD'];

		$keys = array_map('strlen', array_keys(static::$routes));
		array_multisort($keys, SORT_DESC, static::$routes);

		if($this->debug) {
			print '<pre>routes: '.print_r(static::$routes,1).'</pre>';
			print '<pre>URI: '.$this->uri.'</pre>';
			print '<pre>REQUEST_METHOD: '.$this->request_method.'</pre>';
		}

		foreach(static::$routes as $partial_uri => $controller_and_method) {
			$parsed_uri = static::parse_method($partial_uri);
			$route_method = $parsed_uri['method'];
			$parsed_uri = $parsed_uri['uri'];
			$parsed_uri = str_replace('(:num)', '(\d+)', $parsed_uri);
			$parsed_uri = str_replace('(:str)', '([a-z_\-]+)', $parsed_uri);
			$parsed_uri = str_replace('(:any)', '([a-zA-Z0-9+/=]+)', $parsed_uri);
			
			if ($allow_variable_parameters) {
				// /list/users/ IS WITHIN /list/users/1
				if(false !== strpos($this->uri, $parsed_uri)) {
					$matched_route = $parsed_uri;
					$this->parameters = (array)(substr($this->uri, strlen($parsed_uri)) ?: false);
				}
			} elseif (preg_match('@^'.$parsed_uri.'$@i', $this->uri, $this->parameters)) {
				$matched_route = $parsed_uri;
				if (isset($this->parameters[0])) {
					unset($this->parameters[0]);
					$this->parameters = array_values($this->parameters);
				}
				if($this->debug) {
					print '<pre>Partial Route: '.$parsed_uri.'</pre>';
					print '<pre>$this->uri: '.$this->uri.'</pre>';
					print '<pre>params: '.print_r($this->parameters,1).'</pre>';
					print '<hr />';
					print '<pre>$route_method: '.$route_method.'</pre>';
					print '<pre>$this->request_method: '.$this->request_method.'</pre>';
				}
				//$this->parameters = (array)(substr($this->uri, strlen($parsed_uri)) ?: false);

				if($route_method == $this->request_method) {/*isset(static::$routes[$this->uri])*/
					if($this->debug) {
						print '<pre>Partial Route: '.$parsed_uri.'</pre>';
						print '<pre>Matched Route: '.$matched_route.'</pre>';
						print '<pre>Route Requst Method: '.$route_method.'</pre>';
						print '<pre>Controller/method: '.print_r($controller_and_method,1).'</pre>';
						print '<pre>params: '.print_r($this->parameters,1).'</pre>';
					}

					$route = explode('/', $controller_and_method);
					$views = [ $route[0] ];
					$controller = '\\MVCFAM\\App\\Controller\\'.$route[0].'_Controller';
					$this->Controller = new $controller($this);
					if ($view = $this->Controller->getViewName()) {
						array_unshift($views, $view);
					}

					if($this->debug) {
						print '<pre>';
						print 'route: '.print_r($route,1).'<br>';
						print 'view: '.$view.'<br>';
						print 'view: '.print_r($view,1).'<br>';
						print '</pre>';
					}


					$this->Model = $this->getPageModel();
					$this->View = new View\View($views);
					$this->method = (isset($route[1]) ? $route[1] : 'index');
					
					return;
				}
				
				 // check for <requet_method="GET|POST">:<route_url> against $this->request_method
				// eg. $this->request_method == 'POST' -> matches: post:admin/page/create
				if($matched_route && $route_method == $this->request_method) {/*isset(static::$routes[$this->uri])*/
					if($this->debug) {
						print '<pre>Partial Route: '.$partial_uri.'</pre>';
						print '<pre>Matched Route: '.$matched_route.'</pre>';
						print '<pre>Route Requst Method: '.$route_method.'</pre>';
						print '<pre>Controller/method: '.print_r($controller_and_method,1).'</pre>';
						print '<pre>params: '.print_r($this->parameters,1).'</pre>';
					}
					
					return;
				}
			}
			
		}
		if( ! $matched_route) {
			$this->error(404);
		}
	}

	/**
	 * Return a Page_Model instance
	 */
	public function getPageModel() {
		return new Model\Page_Model();
	}

	/**
	 * Check if an array key exists
	 */
	public static function exists($uri) {
		return array_key_exists($uri, static::$routes);
	}

	/**
	 * Get the action for the specified route
	 */
	public static function action($uri) {
		if (static::exists($uri)) {
			return static::$routes[$uri];
		}
	}

	/**
	 * Get the ID hash for a route
	 */
	public static function id_encode($route_url, $method = 'get') {
		if (false === stripos($route_url, $method.':')) {
			$route_url = strtolower($method).':'.$route_url;
		}
		return base64_encode($route_url);
	}

	/**
	 * Get the route for a ID hash
	 */
	public static function id_decode($id) {
		return base64_decode($id);
	}

	/**
	 * Create a route in the route file
	 */
	public static function create($http_method__route_uri, $controller_method) {
		$changed = false;
		$file_contents = static::get_route_file(true);
		$file_lines = explode("\n", $file_contents);
		$route_left_side_operand = sprintf("\$routes['%s'] = ", $http_method__route_uri);
		$matched_line = false;

		foreach ($file_lines as $line_no => $file_line) {
			if (0 === strpos($file_line, $route_left_side_operand)) {
				$matched_line = true;
				break;
			}
		}
		if ( ! $matched_line) {
			$file_lines[] = $route_left_side_operand."'".$controller_method."';";
			$changed = true;
		}
		if ($changed) {
			return static::write_route_file($file_lines);
		}
		return $changed;
	}

	/**
	 * Update a route in the route file
	 */
	public static function update($orig_http_method__route_uri, $http_method__route_uri__controller_method_array) {
		list($http_method__route_uri, $controller_method) = $http_method__route_uri__controller_method_array;
		$changed = false;
		$file_contents = static::get_route_file(true);
		$file_lines = explode("\n", $file_contents);
		$route_left_side_operand = sprintf("\$routes['%s'] = ", $http_method__route_uri);
		$orig_route_left_side_operand = sprintf("\$routes['%s'] = ", $orig_http_method__route_uri);

		foreach ($file_lines as $line_no => $file_line) {
			if (0 === strpos($file_line, $orig_route_left_side_operand)) {
				$file_lines[$line_no] = $route_left_side_operand."'".$controller_method."';";
				$changed = true;
				break;
			}
		}
		if ($changed) {
			return static::write_route_file($file_lines);
		}
		return $changed;
	}

	/**
	 * Return the routes.php file contents, without the final "return $routes" line
	 */
	private static function get_route_file($content = true) {
		if (defined('ROUTE_FILE_PATH')) {
			if (is_readable(ROUTE_FILE_PATH)) {
				if ($content) {
					$contents = file_get_contents(ROUTE_FILE_PATH);
					static::$route_file_contents = $contents;
					$file_lines = explode("\n", $contents);
					$content_lines = array();
					$encountered_route_array_var = false;
					$last_route_array_var_line_no = 0;
					foreach ($file_lines as $line_no => $line) {
						if (false !== stripos($line, '$routes[')) {
							$encountered_route_array_var = true;
							$last_route_array_var_line_no = $line_no;
						}
					}
					foreach ($file_lines as $line_no => $line) {
						if ($last_route_array_var_line_no == 0 || ($line_no <= $last_route_array_var_line_no)) {
							$content_lines[$line_no] = $line;
						}
					}
					return implode("\n", $content_lines);
				} else {
					return ROUTE_FILE_PATH;
				}
			} else {
				throw new \Exception('Route file unreadable');
			}
		} else {
			throw new \Exception('Route file not configured');
		}
		return false;
	}

	private static function write_route_file($lines) {
		$lines[] = "\n\nreturn \$routes;";
		// write the backup first
		if (file_put_contents(ROUTE_BACKUP_FILE_PATH, static::$route_file_contents)) {
			return file_put_contents(ROUTE_FILE_PATH, implode("\n", $lines));
		}
		return false;
	}

	/**
	 * Parse a route URI string into method and uri string
	 * @param string $uri
	 * @return array
	 */
	public static function parse_method($uri) {
		$method = self::METHOD_GET;
		if (0 === stripos($uri, 'get:')) {
			$uri = str_replace('get:', '', $uri);
		}
		if (0 === stripos($uri, 'post:')) {
			$uri = str_replace('post:', '', $uri);
			$method = self::METHOD_POST;
		}
		return [ 'uri' => $uri, 'method' => $method ];
	}

	/**
	 * @return View
	 */
	public function execute() {
		if($this->debug) {
			print '<pre>';
			print 'Controller: '.get_class($this->Controller).'<br>';
			print 'method: '.$this->method.'<br>';
			if($this->parameters) {
				print 'parameters: '.print_r($this->parameters, 1).'<br>';
			}
			print '</pre>';
		}
		

		if($this->Controller && $this->method && method_exists($this->Controller, $this->method)) {
			if($this->parameters)
				return call_user_func_array(array($this->Controller, $this->method), $this->parameters);
			else
				return call_user_func(array($this->Controller, $this->method));	
		}

		$this->error(404);
	}

	public function error($code) {
		switch($code) {
			case 404:
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
				$html = file_get_contents(dirname(ROOT_PATH).'/404.html');
				$html = str_replace('$URL', $this->uri, $html);
				print $html;
				die();
			break;
		}
	}

	public function uri() {
		return $this->uri;
	}

	public function getModel() {
		return $this->Model;
	}

	public function getView() {
		return $this->View;
	}

	public static function get_uri() {
		return str_replace(APP_DIRECTORY.'/', '', $_SERVER['REQUEST_URI']);
	}
}