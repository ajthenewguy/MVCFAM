<?php namespace MVCFAM\App\Model;
/**
 * Article Model
 */

// imports


class Article_Model extends Model {

	/* The Model field definitions */
	protected static $fields = array(
		'ID' => 'Int(10)',
		'Title' => 'Text(255)',
		'Body' => 'Text',
		'CreatedTimestamp' => 'Datetime',
);


	/**
	 * 
	 */
	public function __construct(array $data = array(), $table = 'Article') {
		parent::__construct('Article', $data, $table);
	}
}
