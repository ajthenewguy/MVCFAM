<?php namespace MVCFAM\App\Model;
/**
 * Project Model
 */

// imports


class Project_Model extends Model {

	/* The Model field definitions */
	protected static $fields = array(
		'ID' => 'Int(10)',
		'Title' => 'Text(255)',
		'CreatedTimestamp' => 'Datetime',
);


	/**
	 * 
	 */
	public function __construct(array $data = array(), $table = 'project') {
		parent::__construct('Project', $data, $table);
	}
	
	
}
