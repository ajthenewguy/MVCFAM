<?php namespace MVCFAM\App\Helpers;
/**
 * Writes PHP class files
 */

use \Composer\Console\Application;
use \Symfony\Component\Console\Input\ArrayInput;

 class ClassWriter {

 	private $namespace;

 	private $abstract;

 	private $name;

 	private $imports;

 	private $extends;

 	private $traits;

 	private $properties = [];

 	private $methods = [];


 	public function __construct($namespace, $name, $extends = null, $import = [], $use = []) {
 		$this->setNamespace($namespace);
 		$this->name = $name;
 		if (! is_null($extends)) {
 			$this->extends = $extends;
 		}

 		// Namespace imports
		if (! empty($import)) {
			$this->imports = $import;
		}
		if (! empty($use)) {
			$this->traits = $use;
		}
 	}

 	public function is_abstract() {
 		return $this->abstract;
 	}

 	public function setAbstract($abstract = false) {
 		$this->abstract = $abstract;
 		return $this;
 	}

 	public function setNamespace($namespace) {
 		$this->namespace = $namespace;
 	}

 	public function addProperty($name, $visibility = 'public', $static = false, $default = null, $comment = '') {
 		$this->properties[$name] = [
 			'comment' => $comment,
 			'name' => $name,
 			'visibility' => $visibility,
 			'static' => $static,
 			'default' => $default
 		];
 		return $this;
 	}

 	public function addMethod($name, $visibility = 'public', $static = false, $default_arguments = [], $function_body = '', $comment = '') {
 		$this->methods[$name] = [
 			'comment' => $comment,
 			'name' => $name,
 			'visibility' => $visibility,
 			'static' => $static,
 			'arguments' => $default_arguments,
 			'body' => $function_body
 		];
 		return $this;
 	}

 	/**
 	 * Run $ compoers dump-autoload
 	 */
 	public static function dump_autoload() {
 		$input = new ArrayInput(array('command' => 'dump-autoload'));
		$application = new Application();
		$application->setAutoExit(false); // prevent `$application->run` method from exitting the script
		$application->run();
 	}

 	/**
 	 * Return the "private static $fields" array
 	 */
 	public static function static_fields($config) {
 		$static_fields = [];
 		if (array_key_exists('fields', $config)) {
 			$config = $config['fields'];
 		}
 		/*
		Array
		(
		    [0] => Array
		        (
		            [field_name] => ID
		            [field_type] => INT
		            [field_length] => 10
		            [field_default_type] => NONE
		            [field_default_value] => 
		            [primary_key] => 1
		            [auto_increment] => 1
		        )

		    [1] => Array
		        (
		            [field_name] => Title
		            [field_type] => VARCHAR
		            [field_length] => 255
		            [field_default_type] => NULL
		            [field_default_value] => 
		            [allow_null] => 1
		        )

		    [2] => Array
		        (
		            [field_name] => CreatedTimestamp
		            [field_type] => TIMESTAMP
		            [field_length] => 
		            [field_default_type] => CURRENT_TIMESTAMP
		            [field_default_value] => 
		        )

		)
		->
		private static $fields = array(
	        'PlayerNumber' => 'Int',
	        'FirstName' => 'Varchar(255)',
	        'LastName' => 'Text',
	        'Birthday' => 'Date'
	    );
 		*/
 		foreach ($config as $field) {
 			$static_fields[$field['field_name']] = static::field_type_def($field['field_type'], ($field['field_length'] ?: 0));
		}
		return $static_fields;
 	}

 	public static function field_type_def($field_type, $length = 0) {
 		list($type, $length) = static::friendly_field_type($field_type, $length);
 		return $type.($length ? '('.$length.')' : '');
 	}

 	public static function friendly_field_type($field_type, $length = null) {
 		$type = null;
 		switch ($field_type) {
 			case 'YEAR':
 				$length = 4;
 			case 'TINYINT':
 			case 'SMALLINT':
 			case 'MEDIUMINT':
 			case 'INT':
 			case 'BIGINT':
 			case 'SERIAL':
 				$type = 'Int';
 			break;
 			case 'DECIMAL':
 			case 'FLOAT':
 			case 'DOUBLE':
 			case 'REAL':
 				$type = 'Decimal';
 			break;
 			case 'BIT':
 			case 'BOOLEAN':
 				$type = 'Boolean';
 			break;
 			case 'DATE':
 				$type = 'Date';
 			break;
 			case 'DATETIME':
 			case 'TIMESTAMP':
 				$type = 'Datetime';
 			break;
 			case 'TIME':
 				$type = 'Time';
 			break;
 			case 'CHAR':
 			case 'VARCHAR':
 				$type = 'Varchar';
 			case 'TINYTEXT':
 			case 'TEXT':
 			case 'MEDIUMTEXT':
 			case 'LONGTEXT':
 			case 'BINARY':
 			case 'VARBINARY':
 			case 'TINYBLOB':
 			case 'MEDIUMBLOB':
 			case 'SET':
 			case 'LONGBLOB':
 				$type = 'Text';
 			break;
 			case 'BLOB':
 			case 'ENUM':
 				$type = 'Enum';
 			break;
 			case 'GEOMETRY':
 			case 'POINT':
 			case 'LINESTRING':
 			case 'POLYGON':
 			case 'MULTIPOINT':
 			case 'MULTILINESTRING':
 			case 'MULTIPOLYGON':
 			case 'GEOMETRYCOLLECTION':
 				$type = 'Spatial';
 			break;
 		}
 		
 		return [ $type, $length ];
 	}

 	/**
 	 * Build the class file contents string
 	 *
 	 * @return string
 	 */
 	public function write() {
 		$class = $class_line = ucfirst(trim($this->name));
 		$imports = 
 		$fields_string = 
 		$methods_string = '';
		if (isset($this->extends)) {
 			foreach ([ 'Model', 'View', 'Controller' ] as $class_name) {
				if (stristr($this->extends, $class_name)) {
					$class = rtrim(str_replace($class_name, '', $class), '_');
					break;
	 			}
 			}
 			$class_line .= ' extends '.$this->extends;
 			$class .= $this->extends;
 		}

 		// Imports
		if (! empty($this->imports)) {
			foreach ($this->imports as $FQNS => $alias) {
				$imports .= 'use '.$FQNS.(!is_null($alias) ? ' as '.$alias : '').";\n";
			}
		}

		// Properties
		foreach ($this->properties as $key => $property) {
			$visibility = (isset($property['visibility']) ? $property['visibility'].' ' : '');
			$static = (isset($property['static']) ? (!! $property['static'] ? 'static ' : '') : '');
			$default = '';
			if (isset($property['default'])) {
				$string_copy = var_export($property['default'], true);
				if (false !== strpos($string_copy, "\n);")) {
					$string_copy = str_replace("\n);", "\n\t);", $string_copy);
				}
				if (false !== strpos($string_copy, "=> \n  array);")) {
					$string_copy = str_replace("=> \n  array", "=> array", $string_copy);
				}
				$string_copy = str_replace("\n", "\n\t\t\t\t", $string_copy);
				/**/
				$default .= ' = '.$string_copy;
			}
			$fields_string .= sprintf("\t%s%s%s%s;\n\n", $visibility, $static, $property['name'], $default);
		}

		// Methods
		foreach ($this->methods as $key => $method) {
			$visibility = (isset($method['visibility']) ? $method['visibility'].' ' : '');
			$static = (isset($method['static']) ? (!! $method['static'] ? 'static ' : '') : '');
			$arguments = '';
			if (isset($property['arguments'])) {
				foreach ($property['arguments'] as $name => $value) {
					if (strlen($arguments) > 0) $arguments .= ', ';
					$arguments .= '$'.$name.(! is_null($value) ? ' = '.var_export($value, true) : '');
				}
			}
			$methods_string .= sprintf("\t%s%sfunction %s(%s) {\n\t\t%s\n\t}\n\n", $visibility, $static, $method['name'], $arguments, $method['body']);
		}

 		// class file contents
		$contents = sprintf("<?php namespace %s;
/**
 * %s
 */

%s

%sClass %s {

	// Properties

%s


	// Methods

%s
}
",
			$this->namespace,
			$class,
			($imports ? "// Imports\n".$imports : ''),
			($this->is_abstract() ? 'abstract ' : ''),
			$class_line,
			($fields_string  ?: ''),
			($methods_string ?: '')
		);

		return $contents;
 	}
 }