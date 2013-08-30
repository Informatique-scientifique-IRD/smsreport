<?php
/**-----------------------------------------------------------------------------
 *
 * Andministration controller
 * 
 * File :         controller/admin/smsreport.php
 * Project :      SMS Report
 * Last Modified :ven. 30 août 2013 16:29:21 CEST
 * Created :      juillet 2013
 *
 * Original Copyright :
 *  This project was originally forked from SMS Automate by John Etherton,
      available at https://github.com/jetherton/smsautomate
 *
 * Author :       G.F.
 * Organization : IRD - UMR GRED
 * Copyright :    IRD - UMR GRED, 2013
 * Licence :      LGPL
 * 
 *  You should have received a copy of the GNU Lesser General Public License
 *   along with SMS Report. 
 *   If not, see <http://www.gnu.org/licenses/>.
 *
 *----------------------------------------------------------------------------*/

/**
 * Admin pannel class
 */
class Smsautomate_settings_Controller extends Admin_Controller
{

	/**
	 * Display and populates the settings page, process new settings if needed
	 */
	public function index()
	{

		// Belongs to addons
		$this->template->this_page = 'addons';

		// Standard Settings View
		$this->template->content = new View("admin/addons/plugin_settings");
		$this->template->content->title = "SMS Report Settings";
		
		// Settings Form View
		$this->template->content->settings_form = new View('smsautomate/smsautomate_admin');
		
		// Create the form array
		$form = array(
				'delimiter' => "",
				'code_word' => "",
				'whitelist' => "", 
				'auto_title' => "",
				'auto_desc' => "",
				'auto_date' => "",
				'auto_approve' => "",
				'auto_verify' => "",
				'append_to_desc' => "",
				'append_to_desc_txt' => "",
				'multival_resp_by_id' => "",
				'locate_from_list' => "",
				'fill_empty_gps_by_loc' => "",
				'locate_from_list_id' => "",
				'loc_code_field' => "",
				'locate_from_list_name' => "",
				'locate_from_list_lat' => "",
				'locate_from_list_lon' => "",
				'coord_type_field' =>"",
				'append_errors' => ""
			);

		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;

		// We get the location table
		$loc_table = Smsreport_Controller::get_loc_table();


		/*----------------------------------------------------------------------------
		 * Process posted settings
		 *--------------------------------------------------------------------------*/
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST
			// fields with our own things
			$post = new Validation($_POST);

			// Add some filters
			$post->pre_filter('trim', TRUE);

			// Yes/No answers
			$post->add_rules('auto_title','required','between[0,1]');
			$post->add_rules('auto_desc','required','between[0,1]');
			$post->add_rules('auto_date','required','between[0,1]');
			$post->add_rules('auto_approve','required','between[0,1]');
			$post->add_rules('auto_verify','required','between[0,1]');
			$post->add_rules('append_to_desc','required','between[0,1]');
			$post->add_rules('multival_resp_by_id','required','between[0,1]');
			$post->add_rules('append_errors','required','between[0,1]');

			// Other rules
			$post->add_rules('delimiter', 'required','length[1,1]');
			$post->add_rules('code_word', 'length[1,11]');

			// Verifications for location table if it's valid
			if($loc_table !== false)
			{
				// Only check when it's enabled
				$post->add_rules('locate_from_list', 'required', 'between[0,1]');
				$post->add_rules('fill_empty_gps_by_loc', 'required', 'between[0,1]');
				$post->add_callbacks('loc_code_field', array($this, '_valid_field'));
				$post->add_callbacks('coord_type_field', array($this, '_valid_field'));

				// If locate from list enabled
				if( isset($post['locate_from_list']) and $post['locate_from_list'] == '1')
				{
					// Check if columns are valid
					$post->add_callbacks('locate_from_list_id', array($this, '_valid_loc_column'));
					$post->add_callbacks('locate_from_list_name', array($this, '_valid_loc_column'));

					// And if fill_empty gps enabled
					if ( isset($post['fill_empty_gps_by_loc']) 
						 and $post['fill_empty_gps_by_loc'] == '1' )
					{
						// Check if columns are valid
						$post->add_callbacks('locate_from_list_lat', array($this, '_valid_loc_column'));
						$post->add_callbacks('locate_from_list_lon', array($this, '_valid_loc_column'));
					} else {
						// Empty fields
						$post['locate_from_list_lat'] = '';
						$post['locate_from_list_lon'] = '';
						$post['coord_type_field'] = '';
					}
				} else {
					// Empty fields
					$post['locate_from_list_id'] = '';
					$post['locate_from_list_name'] = '';
					$post['loc_code_field'] = '';
					$post['locate_from_list_lat'] = '';
					$post['locate_from_list_lon'] = '';
					$post['coord_type_field'] = '';

					// Can't be active without locate_from_list
					$post['fill_empty_gps_by_loc'] = 0;
				}
			} else {
				// Can't be active without a valid table
				$post['locate_from_list'] = 0;
				$post['fill_empty_gps_by_loc'] = 0;
			}


			if ($post->validate())
			{

				ORM::factory('smsautomate')->save_all($post);

				$form_saved = TRUE;

				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				//do the white list

				//delete everything in the white list db to make room for the new ones
				ORM::factory('smsautomate_whitelist')->delete_all();

				$whitelist = nl2br(trim($post->whitelist));
				if($whitelist != "" && $whitelist != null)
				{
					$whitelist_array = explode("<br />", $whitelist);
					//now put back the new ones
					foreach($whitelist_array as $item)
					{
						$whitelist_item = ORM::factory('smsautomate_whitelist');
						$whitelist_item->phone_number = trim($item);
						$whitelist_item->save();
					}
				}
			}
			/*------------------------------------------------------------------
			 * No! We have validation errors, we need to show the form again,
			 * with the errors
			 *----------------------------------------------------------------*/
			else
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('settings'));
				$form_error = TRUE;
			}
		}
		/*----------------------------------------------------------------------
		 * Populate forms with saved values
		 *--------------------------------------------------------------------*/
		else
		{

			// Get settings from the database
			$form = ORM::factory('smsautomate')->get_array();

			// Get the white listed numbers
			$whitelist = "";
			$count = 0;
			$listers = ORM::factory('smsautomate_whitelist')->find_all();
			foreach($listers as $item)
			{
				$count++;
				if($count > 1)
				{
					$whitelist = $whitelist."\n";
				}
				$whitelist = $whitelist.$item->phone_number;
			}
			$form['whitelist'] = $whitelist;

		}
		
		// Pass the $form on to the settings_form variable in the view
		$this->template->content->settings_form->form = $form;

		// Yes-no dropdowns
		$this->template->content->settings_form->yesno_array = array(
			'1'=>utf8::ucfirst(Kohana::lang('ui_main.yes')),
			'0'=>utf8::ucfirst(Kohana::lang('ui_main.no')));

		// Disable location table fields if the table isn't valid
		$loc_table_disabled = ($loc_table === false) ? 'disabled' : '';
		$this->template->content->settings_form->loc_table_dis = $loc_table_disabled;

		// Populates location table columns dropdowns
		$col_dropdown = array();
		if($loc_table !== false) 
		{
			$columns = $this->_get_loc_columns();
			
			// Prepare for dropdown
			$col_dropdown = array_combine($columns, $columns);
		}
		$this->template->content->settings_form->columns_dropdown = $col_dropdown;

		// Examples according to settings
		$this->template->content->settings_form->example_format = htmlentities($this->_get_format($form, 'FORMAT'));
		$this->template->content->settings_form->example_sms = htmlentities($this->_get_format($form, 'EX_VALUES'));
		$this->template->content->settings_form->example_txt = htmlentities($this->_get_format($form, 'EX_TEXT'));

		// Other variables
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;

	}//end index method



/*==============================================================================
 * Validation callbacks
 =============================================================================*/

	/**
	 * Check if a column selected for the location.table features really exists
	 * Validation Callback
	 */
	public function _valid_loc_column(Validation $array, $field)
	{
		$table = Smsreport_Controller::get_loc_table();

		// We can't check anything if we don't have the table
	   	if($table !== false)
		{
			$cols = $this->_get_loc_columns();

			if( ! in_array($array[$field], $cols, true))
			{
				$array->add_error($field, 'column_not_found', array($array[$field]));
			}

		} else {
			$array->add_error($field, 'table_must_be_valid', array($array[$field]));
		}
	}


	/**
	 * Check if a field name exists at least once
	 * Validation Callback
	 */
	public function _valid_field(Validation $array, $field)
	{
		// Option disabled
		if($array[$field] == '' OR $array[$field] === NULL)
			return;

		$custom_fields = customforms::get_custom_form_fields();


		$exist = false;

		foreach($custom_fields as $cfield)
		{
			if($cfield['field_name'] == $array[$field])
			{
				$exist = true;
				break;
			}
		}

		if($exist == false)
		{
			$array->add_error($field, 'field_not_found', array($array[$field]));
		}
	}



/*==============================================================================
 * Private functions
 =============================================================================*/

	/**
	 * Returns columns of location.table as an array
	 *
	 * Used for displaying a dropdown of columns to administrator
	 */
	private function _get_loc_columns()
	{
		$array = array();
		$table = Smsreport_Controller::get_loc_table();

		// Only gets columns when location.table is valid
		if( $table !== false )
		{
			$db = Database::instance();
			$array = array_keys($db->list_fields($table));
		}

		return $array;
	}


	/**
	 * Returns the SMS format expected according to settings
	 *
	 * @settings : The settings array
	 * @example_mode : Can either be 'FORMAT', 'EX_VALUES', or 'EX_TEXT'
	 *	'FORMAT' : Gives the current expected SMS format
	 *	'EX_VALUES' : Gives an example of expected SMS
	 *	'EX_TEXT' : Gives a description of that example
	 */
	private function _get_format($settings, $mode = 'FORMAT')
	{
		// SMS Elements as Element => example value
		$example = array(
			'Code' => $settings['code_word'],
			'Title' => 'My new report',
			'Categories' => '1,3',
			'Date' => '20130901 1337',
			'Description' => 'Something amazing happened to me here',
			'Location' => 'Montpellier',
			'Latitude' => '43.6089',
			'Longitude' => '3.8803',
			'Form ID' => '4',
			'Field 1' => 'Beer',
			'Field 2' => 'Case0,Case1,Case6',
			'etc...' => '...');

		// Special items for text description
		$text = array(
			'Title' => $example['Title'],
			'Categories' => '1 and 3',
			'Date' => '2013-09-01 13:37:00',
			'Description' => '"'.$example['Description'].'"',
			'Location' => $example['Location'],
			'Field 2' => $example['Field 2'],
		);

		
		// The current Date
		$cur_date = date('Y-m-d H:i:s');


		// ------
		// Change values according to current settings
		// ------


		// Replace multi-value by ids
		if($settings['multival_resp_by_id'])
		{
			$example['Field 2'] = '0,1,6';
		}

		// Put an arbitrary id
		if($settings['locate_from_list'])
		{
			$example['Location'] = '42';
			$text['Location'] .= ' (42 is this location code)';
		}

		// No title when auto_title
		if ($settings['auto_title'])
		{
			unset($example['Title']);
			$text['Title'] = 'SMS-Report by xxx-xxx-xxxx received on '.$cur_date;
		}

		// No date when auto date
		if ($settings['auto_date'])
		{
			unset($example['Date']);
			$text['Date'] = $cur_date.' (current date)';
		}

		// No description when auto description
		if ($settings['auto_desc'])
		{
			unset($example['Description']);
			$text['Description'] = 'a generated text';
		}

		// ---
		// Return values
		// ---
		switch($mode)
		{
			case 'EX_VALUES':
				$ret = implode(' '.$settings['delimiter'].' ', $example);
				break;
			case 'EX_TEXT':
				$ret = <<<TXT
This would be converted into a report titled "{$text['Title']}"
 with categories {$text['Categories']}
 and dated on {$text['Date']}.
 This report will have {$text['Description']} as description
 and will be located at latitude {$example['Latitude']}
 and longitude {$example['Longitude']},
 which is called {$text['Location']}.
 It answers to the form {$example['Form ID']}
 with "{$example['Field 1']}" for the first field,
 "{$text['Field 2']}" for the second (a checkbox here) and so on...
TXT;
				break;
			case 'FORMAT':
			default:
				$ret = implode(' '.$settings['delimiter'].' ',  array_keys($example));
				break;
		}

		return $ret;
	}
}
		

