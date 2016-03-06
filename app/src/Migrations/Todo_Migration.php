<?php namespace MVCFAM\App\Migrations;
/**
 * Todo_MigrationMigration
 */

// Imports
use \MVCFAM\App\Db\Schema;
use \MVCFAM\App\Db;
use \MVCFAM\App\Db\Migration;
use \MVCFAM\App\Migrations;
use \MVCFAM\App\Model\Model;
use \MVCFAM\App\View\View;
use \MVCFAM\App\Controller\Controller;


Class Todo_Migration extends Migration {

	// Properties




	// Methods

	public function up() {
				return Schema::create('Todo', array(
  0 => 
  array(
    'field_name' => 'ID',
    'field_type' => 'INT',
    'field_length' => '10',
    'field_default_type' => 'NONE',
    'field_default_value' => '',
    'primary_key' => '1',
    'auto_increment' => '1',
  ),
  1 => 
  array(
    'field_name' => 'Description',
    'field_type' => 'VARCHAR',
    'field_length' => '255',
    'field_default_type' => 'NULL',
    'field_default_value' => '',
    'allow_null' => '1',
  ),
  2 => 
  array(
    'field_name' => 'Completed',
    'field_type' => 'BOOLEAN',
    'field_length' => '',
    'field_default_type' => 'USER_DEFINED',
    'field_default_value' => '0',
  ),
  3 => 
  array(
    'field_name' => 'CompletedDatetime',
    'field_type' => 'DATETIME',
    'field_length' => '',
    'field_default_type' => 'NULL',
    'field_default_value' => '',
    'allow_null' => '1',
  ),
  4 => 
  array(
    'field_name' => 'CreatedTimestamp',
    'field_type' => 'TIMESTAMP',
    'field_length' => '',
    'field_default_type' => 'CURRENT_TIMESTAMP',
    'field_default_value' => '',
  ),
));

	}

	public function down() {
				return Schema::drop('Todo');

	}


}
