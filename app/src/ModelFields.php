<?php namespace MVCFAM\App;

use MVCFAM\App\Collection;

class ModelFields extends Collection {
	
	public function __construct($Fields = []) {
		$iterator_class = "ArrayIterator";
		$flags = 0;
		parent::__construct($Fields, true, $iterator_class, $flags);
	}
}