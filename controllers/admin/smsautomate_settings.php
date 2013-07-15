<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   John Etherton
 * @package	   SMS Automate
 */

class Smsautomate_settings_Controller extends Admin_Controller
{

	public function index()
	{

		$this->template->this_page = 'addons';

		// Standard Settings View
		$this->template->content = new View("admin/addons/plugin_settings");
		$this->template->content->title = "SMS Automate Settings";
		
		// Settings Form View
		$this->template->content->settings_form = new View('smsautomate/smsautomate_admin');
		
		//create the form array
		$form = array
			(
				'delimiter' => "",
				'code_word' => "",
				'whitelist' => "", 
				'auto_title' => "",
				'auto_desc' => "",
				'auto_approve' => "",
				'auto_verify' => "",
				'append_to_desc' => "",
				'append_to_desc_txt' => "",
				'multival_resp_by_id' => "",
				'locate_from_list' => "",
				'fill_empty_gps_by_loc' => "",
				'locate_from_list_table' => "",
				'locate_from_list_id' => "",
				'locate_from_list_name' => "",
				'locate_from_list_lat' => "",
				'locate_from_list_lon' => "",
				'append_errors' => ""
			);

		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;

		// check, has the form been submitted if so check the input values and save them
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST
			// fields with our own things
			$post = new Validation($_POST);

			// Add some filters
			$post->pre_filter('trim', TRUE);

			$post->add_rules('delimiter', 'required','length[1,1]');
			$post->add_rules('code_word', 'length[1,11]');
			$post->add_rules('auto_title','required','between[0,1]');
			$post->add_rules('auto_desc','required','between[0,1]');
			$post->add_rules('auto_approve','required','between[0,1]');
			$post->add_rules('auto_verify','required','between[0,1]');
			$post->add_rules('append_to_desc','required','between[0,1]');
			//$post->add_rules('append_to_desc_txt', 'required','length[1,100]');
			$post->add_rules('multival_resp_by_id','required','between[0,1]');
			$post->add_rules('locate_from_list','required','between[0,1]');
			$post->add_rules('fill_empty_gps_by_loc','required','between[0,1]');
			//$post->add_rules('locate_from_list_table = NULL;
			//$post->add_rules('locate_from_list_id = NULL;
			//$post->add_rules('locate_from_list_name = NULL;
			//$post->add_rules('locate_from_list_lat = NULL;
			//$post->add_rules('locate_from_list_lon = NULL;
			$post->add_rules('append_errors','required','between[0,1]');

			if ($post->validate())
			{

				$settings = ORM::factory('smsautomate',1);
				$settings->delimiter = $post->delimiter;
				$settings->code_word = $post->code_word;
				$settings->auto_title = $post->auto_title;
				$settings->auto_desc = $post->auto_desc;
				$settings->auto_approve = $post->auto_approve;
				$settings->auto_verify = $post->auto_verify;
				$settings->append_to_desc = $post->append_to_desc;
				$settings->append_to_desc_txt = $post->append_to_desc_txt;
				$settings->multival_resp_by_id = $post->multival_resp_by_id;
				$settings->locate_from_list = $post->locate_from_list;
				$settings->fill_empty_gps_by_loc = $post->fill_empty_gps_by_loc;
				$settings->locate_from_list_table = $post->locate_from_list_table;
				$settings->locate_from_list_id = $post->locate_from_list_id;
				$settings->locate_from_list_name = $post->locate_from_list_name;
				$settings->locate_from_list_lat = $post->locate_from_list_lat;
				$settings->locate_from_list_lon = $post->locate_from_list_lon;
				$settings->append_errors = $post->append_errors;

				$settings->save();

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

			// No! We have validation errors, we need to show the form again,
			// with the errors
			else
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('settings'));
				$form_error = TRUE;
			}
		}
		else
		{
			//get settings from the database
			$form = ORM::factory('smsautomate',1)->as_array();

			//get the white listed numbers
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

		$this->template->content->settings_form->yesno_array = array(
			'1'=>utf8::ucfirst(Kohana::lang('ui_main.yes')),
			'0'=>utf8::ucfirst(Kohana::lang('ui_main.no')));

		// Other variables
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;

	}//end index method


	public function _strtobool($str)
	{
		return ($str == '1') ? true : false;
	}

	// Database::table_exists($table) returns TRUE or FALSE depending on whether the specified table exists in the database.
	// Database::list_fields($table) returns an array of the fields (columns) in the specified tabl

}
