<?php namespace MVCFAM\App\Db;
/**
 * A Database migration
 */

use MVCFAM\App\Object;
use MVCFAM\App\Db\Schema;
use MVCFAM\App\Helpers\ClassWriter;

abstract class Migration extends Object {

	protected static $path;


	public function beforeDown() {
		return true;
	}

	public function beforeUp() {
		return true;
	}

	public function down() {
		if ($this->beforeDown()) {
			// ..Schema::drop('flights');
			return true;
		}
		return false;
	}

	public function up() {
		if ($this->beforeUp()) {
			/*
			...Schema::create('flights', function (Blueprint $table) {
	            $table->increments('id');
	            $table->string('name');
	            $table->string('airline');
	            $table->timestamps();
	        });
			*/
			return true;
		}
		return false;
	}

	public static function exists($table_name) {
		$class_name = $table_name.'_Migration';
		$Migration_path = Migration::path().'/'.$class_name.'.php';
		if (is_readable($Migration_path)) {
			$MigrationClassName = '\\MVCFAM\\App\\Migrations\\'.$class_name;

			$Migration = new $MigrationClassName;
			/*if (class_exists($MigrationClassName)) {
				return true;
			} else {
				\MVCFAM\App\message(sprintf('Error autoloading class %s', $class_name), 'error');
			}*/
			if ($Migration instanceof Migration) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Create an extension of this class
	 */
	public static function create($namespace = '', $ModelName, $imports = []) {
		$namespace = 'MVCFAM\\App\\'.(str_replace('App\\', '', str_replace('MVCFAM\\', '', $namespace)));
		$ModelName = trim($ModelName);
		if (0 === strpos($ModelName, $namespace)) {
			$ModelName = trim(str_replace($namespace, '', $ModelName), '\\');
		}
		if (0 === strpos($ModelName, 'Model')) {
			$ModelName = trim(str_replace('Model', '', $ModelName), '_');
		}
		if (0 === strpos($ModelName, 'View')) {
			$ModelName = trim(str_replace('View', '', $ModelName), '_');
		}
		if (0 === strpos($ModelName, 'Controller')) {
			$ModelName = trim(str_replace('Controller', '', $ModelName), '_');
		}
		$ModelName = ucfirst($ModelName);
		$imports = array_merge([
			'\MVCFAM\App\Db\Schema' => null,
			'\MVCFAM\App\Db' => null,
			'\MVCFAM\App\Db\Migration' => null,
			'\MVCFAM\App\Migrations' => null,
			'\MVCFAM\App\Model\Model' => null,
			'\MVCFAM\App\View\View' => null,
			'\MVCFAM\App\Controller\Controller' => null
		], $imports);

		$Writer = new ClassWriter($namespace, $ModelName.'_Migration', 'Migration', $imports);
		#$Writer->addMethod('down', 'public', false, [], "parent::down();\n");
		#$Writer->addMethod('up', 'public', false, [], "parent::up();\n");

		return $Writer;
	}

	/**
	 * Set the path to the applications migrations directory
	 * @param string $path
	 */
	public static function set_path($path) {
		static::$path = $path;
	}

	public static function path() {
		return static::$path;
	}
}