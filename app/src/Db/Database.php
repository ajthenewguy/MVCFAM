<?php namespace MVCFAM\App\Db;
/**
 * Database class
 */

use MVCFAM\App\App;
use MVCFAM\App\Db\DbPermission;
use MVCFAM\App\Exception\PermissionException;

class Database implements iDatastore {

	private $error;

	private $sql;

	private $bind;

	private $errorCallbackFunction;

	private $errorMsgFormat;

	private $db;

	private $driver;

	/* connection configuration (host, db, user, pass) */
	private $config;

	/* PDO options */
	private $options;

	/* DbPermission */
	private $DbPermission;


	public function __construct($driver, $config = [], $options = []) {
		$this->driver = strtolower($driver);
		$this->config = $config;
		$this->options = array_merge([
			\PDO::ATTR_PERSISTENT => true, 
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		], $options);
	}

	public function Permissions() {
		if (! isset($this->DbPermissions)) {
			$this->DbPermissions = new DbPermission();
		}
		return $this->DbPermissions;
	}

	public function connect(/*$host = '', $name = '', $user = '', $pass = '', $config = []*/) {
		try {
			//$args   = func_get_args();
			$host   = $this->config['host'];//(isset($args[0]) ? $args[0] : (isset($this->config['host']) ? $this->config['host'] : ''));
			$name   = $this->config['name'];//(isset($args[1]) ? $args[1] : (isset($this->config['name']) ? $this->config['name'] : ''));
			$user   = $this->config['user'];//(isset($args[2]) ? $args[2] : (isset($this->config['user']) ? $this->config['user'] : ''));
			$pass   = $this->config['pass'];//(isset($args[3]) ? $args[3] : (isset($this->config['pass']) ? $this->config['pass'] : ''));
			switch($this->driver) {
				case 'mssql':
				case 'sybase':
					$this->db = new \PDO(sprintf("mssql:host=%s;dbname=%s, %s, %s", $host, $name, $user, $pass, $this->options));
	  				$this->db = new \PDO(sprintf("sybase:host=%s;dbname=%s, %s, %s", $host, $name, $user, $pass, $this->options));
				break;
				case 'mysql':
					$this->db = new \PDO(sprintf("mysql:host=%s;dbname=%s", $host, $name), $user, $pass, $this->options);
				break;
				case 'sqlite':
					$this->db = new \PDO(sprintf("sqlite:%s", $args[0]));
				break;
				default:
					throw new \Exception("Invalid Datastore Driver", 1);
				break;
			}
		} catch (\PDOException $e) {
			throw $e;
		}
		return $this;
	}

	public function db() {
		if (!isset($this->db)) {
			$this->connect();
		}
		return $this->db;
	}

	public function tables() {
		$tables = [];
		if ($result = $this->run('SHOW TABLES')) {
			foreach ($result as $key => $table) {
				foreach ($table as $_key => $table_name) {
					$tables[] = $table_name;
				}
			}
		}
		return $tables;
	}

	/**
	 * return the last error
	 */
	public function error() {
		return $this->error;
	}

	/**
	 * Delete a row from the database
	 */
	public function delete($table, $where, $bind="") {
		if ($this->Permissions()->get(DbPermission::DELETE) || $this->Permissions()->get(DbPermission::ALL)) {
			$sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
			return $this->run($sql, $bind);
		} else {
			throw new PermissionException('DELETE', 1);
		}
	}

	/**
	 * Insert a row into the database
	 */
	public function insert($table, $info) {
		if ($this->Permissions()->get(DbPermission::INSERT) || $this->Permissions()->get(DbPermission::ALL)) {
			$fields = $this->filter($table, $info);
			$sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";
			$bind = array();
			foreach($fields as $field)
				$bind[":$field"] = $info[$field];
			if ($this->run($sql, $bind)) {
				return $this->db()->lastInsertId();
			}
		} else {
			throw new PermissionException('INSERT', 1);
		}
	}

	/**
	 */
	public function run($sql, $bind="") {
		$this->sql = trim($sql);
		$this->bind = $this->cleanup($bind);
		$this->error = "";

		try {
			$pdostmt = $this->db()->prepare($this->sql);
			if($pdostmt->execute($this->bind) !== false) {
				if(preg_match("/^(" . implode("|", array("select", "describe", "pragma", "show")) . ") /i", $this->sql))
					return $pdostmt->fetchAll(\PDO::FETCH_ASSOC);
				elseif(preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $this->sql))
					return $pdostmt->rowCount();
			}
		} catch (\PDOException $e) {
			$this->error = $e->getMessage();	
			$this->debug();
			return false;
		}
	}

	/**
	 */
	public function select($table, $where="", $bind="", $fields="*") {
		if ($this->Permissions()->get(DbPermission::SELECT) || $this->Permissions()->get(DbPermission::ALL)) {
			$sql = "SELECT " . $fields . " FROM " . $table;
			if(!empty($where))
				$sql .= " WHERE " . $where;
			$sql .= ";";
			return $this->run($sql, $bind);
		} else {
			throw new PermissionException('SELECT', 1);
		}
	}

	/**
	 * Return a count
	 */
	public function count($table, $field = '*', $where = '') {
		$count = false;
		if ($result = App::db()->run("SELECT COUNT($field) FROM `$table`".($where ? ' WHERE '.$where : ''))) {
			if (isset($result[0])) {
				$result = $result[0];
				$index = 'COUNT('.$field.')';
				if (isset($result[$index])) {
					$count = $result[$index];
				}
			}
		}
		return $count;
	}

	/**
	 */
	public function update($table, $info, $where, $bind="") {
		if ($this->Permissions()->get(DbPermission::UPDATE) || $this->Permissions()->get(DbPermission::ALL)) {
			$fields = $this->filter($table, $info);
			$fieldSize = sizeof($fields);

			$sql = "UPDATE " . $table . " SET ";
			for($f = 0; $f < $fieldSize; ++$f) {
				if($f > 0)
					$sql .= ", ";
				$sql .= $fields[$f] . " = :update_" . $fields[$f]; 
			}
			$sql .= " WHERE " . $where . ";";

			$bind = $this->cleanup($bind);
			foreach($fields as $field)
				$bind[":update_$field"] = $info[$field];
			
			return $this->run($sql, $bind);
		} else {
			throw new PermissionException('UPDATE', 1);
		}
	}

	/**
	 */
	public function setErrorCallbackFunction($errorCallbackFunction, $errorMsgFormat="html") {
		//Variable functions for won't work with language constructs such as echo and print, so these are replaced with print_r.
		if(in_array(strtolower($errorCallbackFunction), array("echo", "print")))
			$errorCallbackFunction = "print_r";

		if(function_exists($errorCallbackFunction)) {
			$this->errorCallbackFunction = $errorCallbackFunction;	
			if(!in_array(strtolower($errorMsgFormat), array("html", "text")))
				$errorMsgFormat = "html";
			$this->errorMsgFormat = $errorMsgFormat;	
		}	
	}


	/**
	 * Proxy to PDO instance
	 */
	public function __call($method, $args) {
		return call_user_func_array(array($this->db(), $method), $args);
	}

	/**
	 */
	private function debug() {
		if(!empty($this->errorCallbackFunction)) {
			$error = array("Error" => $this->error);
			if(!empty($this->sql))
				$error["SQL Statement"] = $this->sql;
			if(!empty($this->bind))
				$error["Bind Parameters"] = trim(print_r($this->bind, true));

			$backtrace = debug_backtrace();
			if(!empty($backtrace)) {
				foreach($backtrace as $info) {
					if($info["file"] != __FILE__)
						$error["Backtrace"] = $info["file"] . " at line " . $info["line"];	
				}		
			}

			$msg = "";
			if($this->errorMsgFormat == "html") {
				if(!empty($error["Bind Parameters"]))
					$error["Bind Parameters"] = "<pre>" . $error["Bind Parameters"] . "</pre>";
				$css = trim(file_get_contents(dirname(__FILE__) . "/error.css"));
				$msg .= '<style type="text/css">' . "\n" . $css . "\n</style>";
				$msg .= "\n" . '<div class="db-error">' . "\n\t<h3>SQL Error</h3>";
				foreach($error as $key => $val)
					$msg .= "\n\t<label>" . $key . ":</label>" . $val;
				$msg .= "\n\t</div>\n</div>";
			}
			elseif($this->errorMsgFormat == "text") {
				$msg .= "SQL Error\n" . str_repeat("-", 50);
				foreach($error as $key => $val)
					$msg .= "\n\n$key:\n$val";
			}

			$func = $this->errorCallbackFunction;
			$func($msg);
		}
	}

	/**
	 */
	private function filter($table, $info) {
		$driver = $this->getAttribute(\PDO::ATTR_DRIVER_NAME);
		if($driver == 'sqlite') {
			$sql = "PRAGMA table_info('" . $table . "');";
			$key = "name";
		} elseif($driver == 'mysql') {
			$sql = "DESCRIBE " . $table . ";";
			$key = "Field";
		} else {	
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
			$key = "column_name";
		}	

		if(false !== ($list = $this->run($sql))) {
			$fields = array();
			foreach($list as $record)
				$fields[] = $record[$key];
			return array_values(array_intersect($fields, array_keys($info)));
		}
		return array();
	}

	/**
	 */
	private function cleanup($bind) {
		if(!is_array($bind)) {
			if(!empty($bind))
				$bind = array($bind);
			else
				$bind = array();
		}
		return $bind;
	}
}