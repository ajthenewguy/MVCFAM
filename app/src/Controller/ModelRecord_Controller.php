<?php namespace MVCFAM\App\Controller;
/**
 * ModelRecord Controller
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

class ModelRecord_Controller extends Admin_Controller {

	protected $home_path = '/admin/model/records/';

	protected $delete_path = '/admin/model/record/delete/';

	protected $edit_path = '/admin/model/record/edit/';

	protected $models_path = '/admin/models';

	/**
	 * {"route": "get:/admin/model/records/(:str)", "action": "ModelRecord/records"}
	 */
	public function records($ModelName) {
		$this->Title = sprintf(' %s Records', $ModelName);
		$this->Breadcrumb('/admin', Html\Icon::create('angle-double-left').' Dashboard');
		$this->Breadcrumb($this->models_path, Html\Icon::create('angle-left').' Models');
		$table_headers = [];
		$Controls = $jQuery_handlers = [];
		$Controls[] = new Model('AddNewRecordButton', [ 'url' => \MVCFAM\App\url(sprintf('/admin/model/record/create/%s', $ModelName)), 'text' => 'New Record' ]);

		// List all records for $ModelName
		$records = $this->ModelRecords($ModelName);
		foreach ($records as $record) {
			foreach ($record as $field => $value) {
				if (! in_array($field, $table_headers)) {
					$table_headers[] = $field;
				}
			}
		}
		$table_html = '<div class="pure-u-1 "><div class="table-responsive"><table class="mq-table pure-table" width="100%">
		    <thead>
		        <tr>
		';
		foreach ($table_headers as $key => $value) {
			if (strlen($value) > 250) {
				$value = trim(substr($value, 0, 248)).'&hellip;';
			}
			$table_html .= '<th>'.$value.'</th>'."\n";
		}
		$table_html .= '
					<th>&nbsp;</th>
		        </tr>
		    </thead>
		    <tbody>';
		$count = 0;
		foreach ($records as $record) {
			$count++;
			$Model = $this->getModel($ModelName, $record);
			$actions = [];
			$table_html .= '<tr'.($count % 2 == 0 ? ' class="pure-table-odd"' : '').'>';
			foreach ($record as $field => $value) {
				#if ($field['auto_increment'] !== true && $field['field_default_type'] !== 'CURRENT_TIMESTAMP') {
				/*
				$value = Array ( [ID] => 1 [Description] => Test Model CRUD for other Models [Completed] => 0 [CompletedDatetime] => [CreatedTimestamp] => 2016-03-05 22:53:26 )
				*/
				if (stristr($field, 'date')) {
					if ($strtotime = strtotime($value)) {
						$value = date("Y-m-d @ g:i A", $strtotime);
						if (! stristr($field, 'time')) {
							$value = date("Y-m-d", $strtotime);
						}
					}
				}
				if (strlen($value) > 150) {
					$value = trim(substr($value, 0, 148)).'&hellip;';
				}
				$table_html .= '<td>'.$value.'</td>'."\n";
				#}
			}
			$edit_link = \MVCFAM\App\html_button('Edit', \MVCFAM\App\url($this->edit_path.$Model->name().'/'.$Model->ID), 'pure-button button-xsmall button-secondary');
			$actions[] = $edit_link;
			$delete_link = '<form class="pure-form" action="'.\MVCFAM\App\url($this->delete_path.$Model->name()).'" method="post" ';
			$delete_link .= sprintf('onsubmit="return confirm(\'Delete this %s permanently?\')" ', $Model->name());
			$delete_link .= 'style="display:inline-block;margin:0 auto;">
				<input type="hidden" name="ID" value="'.$Model->ID.'" />'
				.\MVCFAM\App\html_button('Delete', false, 'pure-button button-xsmall button-error', 'submit').
				'</form>';
			$actions[] = $delete_link;

			$table_html .= '<td>'.implode(' &nbsp;', $actions).'</td>';
			$table_html .= '</tr>';
		}

		$table_html .= '</tbody></table></div></div>';

		$this->pushContent($table_html);
		
		return $this->View([ 'Controls' => $Controls ]);
	}

	/**
	 * {"route": "get:/admin/model/record/create/(:str)", "action": "ModelRecord/create"}
	 */
	public function create($ModelName) {
		$this->Title = sprintf('Create New %s', $ModelName);
		$this->Breadcrumb('/admin', Html\Icon::create('angle-double-left').' Dash...');
		$this->Breadcrumb($this->models_path, Html\Icon::create('angle-double-left').' Models');
		$this->Breadcrumb($this->home_path.$ModelName, Html\Icon::create('angle-left').' '.$ModelName.' Records');
		$Model = $this->getModel($ModelName);
		$this->Form = $this->RecordForm($ModelName);

		$this->pushContent($this->Form->html);
		$this->pushContent($this->Form->js);

		return $this->View();
	}

	/**
	 * {"route": "post:/admin/model/record/create/(:str)", "action": "ModelRecord/process_create"}
	 */
	public function process_create() {
		if ($POST = $this->post()) {
			if (! isset($POST['table'])) {
				throw new \Exception('Model/Table field not found');
			}
			$ModelName = $table = $POST['table'];
			$Model = $this->getModel($ModelName);
			$fields = $Model->fields(true);
			$data = [];

			// validate post field data
			foreach ($POST as $field_name => $value) {
				foreach ($fields as $_field_name => $config) {
					if ($field_name == $_field_name) {
						$data[$field_name] = (strlen($value) > 0 ? $value : null);
					}
				}
			}
			if (count($data) > 0) {
				try {
					if ($ID = $Model->insert($data)) {
						\MVCFAM\App\message(sprintf('Record %d created', $ID), 'success');
					} else {
						$error_message = App::db()->error();
						\MVCFAM\App\message('Error creating new record'.($error_message ? ': '.$error_message : ''), 'error');
					}
				} catch (\Exception $e) {
					\MVCFAM\App\message(sprintf('Error creating new record: %s', $e->getMessage()), 'error');
					return \MVCFAM\App\redirect($this->Route->uri());
				}
			} else {
				\MVCFAM\App\message('Error creating new record, no data supplied', 'error');
			}
		} else {
			\MVCFAM\App\message('Error creating new record, no data supplied', 'error');
		}
		return \MVCFAM\App\redirect($this->home_path.$ModelName);
	}

	/**
	 * {"route": "get:/admin/model/record/edit/(:str)/(:num)", "action": "ModelRecord/edit"}
	 */
	public function edit($ModelName, $id) {
		$this->Title = sprintf('Edit %s %d', $ModelName, $id);
		$this->Breadcrumb('/admin', Html\Icon::create('angle-double-left').' Dash...');
		$this->Breadcrumb($this->models_path, Html\Icon::create('angle-double-left').' Models');
		$this->Breadcrumb($this->home_path.$ModelName, Html\Icon::create('angle-left').' '.$ModelName.' Records');
		$this->Form = $this->RecordForm($ModelName, $id);

		$this->pushContent($this->Form->html);
		$this->pushContent($this->Form->js);

		return $this->View();
	}

	/**
	 * {"route": "post:/admin/model/record/edit/(:str)/(:num)", "action": "ModelRecord/process_edit"}
	 */
	public function process_edit($ModelName, $id) {
		if ($POST = $this->post()) {
			if (! isset($POST['table'])) {
				throw new \Exception('Model/Table field not found');
			}
			$ModelName = $table = $POST['table'];
			$ID = $POST['ID'];
			$Model = $this->getModel($ModelName, [ 'ID' => $ID ]);
			$fields = $Model->fields(true);
			$data = [];

			// validate post field data
			foreach ($fields as $_field_name => $config) {
				if ($config['field_type'] == 'BOOLEAN' || $config['field_type'] == 'BIT') {
					$value = (!!$value ? '1' : '0');
					$data[$_field_name] = $value;
				}

				foreach ($POST as $field_name => $value) {
					/*if ($_field_name == 'Completed') {
						print_r($config);
						die();
					}
					print_r($config);
					Array ( [field_name] => ID [field_type] => INT [field_length] => 10 [field_default_type] => NONE [field_default_value] => [primary_key] => 1 [auto_increment] => 1 )
					Array ( [field_name] => Completed [field_type] => BOOLEAN [field_length] => [field_default_type] => USER_DEFINED [field_default_value] => 0 )
					die();
					*/
					if (in_array($config['field_type'], [ 'DATE', 'DATETIME', 'TIMESTAMP' ])) {
						$value = date("Y-m-d H:i:s", strtotime($value));
						if ($config['field_type'] == 'DATE') {
							$value = substr($value, 0, 10);
						}
					}
					if ($field_name == $_field_name) {
						$data[$field_name] = (strlen($value) > 0 ? $value : null);
					}
				}
			}

			if (count($data) > 0) {
				try {
					if ($Model->update($data)) {
						\MVCFAM\App\message(sprintf('Record %d updated', $id), 'success');
					} else {
						$error_message = App::db()->error();
						\MVCFAM\App\message('Error updating record'.($error_message ? ': '.$error_message : ''), 'error');
					}
				} catch (\Exception $e) {
					\MVCFAM\App\message(sprintf('Error updating record: %s', $e->getMessage()), 'error');
					return \MVCFAM\App\redirect($this->Route->uri());
				}
			} else {
				\MVCFAM\App\message('Error updating record, no data supplied', 'error');
			}
		} else {
			\MVCFAM\App\message('Error updating record, no data supplied', 'error');
		}
		return \MVCFAM\App\redirect($this->home_path.$ModelName);
	}

	/**
	 * Delete a record
	 */
	public function delete($ModelName) {
		if ($POST = $this->post()) {
			if (isset($POST['ID'])) {
				if ($Model = $this->getModel($ModelName, [ 'ID' => $POST['ID'] ])) {
					if ($Model->delete()) {
						\MVCFAM\App\message(sprintf('Record %d deleted', $POST['ID']), 'success');
					} else {
						$error_message = App::db()->error();
						\MVCFAM\App\message('Error deleting record'.($error_message ? ': '.$error_message : ''), 'error');
					}
				} else {
					$error_message = App::db()->error();
					\MVCFAM\App\message('Error finding record'.($error_message ? ': '.$error_message : ''), 'error');
				}
			}
		}
		return \MVCFAM\App\redirect($this->home_path.$ModelName);
	}

	/**
	 * Return records for this model
	 */
	protected function ModelRecords($ModelName) {
		$Model = $this->getModel($ModelName);
		$fields = $Model->fields(true);
		$rows = [];
		$where = '';
		$bind = '';

		if ($result = App::db()->select($ModelName, $where, $bind)) {
			foreach ($result as $key => $record) {
				$row = [];
				foreach ($record as $field => $value) {
					$config = [];
					foreach ($fields as $_field_name => $_config) {
						if ($field == $_field_name) {
							$config = $_config;
							break;
						}
					}
					if (strtoupper($config['field_type']) == 'BOOLEAN') {
						$value = ($value === '1' ? 'Yes' : 'No');
					} else {
						$value = (is_int($value) ? sprintf('%d', $value) : $value);
					}
					
					$row[$field] = $value;
				}
				$rows[] = $row;
			}
		}
		return $rows;
	}

	protected function RecordForm($ModelName, $id = null) {
		$mode = (is_null($id) ? 'create' : 'edit');
		if (is_null($id)) {
			$Model = $this->getModel($ModelName);
		} else {
			$Model = $this->getModel($ModelName, [ 'ID' => $id ]);
		}
		
		$fields = $Model->fields(true);
		$js = '';
		$html = '';
		$html .= '<div class="pure-u-2-3">';
		$html .= '<form action="'.\MVCFAM\App\url($this->Route->uri()).'" name="'.$ModelName.':'.$mode.'" id="'.$ModelName.'_form" method="post" class="pure-form pure-form-aligned">';
		if ($ModelTable = $Model->table()) {
			$html .= '<input type="hidden" name="table" value="'.$ModelTable.'" />';
		}
		if (! is_null($id)) {
			$html .= '<input type="hidden" name="ID" value="'.$id.'" />';
		}
		
		///////
		
		if (count($fields) > 0) {
			// --------- <DBField:fieldset>----------- //
			$html .= '<fieldset class="DBField">';

			foreach ($fields as $name => $field) {
				$value = $Model->{$name};
				$config = [
					'field_name' => $field['field_name'],
					'field_type' => $field['field_type'],
					'field_length' => $field['field_length'], // !
					'field_default_type' => $field['field_default_type'],
					'field_default_value' => $field['field_default_value'],
					'allow_null' => (isset($field['field_name']) ? !! $field['field_name'] : true),
					'primary_key' => (isset($field['primary_key']) ? !! $field['primary_key'] : false),
					'auto_increment' => (isset($field['auto_increment']) ? ($field['auto_increment'] == 1) : false)
				];
				$html .= $this->FieldForm_Fieldset($config, $value);
			}

			$html .= '</fieldset>';
			// --------- </DBField:fieldset>----------- //
		}
		
		$html .= '<fieldset class="form_controls">';
		$html .= '<div class="pure-control-group"></div>';
		$html .= '<div class="pure-controls">
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

	/**
	 * Return a form field HTML based on the supplied configuration
	 * @param array $config
	 * @param mixed $value
	 * @return string
	 */
	public function FieldForm_Fieldset($config = [], $value = null) {
		$html = '';
		if ($config['auto_increment'] !== true && $config['field_default_type'] !== 'CURRENT_TIMESTAMP') {
			switch ($config['field_type']) {
				case 'TINYINT':
				case 'SMALLINT':
				case 'MEDIUMINT':
				case 'INT':
				case 'BIGINT':
				case 'DECIMAL':
				case 'FLOAT':
				case 'DOUBLE':
				case 'REAL':
				case 'CHAR':
				case 'VARCHAR':
					$html .= '<div class="pure-control-group"><label for="'.$config['field_name'].'">'.$config['field_name'].'</label><input type="text" name="'.$config['field_name'].'" id="'.$config['field_name'].'"'.(! is_null($value) ? ' value="'.\MVCFAM\App\_e($value).'"' : '').' /></div>';
					break;
				case 'BIT':
				case 'BOOLEAN':
					$html .= '<div class="pure-control-group"><label for="'.$config['field_name'].'">'.$config['field_name'].'</label><input type="checkbox" name="'.$config['field_name'].'" id="'.$config['field_name'].'" value="1"'.(! is_null($value) && $value == true ? ' checked' : '').' /></div>';
					break;
				case 'DATE':
				case 'DATETIME':
				case 'TIMESTAMP':
					$html .= '<div class="pure-control-group"><label for="'.$config['field_name'].'">'.$config['field_name'].'</label><input type="text" name="'.$config['field_name'].'" id="'.$config['field_name'].'" class="datepicker"'.(! is_null($value) ? ' value="'.\MVCFAM\App\_e($value).'"' : '').' /></div>';
					break;
				case 'TIME':
				case 'YEAR':
				case 'TINYTEXT':
				case 'TEXT':
				case 'MEDIUMTEXT':
				case 'LONGTEXT':
				case 'TINYBLOB':
				case 'MEDIUMBLOB':
				case 'BLOB':
				case 'LONGBLOB':
					$html .= '<div class="pure-control-group"><label for="'.$config['field_name'].'">'.$config['field_name'].'</label><textarea name="'.$config['field_name'].'" id="'.$config['field_name'].'" rows="5" cols="60">'.(! is_null($value) ? \MVCFAM\App\_e($value) : '').'</textarea></div>';
					break;
				case 'BINARY':
				case 'VARBINARY':
				
				case 'ENUM':
				case 'SET':
					$options = explode(',', $config['field_default_value']);
					$html .= '<div class="pure-control-group default_type">
						<label for="'.$config['field_name'].'">Default</label>
						<select name="'.$config['field_name'].'" id="'.$config['field_name'].'">';
							foreach ($options as $_value) {
								$html .= '<option value="'.$_value.'"'.(! is_null($_value) && $value == $_value ? ' selected="selected"' : '').'>'.$_value.'</option>';
							}
					$html .= '</select>
					</div>';
				case 'GEOMETRY':
				case 'POINT':
				case 'LINESTRING':
				case 'POLYGON':
				case 'MULTIPOINT':
				case 'MULTILINESTRING':
				case 'MULTIPOLYGON':
				case 'GEOMETRYCOLLECTION':
					break;
			}
		}
		return $html;
	}

	/**
	 * Return a new Model instance
	 * @return Model
	 */
	protected function getModel($ModelName, $data = array()) {
		$ModelClass = 'MVCFAM\\App\\Model\\'.$ModelName.'_Model';
		return new $ModelClass($data);
	}
}