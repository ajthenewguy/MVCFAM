<?php namespace MVCFAM\App\Db;
/**
 * The Database/Datastore API Interface
 */

use MVCFAM\App\Db\iDatastore;

interface iDatastore {
	
	public function connect();

	public function tables();
}