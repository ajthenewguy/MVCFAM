<?php namespace MVCFAM\App\Migrations;
/**
 * Article_MigrationMigration
 */

// Imports
use \MVCFAM\App\Db\Schema;
use \MVCFAM\App\Db;
use \MVCFAM\App\Db\Migration;
use \MVCFAM\App\Migrations;
use \MVCFAM\App\Model\Model;
use \MVCFAM\App\View\View;
use \MVCFAM\App\Controller\Controller;


Class Article_Migration extends Migration {

	// Properties




	// Methods

	public function up() {
				return Schema::create('Article', array(
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
  2 => 
  array(
    'field_name' => 'Title',
    'field_type' => 'VARCHAR',
    'field_length' => '255',
    'field_default_type' => 'USER_DEFINED',
    'field_default_value' => 'New Article',
  ),
  3 => 
  array(
    'field_name' => 'Body',
    'field_type' => 'TEXT',
    'field_length' => '',
    'field_default_type' => 'NONE',
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
				return Schema::drop('Article');

	}


}
