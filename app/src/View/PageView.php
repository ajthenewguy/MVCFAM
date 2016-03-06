<?php namespace MVCFAM\App\View;

class PageView extends View {
	
	protected $file_extensions = array('.php');

	protected $file_extension;

	private $exists = false;

	private $vars;


	public function __construct($files = array(), $vars = array()) {
		foreach($files as $key => $file) {
			if (false === strpos($file, 'Page/')) {
				$files[$key] = 'Page/'.$file;
			}
		}
		/*if(!in_array('Page/Page', $files)) {
			$files[] = 'Page/Page';
		}*/
		parent::__construct($files, $vars);
	}

}