<?php namespace MVCFAM\App\Controller;
/**
 * Admin Controller
 */

use MVCFAM\App\App;
use MVCFAM\App\Object;
use MVCFAM\App\AnObj;
use MVCFAM\App\Model\Model;
use MVCFAM\App\Route;
use MVCFAM\App\Session;

class Admin_Controller extends Page_Controller {
	
	protected $ViewName = 'Page/AdminPage';

	public function index() {
		$this->Title = 'Administration';




		/** BOF:DEV **/



		

		// Array to dump arbitrary data to the View
		$out = [];

		$Object = new Object;
		$out[] = $Object->_namespace();
		$out[] = $Object->_class_name('b');
		$out[] = $Object->_class_name('i', 'b');
		$out[] = $this->_class_name('b');

		// Push onto View
		if (isset($out[0])) {
			$this->pushContent('<div class="pure-u-1">');
			foreach ($out as $key => $value) {
				$this->pushContent('<pre>'.$value.'</pre>');
			}
			$this->pushContent('</div>');
		}
		

/*if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    $request = file_get_contents('php://input');
    var_dump($request);
    #$request = json_decode(file_get_contents('php://input'));
    #echo json_encode($request);

    exit;
}*/
/*
		$Lock = \MVCFAM\App\Helpers\Lock::create();
		$results = [];
		$results[] = $Lock('p@ssw0rd');
		for ($p = 0; $p < 20; $p++) {
			$str_array = [];
			for ($i = 0; $i < 20; $i++) {
				$str_array[] = (0 === $p % 2 ? chr(rand(33, 126)) : chr($i + 33));
			}
			$password = implode('', $str_array);
			$results[] = \MVCFAM\App\_e($Lock($password));
		}


		$Lock = new \MVCFAM\App\Helpers\Lock('1234');
		
		$Cipher = $Lock->get_cipher(0);
		$input = 4;
		$encrypt = $Cipher->encrypt;
		$decrypt = $Cipher->decrypt;
		$encrypted = $encrypt($input);
		$decrypted = $decrypt($encrypted);
		$view = ['$input' => $input, '$encrypted' => $encrypted, '$decrypted' => $decrypted];

		$this->pushContent('<div class="pure-u-1">');
		$this->pushContent('<pre>');
		$this->pushContent(print_r($results,1));
		$this->pushContent('</pre>');
		$this->pushContent('<pre>'.print_r($view,1).'</pre>');
		$this->pushContent('</div>');
*/
		$this->pushContent('<div class="pure-u-1">');
		if ($post = $this->post()) {
			$this->pushContent('<pre>POSTed Data:'."\n".print_r($post,1)."\n".'</pre>');
		}
		$this->pushContent('<form action="'.\MVCFAM\App\url('/admin').'" method = "post">
			<input type="hidden" name="ID" value="'.md5(rand(1,765)).'" />
			<input type="text" name="Name" />
			<br><br>
			<input type="submit" value="POST" />
		</form>');
		$this->pushContent('</div>');




		/** EOF:DEV **/




		return $this->View();
	}

	public function getTopLink() {
		$TopLink = new Model('TopLink', [ 'url' => \MVCFAM\App\url('/'), 'text' => 'HOME' ]);
		return $TopLink;
	}

	public function getLinks() {
		$Links = array();
		$Links[] = new Model('RoutesLink', [ 'url' => \MVCFAM\App\url('/admin'), 'text' => 'Dashbaord' ]);
		$Links[] = new Model('RoutesLink', [ 'url' => \MVCFAM\App\url('/admin/routes'), 'text' => 'Routes' ]);
		$Links[] = new Model('ModelsLink', [ 'url' => \MVCFAM\App\url('/admin/models'), 'text' => 'Models' ]);
		$Links[] = new Model('SettingsLink', [ 'url' => \MVCFAM\App\url('/admin/settings'), 'text' => 'Settings' ]);
		return $Links;
	}

	public function getListItems() {
		$Links = array();
		return $Links;
	}
}