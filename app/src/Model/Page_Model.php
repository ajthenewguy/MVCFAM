<?php namespace MVCFAM\App\Model;
/**
 * Page Model
 */

use MVCFAM\App\App;
use MVCFAM\App\Model\Model;
use MVCFAM\App\View\View;

class Page_Model extends Model {

	/*
	private static $fields = array(
        'PlayerNumber' => 'Int',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Text',
        'Birthday' => 'Date'
    );
	*/
	protected static $fields = [
		'ID' => 'Int',
		'Title' => 'Varchar(255)',
		'Subtitle' => 'Varchar(255)'
	];

	public $View;


	public function __construct(array $data = array(), $table = 'page') {
		parent::__construct('Page', $data, $table);
	}

	/**
	 * Issue a SQL query to create the database table
	 * @deprecated
	 */
	public static function create_table() {
		if (static::has_fields()) {
			$Model = new static;
			
			// fields string
			$db_field_create_string = '';
			$field_string = "array(\n";
			foreach (static::$fields as $name => $data) {
				$field_string .= "\t\t'".$name."'".$data."',\n";
				$db_field_create_string .= "\n\t\t".$name.' '.$data.',';
			}
			$field_string .= ");\n";
			$db_field_create_string = trim(trim($db_field_create_string), ',');

			$sql = "CREATE TABLE ".$Model->table()." (".$db_field_create_string.");";

			App::db()->run($sql);
			return true;
		}
		
		return false;
	}

	public function getView() {
		if(!isset($View)) {
			$files = array('Page/'.$this->name);
			$this->View = new View($files);
		}

		return $this->View;
	}
}