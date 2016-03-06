<?php namespace MVCFAM\App;
/**
 * Initialize MVC FAM (From Anotha Motha)
 */
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH.'/app');
define('APP_CACHE_PATH', ROOT_PATH.'/storage/app');
define('CONFIG_PATH', ROOT_PATH.'/config');
define('VENDOR_PATH', ROOT_PATH.'/vendor');
define('APP_SRC_PATH', APP_PATH.'/src');
define('APP_HELPERS_PATH', APP_SRC_PATH.'/helpers');
define('APP_MIGRATIONS_PATH', APP_SRC_PATH.'/Migrations');
define('APP_MODELS', APP_SRC_PATH.'/Model');
define('STORAGE_PATH', ROOT_PATH.'/storage');
define('APP_STORAGE_PATH', STORAGE_PATH.'/app');
define('LOG_PATH', STORAGE_PATH.'/logs');
define('APP_DIRECTORY', 'MVC');
define('APP_NAMESPACE', '\\MVCFAM\\App\\');
define('BASE_PATH', realpath('./').'/');
define('APP_VIEW_PATH', BASE_PATH.'views/');
define('ROUTES_FILE', 'routes.php');
define('ROUTES_BACKUP_FILE', 'routes.php.bak');
define('ROUTE_FILE_PATH', CONFIG_PATH.'/'.ROUTES_FILE);
define('ROUTE_BACKUP_FILE_PATH', CONFIG_PATH.ROUTES_BACKUP_FILE);
define('SESSION_NAMESPACE', str_replace('/', '_', APP_PATH));
define('DEVELOPMENT_MODE', true);

ini_set("log_errors", 1);
ini_set("error_log", LOG_PATH.'/php-error.log');
ini_set("display_errors", 1);

#require(APP_PATH.'autoload.php');
require(VENDOR_PATH.'/autoload.php');
require(APP_HELPERS_PATH.'/html.php');
require(APP_HELPERS_PATH.'/url.php');
require(APP_HELPERS_PATH.'/view.php');

use MVCFAM\App\Db\Db;
use MVCFAM\App\Db\DbPermission;
use MVCFAM\App\Db\Migration;

putenv('COMPOSER_HOME=' . VENDOR_PATH . '/bin/composer');

Session::init();
Route::$routes = include(ROUTE_FILE_PATH);
Migration::set_path(APP_MIGRATIONS_PATH);

if (is_readable(CONFIG_PATH.'/db.php')) {
	$db = include(CONFIG_PATH.'/db.php');
	
	App::set_db($db);

	if (DEVELOPMENT_MODE) {
		App::db()->Permissions()->set(DbPermission::ALL);
	} else {
		/**
		 * Be stingy, permissions can be layered in later, for example after the user
		 * re-enters their password, or confirms an emailed code.
		 */
		App::db()->Permissions()->set(DbPermission::SELECT);
		App::db()->Permissions()->set(DbPermission::UPDATE);
		App::db()->Permissions()->set(DbPermission::INSERT);
		App::db()->Permissions()->set(DbPermission::DELETE);
		App::db()->Permissions()->set(DbPermission::INDEX);
	}
	

	#$db = Db::driver('mysql');
	#$result = $db->run('SHOW TABLES');
	#$this->pushContent('<pre class="pure-u-1">$result: '.var_export($result,1).'</pre>');
}

// can load additional routes based on permissions - sweet!