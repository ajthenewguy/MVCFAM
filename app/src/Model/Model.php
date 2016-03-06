<?php namespace MVCFAM\App\Model;
/**
 * Base Model
 */

use MVCFAM\App\App;
use MVCFAM\App\Object;
use MVCFAM\App\Db\Schema;
use MVCFAM\App\Db\Migration;
use MVCFAM\App\Helpers\ClassWriter;
use MVCFAM\App\Controller\Controller;

Class Model extends Object {

	/**
	 * The database fields and types
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Additional field configuration, eg. primary_key => true
	 * @var array
	 */
	protected static $config;

	/* The Model instance discrete data */
	private $data;
	
	/* The Model name, eg. ('Car') */
	private $name;

	/* Ensure only entity fields are accessed/mutated */
	protected static $protected = false;

	/* The databse table, if applicable */
	protected $table;

	protected $Controller;

	protected static $protected_models = ['Model', 'Page_Model'];

	/**
	 * @param string $name The Model name, eg. ('Car')
	 * @param array $data The Model fields/values array
	 * @param string $table The database table/repo name
	 */
	public function __construct($name, array $data = array(), $table = null) {
		$this->name = $name;
		$this->data = $data;
		$this->table = ($table ?: $name);
		$this->protected = ($this->table ? true : false);
		$this->onInit();
	}

	protected function onInit() {
		if (isset($this->data['ID'])) {
			if ($table = $this->table()) {
				if ($result = App::db()->select($table, "`ID`=?", $this->data['ID'])) {
					if (isset($result[0])) {
						$this->data = array_merge($this->data, $result[0]);
					}
				}
			}
		}
	}

	/**
	 * Set the $Controller property
	 *
	 * @param Controller $Controller A Controller instance
	 */
	public function setController(\MVCFAM\App\Controller\Controller $Controller) {
		$this->Controller = $Controller;
	}

	public function __set($name, $value) {
		if (!static::$protected || array_key_exists($name, static::$fields)) {
			$this->data[$name] = $value;
		}
	}

	public function __get($name) {
		$value = NULL;
		if (!static::$protected || array_key_exists($name, static::$fields)) {
			if(isset($this->data[$name])) {
				$value = $this->data[$name];
			}
		}
		return $value;
	}

	public function name() {
		return $this->name;
	}

	public function table() {
		if ($this->name !== 'Model') {
			return $this->table;
		}
	}

	public function fields($include_config = false, $force_reload_config = false) {
		$fields = static::$fields;
		if ($include_config) {
			if ($config = $this->config($force_reload_config)) {
				ksort($config['fields']);
				foreach ($config['fields'] as $key => $field) {
					$fields[$field['field_name']] = $field;
				}
			}
		}
		return $fields;
	}

	/**
	 * Insert a record
	 * @param array $data The field => value array
	 */
	public function insert($data) {
		if ($table = $this->table()) {
			return App::db()->insert($table, $data);
		}
	}

	/**
	 * Update a record
	 * @param array $data The field => value array
	 */
	public function update($data, $id = null) {
		if ($table = $this->table()) {
			if (! is_null($id) || isset($data['ID'])) {
				$id = (is_null($id) ? $data['ID'] : $id);
				unset($data['ID']);
				return App::db()->update($table, $data, "`ID` = :ID", [ ':ID' => $id ]);
			}
		}
	}

	/**
	 * Count the number of records in a table and return the result
	 * @return int|false on error
	 */
	public function record_count($where = '') {
		if ($table = $this->table()) {
			return App::db()->count($table, '*', $where);
		}
	}

	public function config($reload = false) {
		if (! isset(static::$config)) {
			if ($table = $this->table()) {
				static::$config = Schema::config($table);
			}
		}
		return static::$config;
	}

	/**
	 * Check if Model has a Migration file
	 */
	public function hasMigration() {
		if ($table = $this->table()) {
			return Migration::exists($table);
		}
		return false;
	}

	/**
	 * Return the migration path
	 */
	public function migrationPath() {
		if ($table = $this->table()) {
			return Migration::path().'/'.$table.'_Migration.php';
		}
	}

	/**
	 * @deprecated
	 */
	public function config_write($config = [], $force = false) {
		if ($table = $this->table()) {
			if (empty($config) && ! $force) {
				throw new \Exception("Cannot save an empty configuration file", 1);
			}
			return file_put_contents(APP_STORAGE_PATH.'/'.$this->table(), json_encode($config));
		} else {
			throw new \Exception("Cannot save a configuration file for Model", 1);
		}
	}

	public function data($key = null) {
		if (is_null($key)) {
			$data = [];
			foreach ($this->data as $key => $var) {
				if (!static::$protected || array_key_exists($key, static::$fields)) {
					$data[$key] = $var;
				}
			}
		} else {
			$data = null;
			if (array_key_exists($key, $this->data)) {
				if (!static::$protected || array_key_exists($key, static::$fields)) {
					$data = $this->data[$key];
				}
			}
		}
		return $data;
	}

	public static function get_protected_models() {
		return static::$protected_models;
	}

	/**
	 * Delete a model record
	 */
	public function delete() {
		if ($table = $this->table()) {
			if ($id = $this->ID) {
				return App::db()->delete($table, '`ID`=?', $id);
			} else {
				throw new \Exception('ID Unknown: cannot delete record');
			}
		}
	}

	/**
	 * Delete a model file
	 */
	public static function deleteModel($ModelName) {
		if (static::delete_model_class_file($ModelName)) {
			if (static::delete_model_migration_file($ModelName)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Delete a model class file
	 */
	public static function delete_model_class_file($ModelClassName) {
		$filename = $ModelClassName.'_Model';
		$filepath = APP_MODELS.'/'.$filename.'.php';
		if (!in_array($ModelClassName, static::$protected_models)) {
			if (unlink($filepath)) {
				return true;
			}
		} else {
			throw new \Exception('Unable to delete protected Model');
		}
		return false;
	}

	/**
	 * Delete a model migration file
	 */
	public static function delete_model_migration_file($ModelClassName) {
		$filename = $ModelClassName.'_Migration';
		$filepath = Migration::path().'/'.$ModelClassName.'.php';
		if (!in_array($ModelClassName, static::$protected_models)) {
			if (unlink($filepath)) {
				return true;
			}
		} else {
			throw new \Exception('Unable to delete protected Model');
		}
		return false;
	}

	/**
	 * Issue a SQL query to create the database table
	 * @deprecated
	 */
	public static function create_table() {
		///
		return false;
	}

	protected static function has_fields() {
		return (isset(static::$fields) && !empty(static::$fields));
	}

	/**
	 * Generate the file contents for a .php file
	 * @param
	 */
	public static function generate($ModelName, $table, $import = []) {

		$Writer = new ClassWriter('MVCFAM\App\Model', $ModelName.'_Model', 'Model', $import);
		//$Writer->addMethod();

		$ModelName = ucfirst(rtrim(str_replace('Model', '', trim($ModelName)), '_'));
		$imports = '';
		$ClassName = $ModelName.'_Model';

		// Namespace imports
		if (!empty($namespace_use)) {
			foreach ($namespace_use as $FQNS => $alias) {
				$imports .= 'use '.$FQNS.(!is_null($alias) ? ' as '.$alias : '').";\n";
			}
		}

		// class file contents
		$contents = sprintf("<?php namespace MVCFAM\App\Model;
/**
 * %s Model
 */

// imports
%s

class %s extends Model {

	/* The Model field definitions */
	protected static \$fields = [];

	/**
	 * 
	 */
	public function __construct(array \$data = array(), \$table = '%s') {
		parent::__construct('%s', \$data, \$table);
	}
}
",
			$ModelName, 	//  * %s Model
			$imports,		// %s
			$ClassName, 	// Class %s {
			$table, 		//	public function __construct(array \$data = array(), \$table = '%s') {
			$ModelName		// parent::__construct('%s', $data, $table);
		);

		return $contents;
	}
}