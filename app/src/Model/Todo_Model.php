<?php namespace MVCFAM\App\Model;
/**
 * Todo Model
 */

// imports


class Todo_Model extends Model {

	/* The Model field definitions */
	protected static $fields = array(
		'ID' => 'Int(10)',
		'Description' => 'Text(255)',
		'Completed' => 'Boolean',
		'CompletedDatetime' => 'Datetime',
		'CreatedTimestamp' => 'Datetime',
);


	/**
	 * 
	 */
	public function __construct(array $data = array(), $table = 'Todo') {
		parent::__construct('Todo', $data, $table);
	}

}
