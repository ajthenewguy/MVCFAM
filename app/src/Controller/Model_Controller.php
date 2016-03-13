<?php namespace MVCFAM\App\Controller;
/**
 * Home Controller
 */

use MVCFAM\App\App;
use MVCFAM\App\Db\Db;
use MVCFAM\App\Db\Migration;
use MVCFAM\App\Db\Schema;
use MVCFAM\App\Model\Model;
use MVCFAM\App\Route;
use MVCFAM\App\Session;
use MVCFAM\App\Helpers\ClassWriter;
use MVCFAM\App\View\Html;

class Model_Controller extends Admin_Controller {

	protected $home_path = '/admin/models';

	protected $create_path = '/admin/model/create';

	protected $update_path = '/admin/model/edit/';

	protected $delete_path = '/admin/model/delete/';

	protected $migration_create_path = '/admin/model/migration/create';

	protected $table_create_path = '/admin/model/table/create';

	protected $table_drop_path = '/admin/model/table/drop';

	protected $fields_edit_path = '/admin/model/fields/edit/';

	protected $view_records_path = '/admin/model/records/';

	protected $ModelName = 'Model';
	
	protected $ViewName = 'Page/AdminPage';

	
	/**
	 * {"route": "get:/admin/models", "action": "Model/index"}
	 */
	public function index() {
		$this->Title = 'Models';
		$this->Breadcrumb('/admin', '<i class="fa fa-angle-left"></i> Dashboard');
		$Controls = $jQuery_handlers = [];
		$Controls[] = new Model('AddNewRouteButton', [ 'url' => \MVCFAM\App\url('/admin/model/create'), 'text' => 'New Model' ]);
		$Models = $this->Models();
		$database_tables = App::db()->tables();

		$table_html = '<div class="pure-u-1 "><div class="table-responsive"><table class="mq-table pure-table" width="100%">
		    <thead>
		        <tr>
		            <th>#</th>
		            <th>Name</th>
		            <th>Table</th>
		            <th># Records</th>
		            <th># Fields</th>
		            <th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody>';
		$count = 0;
		foreach ($Models as $key => $Model) {
			$Fields = new \MVCFAM\App\ModelFields();


			// ->name  ->html
			$count++;
			$actions = [];
			$delete_link = $edit_link = $edit_fields_link = $create_table_link = $drop_table_link = $view_records_link = '';
			$ModelName = $Model->name();
			$ModelTable = $Model->table();
			$ModelMigration_exists = $Model->hasMigration();
			$ModelRecordCount = 0;
			$ModelTable_exists = in_array($ModelTable, $database_tables);
			$ModelFields = $Model->fields();
			$ModelFieldCount = count($ModelFields);
			if ($ModelName !== 'Model') {
				$ModelName = $ModelName.'_Model';
			}
			$ModelName .= '.php';
			$filepath = APP_MODELS.'/'.$ModelName;
			if ($ModelTable_exists) {
				$ModelRecordCount = $Model->record_count();
			}

			$datastore_html = ($ModelTable ? '<span class="'.($ModelTable_exists ? 'alert-success' : 'alert-error').'">'.$ModelTable.'</span>' : '');

			if ( ! in_array($Model->name, Model::get_protected_models())) {
				$remaining_steps_for_db_table = 0;
				if (! $ModelMigration_exists) {
					$remaining_steps_for_db_table++;
				}
				if (! $ModelTable_exists) {
					$remaining_steps_for_db_table++;
				}
				// @todo: abstract to Model
				/*$edit_link = \MVCFAM\App\html_button('Edit', \MVCFAM\App\url($this->update_path.$Model->name), 'pure-button button-xsmall');
				$actions[] = $edit_link;
				*/;
				
				// @todo: abstract to Model
				$preview_link = \MVCFAM\App\html_button('Preview', 'preview(\''.\MVCFAM\App\_e($Model->name()).'\')', 'pure-button button-xsmall', 'modal');
				$preview_link .= sprintf('<div id="%s" title="%s"><pre>%s</pre></div>', \MVCFAM\App\_e($Model->name()), \MVCFAM\App\_e($Model->name()), \highlight_string(file_get_contents($filepath), true));
				$actions[] = $preview_link;
				
				// @todo: abstract to Model
				if ($Model->name() !== 'Model') {
					$delete_link = '<form class="pure-form" action="'.\MVCFAM\App\url($this->delete_path.$Model->name()).'" method="post" ';
					$delete_link .= sprintf('onsubmit="return confirm(\'Delete %s permanently?\')" ', $Model->name());
					$delete_link .= 'style="display:inline-block;margin:0 auto;">
						<input type="hidden" name="Model" value="'.$Model->name().'" />'
						.\MVCFAM\App\html_button('Delete', false, 'pure-button button-xsmall button-error', 'submit').
						'</form>';
					$actions[] = $delete_link;
				}
				
				$jQuery_handlers[] = '$("#'.\MVCFAM\App\_e($Model->name()).'").dialog({ autoOpen: false, minWidth: 1000, modal: true });';

				// Create table button
				if (! $ModelTable_exists && $ModelTable) {
					$edit_fields_link = \MVCFAM\App\html_button('Edit Fields', \MVCFAM\App\url($this->fields_edit_path.$Model->name()), 'pure-button button-xsmall'.($ModelFieldCount ? '' : ' button-secondary'));
					if ($ModelMigration_exists) {
						$uri = \MVCFAM\App\url($this->table_create_path);
						$button_text = 'x Run Migration';
						$confirm_message_text = 'Run database table migration?';
					} else {
						$uri = \MVCFAM\App\url($this->migration_create_path);
						$button_text = '+ Create Migration';
						$confirm_message_text = 'Create database table migration?';
					}

					$create_table_link = '<form class="pure-form float-right" action="'.$uri.'" method="post" ';
					
					if ($ModelFieldCount > 0) {
						$create_table_link .= sprintf('onsubmit="return confirm(\''.$confirm_message_text.'\')" ', $Model->table());
					} else {
						$create_table_link .= sprintf('onsubmit="return alertCancel(\'Model needs fields before a database table can be created\')" ', $Model->table());
					}
					
					$create_table_link .= 'style="display:inline-block;margin:0 auto;">
						<input type="hidden" name="Model" value="'.$Model->name().'" />'
						.\MVCFAM\App\html_button($button_text, false, 'pure-button pure-button-primary button-xsmall', 'submit').
						'</form>';
				} else {
					if ($ModelTable) {
						$drop_table_link = '<form class="pure-form float-right" action="'.\MVCFAM\App\url($this->table_drop_path).'" method="post" ';
						$drop_table_link .= sprintf('onsubmit="return confirm(\'This will DROP the table %s and all rows forever. Continue?\')" ', $Model->table());
						$drop_table_link .= 'style="display:inline-block;margin:0 auto;">
							<input type="hidden" name="Model" value="'.$Model->name().'" />'
							.\MVCFAM\App\html_button('Drop Table', false, 'pure-button pure-button-primary button-xsmall button-error control', 'submit').
							'</form>';

						$records_link_url = ($ModelRecordCount ? $this->view_records_path.$Model->name() : '/admin/model/record/create/'.$Model->name());
						$view_records_link = \MVCFAM\App\html_button(($ModelRecordCount ? Html\Icon::create('database').' View Records' : 'Create New '.$Model->name()), \MVCFAM\App\url($records_link_url), 'pure-button button-xsmall'.($ModelRecordCount ? '' : ' button-secondary'));
					}
				}
			}

			$table_html .= '<tr'.($count % 2 == 0 ? ' class="pure-table-odd"' : '').'>
	            <td>'.$count.'</td>
	            <td>'.$Model->html.'</td>
	            <td>'.($datastore_html ?: '&nbsp;').($create_table_link ? ' &nbsp; '.$create_table_link : '').($drop_table_link ? ' &nbsp; '.$drop_table_link : '').'</td>
	            <td>'.sprintf('%d', $ModelRecordCount).' &nbsp; '.$view_records_link.'</td>
	            <td>'.$ModelFieldCount.($edit_fields_link ? ' &nbsp; '.$edit_fields_link : '').'</td>
	            <td>'.implode(' &nbsp;', $actions).'</td>
	        </tr>';
		}
		$table_html .= '</tbody></table></div></div>';

		$this->pushContent($table_html);
		$this->pushContent("</ul>\n");
		$this->pushContent('<script type="text/javascript">
			function preview(ele_id) {
				$("#" + ele_id).dialog("open");
			};
			function alertCancel(msg) {
				alert(msg);
				return false;
			}
			jQuery(document).ready(function($) {

				'.implode("\n\t\t", $jQuery_handlers).'
				
			}); 
		</script>');
		
		return $this->View([ 'Controls' => $Controls ]);
	}

	/**
	 * Create Model
	 * {"route": "get:/admin/model/create", "action": "Model/create"}
	 */
	public function create() {
		$this->Title = 'New '.$this->ModelName;
		$this->Breadcrumb('/admin', Html\Icon::create('angle-double-left').' Dashboard');
		$this->Breadcrumb($this->home_path, Html\Icon::create('angle-left').' Models');
		$namespaces = App::namespaces();
		$classes = App::classes();
		$imports = [
			'MVCFAM\App\App' => '\MVCFAM\App\App',
			'MVCFAM\App\Model\Model' => '\MVCFAM\App\Model\Model',					// merged in later anyways, delete when addntl imports listed here
			'MVCFAM\App\Controller\Controller' => '\MVCFAM\App\Controller\Controller'		// merged in later anyways, delete when addntl imports listed here
		];
		$this->pushContent('<div class="pure-u-1"><pre>'.print_r($namespaces,1).'</pre></div>');
		$this->pushContent('<div class="pure-u-1"><pre>'.print_r($classes,1).'</pre></div>');
		$this->pushContent('<div class="pure-u-1 "><form action="'.\MVCFAM\App\url($this->Route->uri()).'" name="Model:create" method="post" class="pure-form pure-form-aligned">');
		$this->pushContent('<fieldset>');
		$this->pushContent('<div class="pure-control-group"><label for="Name">Name</label><input type="text" name="Name" id="Name" /></div>');
		$this->pushContent('<div class="pure-control-group">');
		$this->pushContent('<label for="Type">Import</label>');

		$this->pushContent(Html\Select::create('import[]', $imports, [], [ 'id' => 'Type' ]));

		$this->pushContent('</div>');
		/* @todo: Get HTML form fields for datastore field metadata from polymorphic database field classes, eg. $html = DatabaseField::get($type)->setName($name)->html();
			// DatabaseField::formInputs(); or something...
			// loop form inputs to generate HTML
		*/
		#$this->pushContent('<div class="pure-control-group"><label for="onInit">onInit() Code</label><textarea name="onInit" id="onInit" rows="5" cols="50"></textarea></div>');
		$this->pushContent('<div class="pure-controls"><button type="submit" class="pure-button pure-button-primary">Create</button></div>');
		$this->pushContent('</fieldset>');
		$this->pushContent('</form></div>');

		return $this->View();
	}

	/**
	 * Create Model: process
	 * {"route": "post:/admin/model/create", "action": "Model/process_create"}
	 */
	public function process_create() {
		$POST = $this->post();

		// validate
		if (!isset($POST['Name']) || empty(trim($POST['Name']))) {
			\MVCFAM\App\message('Model name required', 'error');
			return \MVCFAM\App\redirect($this->create_path);
		}

		$ModelName = ucfirst(rtrim(str_replace('Model', '', trim($POST['Name'])), '_'));
		$data_repo = $ModelName;

		// Write Model class file
		$imports = [];
		if (isset($POST['import[]'])) {
			if (!empty($POST['import[]'])) {
				foreach ($POST['import[]'] as $key => $FQNS) {
					$imports[$FQNS] = null;
				}
			}
		}
		$imports = array_merge([
			'\MVCFAM\App\Model\Model' => null,
			'\MVCFAM\App\View\View' => null,
			'\MVCFAM\App\Controller\Controller' => null
		], $imports);
		
		if ($class_file_contents = Model::generate($ModelName, $data_repo, $imports)) {
			$filename = $ModelName.'_Model.php';
			$filepath = APP_MODELS.'/'.$filename;

			// Model create: file MUST not exist
			if (!file_exists($filepath)) {
				if (file_put_contents($filepath, $class_file_contents)) {
					return \MVCFAM\App\redirect('/admin/model/fields/edit/'.$ModelName);
					#\MVCFAM\App\message('Model class created', 'success');
					#return \MVCFAM\App\redirect($this->home_path);
				} else {
					return \MVCFAM\App\redirect($this->create_path, [ sprintf('Error writing Model class file to %s', $filepath), 'error' ]);
				}
			} else {
				// @todo: sticky form data
				\MVCFAM\App\message(sprintf('Model class already exists at %s', $filepath), 'error');
				return \MVCFAM\App\redirect($this->create_path);
			}
		} else {
			\MVCFAM\App\message(sprintf('Error generatingModel class already exists at %s', $filepath), 'error');
			return \MVCFAM\App\redirect($this->create_path);
		}

		return \MVCFAM\App\redirect($this->home_path);
	}

	/**
	 * Write database table create migration if schema config file exists
	 * {"route": "post:/admin/mode/migration/create", "action": "Model/migration_create"}
	 */
	public function migration_create() {
		if($POST = $this->post()) {
			$ModelName = $POST['Model'];
			if ($ModelName != 'Model') {
				$FQNS = '\\MVCFAM\\App\\Model\\'.$ModelName.'_Model';
				$Model = new $FQNS();

				if ($table = $Model->table()) {
					if ($config = $Model->config()) {
						if (! $Model->hasMigration()) {
							$up_method_body = 'return false;';
							$down_method_body = 'return false;';
							if (! empty($config['fields'])) {
								$static_fields_config_string = str_replace('array (', 'array(', var_export($config['fields'], true));
								$up_method_body = sprintf("\t\treturn Schema::create('%s', %s);\n", $table, $static_fields_config_string);
								$down_method_body = sprintf("\t\treturn Schema::drop('%s');\n", $table);
							}
							$Writer = Migration::create('Migrations', $ModelName);
							$Writer->addMethod('up', 'public', false, [], $up_method_body, 'Issue a SQL query to CREATE the database table');
							$Writer->addMethod('down', 'public', false, [], $down_method_body, 'Issue a SQL query to DROP the database table');

							if ($migration_written = file_put_contents(Migration::path().'/'.$table.'_Migration.php', $Writer->write())) {
								\MVCFAM\App\message('Migration procedure created', 'success');
								ClassWriter::dump_autoload();
							} else {
								\MVCFAM\App\message('Error writing Migration file', 'error');
							}
						} else {
							// Migration file already written
						}
					} else {
						\MVCFAM\App\message('Error loading model field configuration file', 'error');
					}
				} else {
					\MVCFAM\App\message(sprintf('%s does not have a table defined', $ModelName), 'error');
				}
			} else {
				\MVCFAM\App\message('Base Model class shall not persist', 'error');
			}
		}
		return \MVCFAM\App\redirect($this->home_path);
	}

	/**
	 * Run a Model Migration to create a database table
	 * {"route": "post:/admin/model/table/create", "action": "Model/table_create"}
	 */
	public function table_create() {
		if($POST = $this->post()) {
			$ModelName = $POST['Model'];
			if ($ModelName != 'Model') {
				$FQNS = '\\MVCFAM\\App\\Model\\'.$ModelName.'_Model';
				$Model = new $FQNS();
				if ($Model->table()) {
					if ($Model->hasMigration()) {
						$MigrationClassName = '\\MVCFAM\\App\\Migrations\\'.$Model->table().'_Migration';
						$Migration = new $MigrationClassName;
						if ($Migration->up()) {
							\MVCFAM\App\message(sprintf('%s migration complete', $ModelName), 'success');
						} else {
							$error_message = App::db()->error();
							\MVCFAM\App\message(sprintf('Error migrating %s', $ModelName).($error_message ? ': '.$error_message : ''), 'error');
						}
					} elseif ($Model->create_table()) {
						\MVCFAM\App\message(sprintf('%s database table created', $ModelName), 'success');
					} else {
						\MVCFAM\App\message(sprintf('Error creating database table for %s', $ModelName), 'error');
					}
				} else {
					\MVCFAM\App\message(sprintf('%s does not have a table defined', $ModelName), 'error');
				}
			} else {
				\MVCFAM\App\message('Base Model class shall not persist', 'error');
			}
		}
		return \MVCFAM\App\redirect($this->home_path);
	}

	/**
	 * {"route": "/admin/model/fields/edit/(:str)", "action": "Model/edit_fields"}
	 */
	public function edit_fields($ModelName) {
		$ModelClass = 'MVCFAM\\App\\Model\\'.$ModelName.'_Model';
		$Model = new $ModelClass;
		$fields = $Model->fields(true);
		$this->Title = sprintf('Edit Fields on %s', $ModelName);
		$this->Breadcrumb('/admin', '<i class="fa fa-angle-double-left"></i> Dashboard');
		$this->Breadcrumb($this->home_path, '<i class="fa fa-angle-left"></i> Models');
		$this->Form = $this->editFieldForm($ModelName, $fields);

		$this->pushContent($this->Form->html);
		$this->pushContent($this->Form->js);
		
		return $this->View();
	}

	/**
	 * Edit the fields and update the physical schema file
	 */
	public function process_edit_fields() {
		if ($POST = $this->post()) {
			if (!isset($POST['Model'])) {
				throw new \Exception('Model field not found');
			}

			$ModelName = $table = $POST['Model'];
			$ModelClass = 'MVCFAM\\App\\Model\\'.$ModelName;
			//$Model = new $ModelClass;

			// process fields schema config
			$fields = [];
			foreach ($POST as $field => $values) {
				if (is_array($values)) {
					foreach ($values as $field_key => $value) {
						if ($field == 'field_type' && $value == 'SERIAL') {
							// @todo: An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE
						}
						if (is_numeric($field_key)) {
							$field_key = intval($field_key);
							if (! isset($fields[$field_key])) {
								$fields[$field_key] = [];
							}
							$fields[$field_key][$field] = $value;
						}
					}
				}
			}
			$config = [ 'name' => $ModelName, 'fields' => $fields ];

			if ($debug = false) {
				print '<pre>$POST: '.print_r($POST,1).'</div>';
				print '<pre>$config: '.print_r($config,1).'</div>';
				die();
			}

			// generate fields schema config file
			try {
				if (Schema::config_write($ModelName, $config)) {
					$config = Schema::config($table);

					// update fields definition
					$filename = $ModelName.'_Model.php';
					$filepath = APP_MODELS.'/'.$filename;
					
					if (file_exists($filepath)) {
						$static_fields_array = ClassWriter::static_fields($fields);

						// overwrite existing definition, insert after first line if not present
						/*
							protected static $fields = [
								'ID' => 'Int',
								'Title' => 'Varchar(255)',
								'Subtitle' => 'Varchar(255)'
							];
						*/
						$file_contents = file_get_contents($filepath);
						$file_contents_array = explode("\n", $file_contents);
						$class_declaration_line_index = 0;
						$has_fields_defined = false;
						$insert_after_index = 0;
						$trim_start_index = 0;
						$trim_end_index = 0;
						$previous_line = '';
						if (false !== strpos($file_contents, 'protected static $fields')) {
							$has_fields_defined = true;
						}

						foreach ($file_contents_array as $line_key => $line) {
							if (! $has_fields_defined) {
								if (false !== strpos($previous_line, 'class '.$ModelName.'_Model')) {
									$insert_after_index = ($line_key - 1);
								}
								$previous_line = $line;
								continue;
							} else {
								if (false !== strpos($line, 'protected static $fields')) {
									$trim_start_index = $line_key;
								}
								if ($trim_start_index > 0 && $trim_end_index == 0) {
									if (false !== strpos($line, '];') || false !== strpos($line, ');')) {
										$trim_end_index = $line_key;
									}
									break;
								}
							}
						}

						$static_fields_array_string = str_replace('array (', 'array(', var_export($static_fields_array, true));
						$new_static_fields_lines = "\t".'protected static $fields = '.preg_replace(['/\s{2}\'/', '/^\);/'], ["\t\t'", "\t\t);"], $static_fields_array_string).";\n";
						$old_static_fields_lines = array_splice($file_contents_array, $trim_start_index, ($trim_end_index - $trim_start_index) + 1, $new_static_fields_lines);

						// Write (overwrite) Model class file
						try {
							if (file_put_contents($filepath, implode("\n", $file_contents_array))) {
								\MVCFAM\App\message(sprintf('%s Fields configuration updated', $ModelName), 'success');

								// Write migration if schema config exists
								/* --Moved to separate method (and process)
								if ($config) {
									$up_method_body = 'return false;';
									$down_method_body = 'return false;';
									if (! empty($fields)) {
										$static_fields_config_string = str_replace('array (', 'array(', var_export($config, true));
										$up_method_body = sprintf("\t\treturn Schema::create('%s', %s);\n", $table, $static_fields_config_string);
										$down_method_body = sprintf("\t\treturn Schema::drop('%s');\n", $table);
									}
									$Writer = Migration::create('Migrations', $ModelName);
									$Writer->addMethod('up', 'public', false, [], $up_method_body, 'Issue a SQL query to CREATE the database table');
									$Writer->addMethod('down', 'public', false, [], $down_method_body, 'Issue a SQL query to DROP the database table');

									if ($migration_written = file_put_contents(Migration::path().'/'.$table.'_Migration.php', $Writer->write())) {
										\MVCFAM\App\message('Migration procedure created', 'success');
										ClassWriter::dump_autoload();
									} else {
										\MVCFAM\App\message('Error writing Migration file', 'error');
									}
								} else {
									\MVCFAM\App\message('Error loading model field configuration file', 'error');
								}
								*/

								return \MVCFAM\App\redirect($this->home_path);
							} else {
								\MVCFAM\App\message('Error writing class fields definition', 'error');
							}
						} catch (\Exception $e) {
							\MVCFAM\App\message($e->getMessage(), 'error');
						}
					} else {
						\MVCFAM\App\message(sprintf('%s class definition file not found', $ModelName), 'warning');
					}

				} else {
					\MVCFAM\App\message('Error writing field configuration', 'error');
				}
			} catch (\Exception $e) {
				\MVCFAM\App\message($e->getMessage(), 'error');
			}
		} else {
			\MVCFAM\App\message('Fields empty', 'error');
		}

		return \MVCFAM\App\redirect($this->Route->uri());
	}

	private function getDbfield($config) {
		return Dbfield::create($config);
	}

	/**
	 * {"route": "post:/admin/model/delete/(:any)", "action" : "Model/delete"}
	 */
	public function delete($ModelName) {
		try {
			if (Model::delete($ModelName)) {
				// DELETE/DROP table?
				\MVCFAM\App\message(sprintf('%s deleted - Database unaffected', $ModelName), 'success');
			} else {
				\MVCFAM\App\message(sprintf('Error deleting %s', $ModelName), 'error');
			}
		} catch (\Exception $e) {
			\MVCFAM\App\message($e->getMessage(), 'error');
		}

		return \MVCFAM\App\redirect($this->home_path);
	}

	/**
	 * {"route": "post:/admin/model/table/drop/(:any)", "action" : "Model/drop_table"}
	 */
	public function drop_table() {
		if($POST = $this->post()) {
			$ModelName = $POST['Model'];
			if ($ModelName != 'Model') {
				$FQNS = '\\MVCFAM\\App\\Model\\'.$ModelName.'_Model';
				$Model = new $FQNS();
				if ($Model->table()) {
					if ($Model->hasMigration()) {
						$MigrationClassName = '\\MVCFAM\\App\\Migrations\\'.$Model->table().'_Migration';
						$Migration = new $MigrationClassName;
						try {
							if ($Migration->down()) {
								\MVCFAM\App\message(sprintf('Dropped %s', $ModelName), 'success');
							} else {
								$error_message = App::db()->error();
								\MVCFAM\App\message(sprintf('Error dropping %s', $ModelName).($error_message ? ': '.$error_message : ''), 'error');
							}
						} catch (\Exception $e) {
							$error_message = $e->getMessage();
							\MVCFAM\App\message(sprintf('Error dropping %s', $ModelName).($error_message ? ': '.$error_message : ''), 'error');
						}
					} else {
						if (Scheme::drop($Model->table())) {
							\MVCFAM\App\message(sprintf('Dropped %s', $ModelName), 'success');
						} else {
							$error_message = App::db()->error();
							\MVCFAM\App\message(sprintf('Error dropping %s', $ModelName).($error_message ? ': '.$error_message : ''), 'error');
						}
					}
				} else {
					\MVCFAM\App\message(sprintf('%s does not have a table defined', $ModelName), 'error');
				}
			} else {
				\MVCFAM\App\message('Base Model class does not require a table', 'error');
			}
		}
		return \MVCFAM\App\redirect($this->home_path);
	}

	/**
	 * Return the collection of fields in HTML for a Model Field
	 */
	protected function FieldFieldset($ModelName, $fields = [], $mode = 'create') {
		/**
		 *
		 * Need to reuse these if possible; need to include ModelField fields in a Model create form
		 *
		 **/
		$html = '';
		if (count($fields) === 0) {
			$html .= '<div id="template_container" style="display:none;">';
			// --------- <DBField:fieldset>----------- //
			$html .= $this->FieldForm_Fieldset($row = '%r', $col = '%c');
			// --------- </DBField:fieldset>----------- //
			$html .= '</div>'; // </div#template_container>
		} else {
			/*
			private static $fields = array(
		        'PlayerNumber' => 'Int',
		        'FirstName' => 'Varchar(255)',
		        'LastName' => 'Text',
		        'Birthday' => 'Date'
		    );
			*/
			$row = 0;
			foreach ($fields as $name => $type) {
				$values = [
					'field_name['.$row.']' => $name,
					'field_default_type['.$row.']' => $type,
					'field_length['.$row.']' => null, // !
					'field_default_value['.$row.']' => null,
					'allow_null['.$row.']' => null,
					'primary_key['.$row.']' => null,
					'auto_increment['.$row.']' => null
				];
				$html .= $this->FieldForm_Fieldset($row, 0, $values);
				$row++;
			}
		}
		
		$html .= '<fieldset class="form_controls">';
		$html .= '<div class="pure-control-group"></div>';
		$html .= '<div class="pure-controls">
			<button type="submit" class="pure-button pure-button-primary">Create</button>
			'.\MVCFAM\App\html_button('Add Field', '#', 'pure-button button-small control', null, 'id="add_row"').'
		</div>';
		$html .= '</fieldset>';

		return $html;
	}

	protected function editFieldForm($ModelName, $fields = []) {
		$mode = (count($fields) ? 'edit' : 'create');
		$js = '';
		$html = '';
		$html .= '<div class="pure-u-2-3">';
		$html .= '<form action="'.\MVCFAM\App\url($this->Route->uri()).'" name="ModelFields:'.$mode.'" id="ModelField_form" method="post" class="pure-form pure-form-aligned">';
		$html .= '<input type="hidden" name="Model" value="'.$ModelName.'" />';

		///////

		$html .= '<div id="template_container" style="display:none;">';
		// --------- <DBField:fieldset>----------- //
		$html .= $this->FieldForm_Fieldset($row = '%r', $col = '%c');
		// --------- </DBField:fieldset>----------- //
		$html .= '</div>'; // </div#template_container>
		
		if (count($fields) > 0) {
			/*
			private static $fields = array(
		        'PlayerNumber' => 'Int',
		        'FirstName' => 'Varchar(255)',
		        'LastName' => 'Text',
		        'Birthday' => 'Date'
		    );
			*/
			$row = 0;
			foreach ($fields as $name => $field) {
				if (is_array($field)) {
					$values = [
						'field_name['.$row.']' => $field['field_name'],
						'field_type['.$row.']' => $field['field_type'],
						'field_length['.$row.']' => $field['field_length'], // !
						'field_default_type['.$row.']' => $field['field_default_type'],
						'field_default_value['.$row.']' => $field['field_default_value'],
						'allow_null['.$row.']' => (isset($field['field_name']) ? !! $field['field_name'] : true),
						'primary_key['.$row.']' => (isset($field['primary_key']) ? !! $field['primary_key'] : false),
						'auto_increment['.$row.']' => (isset($field['auto_increment']) ? ($field['auto_increment'] == 1) : false)
					];
					$html .= $this->FieldForm_Fieldset($row, 0, $values);
					$row++;
				}
			}
		}
		
		$html .= '<fieldset class="form_controls">';
		$html .= '<div class="pure-control-group"></div>';
		$html .= '<div class="pure-controls">
			'.\MVCFAM\App\html_button('Add Field', '#', 'pure-button button-small control', null, 'id="add_row"').'
			<button type="submit" class="pure-button pure-button-primary">'.($mode == 'create' ? 'Create' : 'Update').'</button>
		</div>';
		$html .= '</fieldset>';

		///////

		$html .= '</form>';
		$html .= '</div>';
		$html .= '<div class="pure-u-1-3">';
		/* sidebar..? perhaps... */
		$html .= '</div>';

		$js .= '<script type="text/javascript" src="'.\MVCFAM\App\url('/js/forms.js').'"></script>';

		$Form = new \stdClass;
		$Form->html = $html;
		$Form->js = $js;

		return $Form;
	}

	public function FieldForm_Fieldset($row = '%r', $col = '%c', $values = []) {
		// --------- <DBField:fieldset>----------- //
		$html = '<fieldset class="DBField deletable">';
		
		$html .= '<div class="pure-control-group"><label for="field_'.$row.'_'.$col.'">Name</label><input type="text" name="field_name['.$row.']" id="field_'.$row.'_'.$col.'"'.(isset($values['field_name['.$row.']']) ? ' value="'.\MVCFAM\App\_e($values['field_name['.$row.']']).'"' : '').' /></div>';
		$col++;
		$html .= '<div class="pure-control-group">';
		$html .= '<label>Type</label>';
		$html .= '<select class="column_type" name="field_type['.$row.']" id="field_'.$row.'_'.$col.'">
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'INT' ? ' selected="selected"' : '').' title="A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295">INT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'VARCHAR' ? ' selected="selected"' : '').' title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size">VARCHAR</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TEXT' ? ' selected="selected"' : '').' title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes">TEXT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'DATE' ? ' selected="selected"' : '').' title="A date, supported range is 1000-01-01 to 9999-12-31">DATE</option>
				<optgroup label="Numeric">
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TINYINT' ? ' selected="selected"' : '').' title="A 1-byte integer, signed range is -128 to 127, unsigned range is 0 to 255">TINYINT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'SMALLINT' ? ' selected="selected"' : '').' title="A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535">SMALLINT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'MEDIUMINT' ? ' selected="selected"' : '').' title="A 3-byte integer, signed range is -8,388,608 to 8,388,607, unsigned range is 0 to 16,777,215">MEDIUMINT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'INT' ? ' selected="selected"' : '').' selected="selected" title="A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295">INT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'BIGINT' ? ' selected="selected"' : '').' title="An 8-byte integer, signed range is -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807, unsigned range is 0 to 18,446,744,073,709,551,615">BIGINT</option>
				<option disabled="disabled">-</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'DECIMAL' ? ' selected="selected"' : '').' title="A fixed-point number (M, D) - the maximum number of digits (M) is 65 (default 10), the maximum number of decimals (D) is 30 (default 0)">DECIMAL</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'FLOAT' ? ' selected="selected"' : '').' title="A small floating-point number, allowable values are -3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38">FLOAT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'DOUBLE' ? ' selected="selected"' : '').' title="A double-precision floating-point number, allowable values are -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308">DOUBLE</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'REAL' ? ' selected="selected"' : '').' title="Synonym for DOUBLE (exception: in REAL_AS_FLOAT SQL mode it is a synonym for FLOAT)">REAL</option>
				<option disabled="disabled">-</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'BIT' ? ' selected="selected"' : '').' title="A bit-field type (M), storing M of bits per value (default is 1, maximum is 64)">BIT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'BOOLEAN' ? ' selected="selected"' : '').' title="A synonym for TINYINT(1), a value of zero is considered false, nonzero values are considered true">BOOLEAN</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'DATE' ? ' selected="selected"' : '').' title="An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE">SERIAL</option>
				</optgroup>
				<optgroup label="Date and time">
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'DATE' ? ' selected="selected"' : '').' title="A date, supported range is 1000-01-01 to 9999-12-31">DATE</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'DATETIME' ? ' selected="selected"' : '').' title="A date and time combination, supported range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59">DATETIME</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TIMESTAMP' ? ' selected="selected"' : '').' title="A timestamp, range is 1970-01-01 00:00:01 UTC to 2038-01-09 03:14:07 UTC, stored as the number of seconds since the epoch (1970-01-01 00:00:00 UTC)">TIMESTAMP</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TIME' ? ' selected="selected"' : '').' title="A time, range is -838:59:59 to 838:59:59">TIME</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'YEAR' ? ' selected="selected"' : '').' title="A year in four-digit (4, default) or two-digit (2) format, the allowable values are 70 (1970) to 69 (2069) or 1901 to 2155 and 0000">YEAR</option>
				</optgroup>
				<optgroup label="String">
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'CHAR' ? ' selected="selected"' : '').' title="A fixed-length (0-255, default 1) string that is always right-padded with spaces to the specified length when stored">CHAR</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'VARCHAR' ? ' selected="selected"' : '').' title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size">VARCHAR</option>
				<option disabled="disabled">-</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TINYTEXT' ? ' selected="selected"' : '').' title="A TEXT column with a maximum length of 255 (2^8 - 1) characters, stored with a one-byte prefix indicating the length of the value in bytes">TINYTEXT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TEXT' ? ' selected="selected"' : '').' title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes">TEXT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'MEDIUMTEXT' ? ' selected="selected"' : '').' title="A TEXT column with a maximum length of 16,777,215 (2^24 - 1) characters, stored with a three-byte prefix indicating the length of the value in bytes">MEDIUMTEXT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'LONGTEXT' ? ' selected="selected"' : '').' title="A TEXT column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) characters, stored with a four-byte prefix indicating the length of the value in bytes">LONGTEXT</option>
				<option disabled="disabled">-</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'BINARY' ? ' selected="selected"' : '').' title="Similar to the CHAR type, but stores binary byte strings rather than non-binary character strings">BINARY</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'VARBINARY' ? ' selected="selected"' : '').' title="Similar to the VARCHAR type, but stores binary byte strings rather than non-binary character strings">VARBINARY</option>
				<option disabled="disabled">-</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'TINYBLOB' ? ' selected="selected"' : '').' title="A BLOB column with a maximum length of 255 (2^8 - 1) bytes, stored with a one-byte prefix indicating the length of the value">TINYBLOB</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'MEDIUMBLOB' ? ' selected="selected"' : '').' title="A BLOB column with a maximum length of 16,777,215 (2^24 - 1) bytes, stored with a three-byte prefix indicating the length of the value">MEDIUMBLOB</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'BLOB' ? ' selected="selected"' : '').' title="A BLOB column with a maximum length of 65,535 (2^16 - 1) bytes, stored with a two-byte prefix indicating the length of the value">BLOB</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'LONGBLOB' ? ' selected="selected"' : '').' title="A BLOB column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) bytes, stored with a four-byte prefix indicating the length of the value">LONGBLOB</option>
				<option disabled="disabled">-</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'ENUM' ? ' selected="selected"' : '').' title="An enumeration, chosen from the list of up to 65,535 values or the special \'\' error value">ENUM</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'SET' ? ' selected="selected"' : '').' title="A single value chosen from a set of up to 64 members">SET</option>
				</optgroup>
				<optgroup label="Spatial">
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'GEOMETRY' ? ' selected="selected"' : '').' title="A type that can store a geometry of any type">GEOMETRY</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'POINT' ? ' selected="selected"' : '').' title="A point in 2-dimensional space">POINT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'LINESTRING' ? ' selected="selected"' : '').' title="A curve with linear interpolation between points">LINESTRING</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'POLYGON' ? ' selected="selected"' : '').' title="A polygon">POLYGON</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'MULTIPOINT' ? ' selected="selected"' : '').' title="A collection of points">MULTIPOINT</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'MULTILINESTRING' ? ' selected="selected"' : '').' title="A collection of curves with linear interpolation between points">MULTILINESTRING</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'MULTIPOLYGON' ? ' selected="selected"' : '').' title="A collection of polygons">MULTIPOLYGON</option>
				<option'.(isset($values['field_type['.$row.']']) && $values['field_type['.$row.']'] == 'GEOMETRYCOLLECTION' ? ' selected="selected"' : '').' title="A collection of geometry objects of any type">GEOMETRYCOLLECTION</option>
				</optgroup>
			</select>';
		$html .= '</div>';
		$col++;
		$html .= '<div class="pure-control-group"><label for="field_'.$row.'_'.$col.'">Length</label><input type="text" name="field_length['.$row.']" id="field_'.$row.'_'.$col.'"'.(isset($values['field_length['.$row.']']) ? ' value="'.\MVCFAM\App\_e($values['field_length['.$row.']']).'"' : '').' /></div>';
		$col++;
		/* @todo: Get HTML form fields for datastore field metadata from polymorphic database field classes, eg. $html = DatabaseField::get($type)->setName($name)->html();
			// DatabaseField::formInputs(); or something...
			// loop form inputs to generate HTML
		*/
		$html .= '<div class="pure-control-group default_type">
			<label for="field_'.$row.'_'.$col.'">Default</label>
			<select name="field_default_type['.$row.']" id="field_'.$row.'_'.$col.'">
				<option value="NONE">None</option>
				<option value="USER_DEFINED"'.(isset($values['field_default_type['.$row.']']) && $values['field_default_type['.$row.']'] == 'USER_DEFINED' ? ' selected="selected"' : '').'>As defined:</option>
				<option value="NULL"'.(isset($values['field_default_type['.$row.']']) && $values['field_default_type['.$row.']'] == 'NULL' ? ' selected="selected"' : '').'>NULL</option>
				<option value="CURRENT_TIMESTAMP"'.(isset($values['field_default_type['.$row.']']) && $values['field_default_type['.$row.']'] == 'CURRENT_TIMESTAMP' ? ' selected="selected"' : '').'>CURRENT_TIMESTAMP</option>
			</select>
		</div>';
		$col++;
		$html .= '<div class="pure-control-group as_defined"><label for="field_'.$row.'_'.$col.'"></label><input type="text" name="field_default_value['.$row.']" id="field_'.$row.'_'.$col.'"'.(isset($values['field_default_value['.$row.']']) ? ' value="'.\MVCFAM\App\_e($values['field_default_value['.$row.']']).'"' : '').' placeholder="default value" /></div>';
		$col++;
		$html .= '<div class="pure-control-group allow_null"><label for="field_'.$row.'_'.$col.'">Allow NULL</label><input type="checkbox" name="allow_null['.$row.']" id="field_'.$row.'_'.$col.'" value="1"'.(isset($values['allow_null['.$row.']']) && $values['allow_null['.$row.']'] == true ? ' checked' : '').' /></div>';
		$col++;
		$html .= '<div class="pure-control-group"><label for="field_'.$row.'_'.$col.'">Primary Key</label><input type="radio" name="primary_key['.$row.']" id="field_'.$row.'_'.$col.'" class="primary_key" value="1"'.(isset($values['primary_key']) && $values['primary_key'] == true ? ' checked' : '').' /></div>';
		$col++;
		$html .= '<div class="pure-control-group auto_increment"><label for="field_'.$row.'_'.$col.'">Auto Increment</label><input type="checkbox" name="auto_increment['.$row.']" id="field_'.$row.'_'.$col.'" value="1"'.(isset($values['auto_increment['.$row.']']) && $values['auto_increment['.$row.']'] == true ? ' checked' : '').' /></div>';
		$col++;
		
		$html .= '</fieldset>';
		// --------- </DBField:fieldset>----------- //

		return $html;
	}

	/**
	 * Return a list of models
	 */
	protected function Models() {
		//$Models = [];
		$Models = new \MVCFAM\App\Collection();
		// cache->get('...?', Cache::DIRECTORY_CONTENTS)
		foreach(glob(APP_MODELS.'/*') as $ModelFile) {
			$basename = basename($ModelFile);
			$basename_parts = explode('.', $basename);
			if ($basename_parts[0] == 'Model') {
				$Model = new Model('Model');
			} else {
				$ModelName = '\\MVCFAM\\App\\Model\\'.$basename_parts[0];
				$Model = new $ModelName();
			}
			
			if(in_array(basename($ModelFile), Model::get_protected_models())) {
				// $ViewName = '<span class="'.($Controller->getView()->exists() ? 'alert-success' : 'alert-error').'">'.$Controller->getViewName().'</span>';
				$Model->html = '<span class="Model:protected">'.$Model->name().'</span>';
			} else {
				$Model->html = '<span class="Model:user">'.$Model->name().'</span>';
			}

			//$Models[] = $Model;
			$Models->append($Model);
		}
		return $Models;
	}
}