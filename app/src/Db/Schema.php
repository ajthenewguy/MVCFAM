<?php namespace MVCFAM\App\Db;
/**
 * Database schema service
 */

use MVCFAM\App\App;
use MVCFAM\App\Db\Dbfield;
use MVCFAM\App\Db\Migration;
use MVCFAM\App\Db\DbPermission;
use MVCFAM\App\Exception\PermissionException;

class Schema {

	/**
	 * A Database connection instance
	 */
	protected static $db;

	/**
	 * Never perform SQL operations on tables or Objects in this list
	 */
	protected static $do_not_persist = [ 'Model', 'View', 'Controller' ];

	
	public function __construct() {
	}

	/**
	 * Get the $db properyy (Database)
	 */
	public static function db() {
		if (! isset(static::$db)) {
			static::set_db(App::db());
		}
		return static::$db;
	}

	public static function set_db(Database $db) {
		static::$db = $db;
	}

	public static function tables() {
		return static::db()->tables();
	}

	public static function has_table($table) {
		if ($tables = static::tables()) {
			return in_array($table, $tables);
		}
		return false;
	}

	public static function create($table_name, $fields, $engine = 'InnoDB') {
		$created = false;
		if (! in_array($table_name, static::$do_not_persist)) {
			$db_field_create_string = '';
			$primary_key_field = '';
			foreach ($fields as $key => $field) {
				$Dbfield = new Dbfield($field);
				$db_field_create_string .= $Dbfield->create_string();
				if ($Dbfield->is_primary_key()) {
					if ($primary_key_field) {
						throw new \Exception('Cannot have more than one primary key');
					}
					$primary_key_field = $Dbfield->name();
				}
			}
			$db_field_create_string = trim(trim($db_field_create_string), ',');

			$sql = "CREATE TABLE ".$table_name." (".$db_field_create_string;

			// primary key
			if ($primary_key_field) {
				// already declared in field create line
				//$sql .= ", PRIMARY KEY (`$primary_key_field`)";
			}
			$sql .= ") ENGINE=$engine;";

			static::db()->run($sql);

			$created = static::has_table($table_name);

			if ($created) {
				$config = [ 'name' => $table_name, 'fields' => $fields ];
				static::config_write($table_name, $config);
			}
		}
		return $created;
	}

	/**
	 * Drop table
	 */
	public static function drop($table_name) {
		$debug = false;
		$dropped = false;
		$Permissions = static::db()->Permissions();
		if ($Permissions->get(DbPermission::DROP) || $Permissions->get(DbPermission::ALL)) {
			if (! in_array($table_name, static::$do_not_persist)) {
				if (! $debug) {
					$sql = "DROP TABLE ".$table_name.';';
					static::db()->run($sql);
					$dropped = ! static::has_table($table_name);
				}
			}
		} else {
			throw new PermissionException('DROP', 1);
		}
		return $dropped;
	}

	/**
	 * Drop table index
	 */
	public static function drop_index($table_name, $index_name) {
		$debug = false;
		$dropped = ! $debug;
		if (! $debug) {
			$sql = "ALTER TABLE ".$table_name." DROP INDEX ".$index_name.';';
			$dropped = static::db()->run($sql);
			//$dropped = ! static::has_key($table_name, $index_name);
		}
		return $dropped;
	}

	/**
	 * Trncate table
	 */
	public static function truncate($table_name) {
		$debug = false;
		$truncated = ! $debug;
		if (! $debug) {
			$sql = "TRUNCATE TABLE ".$table_name.';';
			$truncated = static::db()->run($sql);
		}
		return $truncated;
	}

	/**
	 * Load table configuration from a local .json file
	 */
	public static function config($table) {
		if (! in_array($table, [ 'Model', 'View', 'Controller' ])) {
			$path = APP_STORAGE_PATH.'/'.$table.'.json';
			if ($contents = @file_get_contents($path)) {
				return json_decode($contents, true);
			}
		}
	}

	public static function config_write($table, $config = [], $force = false) {
		if (! in_array($table, [ 'Model', 'View', 'Controller' ])) {
			if (empty($config) && ! $force) {
				throw new \Exception("Cannot save an empty configuration file", 1);
			}
			return file_put_contents(APP_STORAGE_PATH.'/'.$table.'.json', json_encode($config));
		} else {
			throw new \Exception(sprintf("Cannot save a configuration file for %s", $table), 1);
		}
	}
}