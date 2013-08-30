<?php defined('SYSPATH') or die('No direct script access.');
/**
 * smsautomate Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class smsautomate {

	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{

		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));

		$this->settings = ORM::factory('smsautomate')->get_array();
		$this->white_table = 'smsautomate_whitelist';
		$this->event = NULL;
	}


	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		Event::add('ushahidi_action.message_sms_add',
					array($this, '_parse_sms'));
	}


	/**
	 * Check the SMS message and parse it
	 */
	public function _parse_sms()
	{
		//The message
		$sms['m'] = Event::$data->message;
		$sms['from'] = Event::$data->message_from;
		$sms['reporterId'] = Event::$data->reporter_id;
		$sms['date'] = Event::$data->message_date;
		



		// We store a reference of the Event for updating it later
		$this->event = &Event::$data;


		$settings = $this->settings;

	
		// Informations to fill in the report
		$post = array(
			'incident_title' => '',
			'incident_description' => '',
			'incident_date' => '',
			'incident_hour' => '',
			'incident_minute' => '',
			'incident_ampm' => '',
			'latitude' => '',
			'longitude' => '',
			'location_name' => '',
			'incident_category' => array(),
			'form_id' => 1,  // Default form
			'custom_field' => array(),
			'service_id' => 1  // Mode : sms
		);

		// split up the string using the delimiter
		$m_elements = explode($settings['delimiter'], $sms['m']);
		$elements_count = count($m_elements);
		$min_elements = $this->get_min_elements();

		// APPROVE / VERIFY 
		$post['incident_active'] = ($settings['auto_approve']) ? 1 : 0;
		$post['incident_verified'] = ($settings['auto_verify']) ? 1 : 0;

		// location table & special fields
		$loc_table = Smsreport_Controller::get_loc_table();
		$special = array(
			'loc_id' => NULL,
			'gps_replaced' => 0,
		);

		// === START PROCESSING ===

		// 0a : CODE WORD
		//		--> If nothing, exit without error : this SMS is not for us
		if( strtoupper($m_elements[0]) 
			!= strtoupper($settings['code_word']) )
		{
			// Nothing to say, maybe this sms is not for automatic processing
			return;
		}

		// 0b : WHITELIST
		if ( ! $this->is_whitelisted($sms['from']))
		{
			$this->p_error( 'Whitelist', 
			'The number '.$sms['from'].' is not whitelisted');
			return;
		}

		// 0c : VALID
		//		--> Check if enough informations are provided
		if( $elements_count < $min_elements) 
		{
			$this->p_error( 'Valid',
				'Not enough arguments.'.
			    'A valid report requires '.$min_elements.' elements '.
			    'and '.$elements_count.' elements were provided');
			return;
		}


		// == PRE-PROCESSING ==

		foreach ($m_elements as $element)
		{
			$element = trim($element);
		}

		// == START PARSING ==

		reset($m_elements);


		// 1 : TITLE
		$title ='';
		if ( $settings['auto_title'])
		{
			$title = 'SMS-Report by '.$sms['from'].' received on '.$sms['date'];
		} else {
			$title = next($m_elements);
		}
		$post['incident_title'] = $title;


		// 2 : CATEGORIES
		$post['incident_category'] = explode(",", next($m_elements) );
		foreach($post['incident_category'] as $cat)
		{
			if( ! ORM::factory('category')->is_valid_category($cat))
			{
				$this->p_error( 'Invalid Category',
					'The category : '.$cat.' doesn\'t exist');
				return;
			}
		}

		// 3 : DATE
		$date = new DateTime;
		if ( $settings['auto_date'])
		{
			// We get date from the sms
			$date = $date->createFromFormat('Y-m-d H:i:s', $sms['date']);
		} else {
			// Date must be in yyyymmdd hhii inside SMS
			$date = $date->createFromFormat('Ymd Hi', next($m_elements) );
			if ( ! $date)
			{
				$this->p_error( 'Invalid Date',
					'Unable to interpret : '.current($m_elements).
					'Date must be in format yyyymmdd hhii, '.
				    'like 19700314 1337 for 14th March 1970 at 13h37');
				return;
			}
		}
		$post['incident_date'] = $date->format('m/d/Y'); // mm/dd/yyyy
		$post['incident_hour'] = $date->format('h');
		$post['incident_minute'] = $date->format('i');
		$post['incident_ampm'] = $date->format('a');
		unset($date);


		// 4 : DESCRIPTION
		$desc = "";
		if ( $settings['auto_desc'] )
		{
			$desc = 'Report created from SMS.'."\n\n".
					'From : '.$sms['from']."\n".
					'Date : '.$sms['date']."\n".
					'Message :'."\n".$sms['m'];
		} else {
			$desc = next($m_elements);
		}

		if ($settings['append_to_desc'])
		{
			$desc .= "\n\n" . $settings['append_to_desc_txt'];
		}

		$post['incident_description'] = $desc;


		// 5 : LOCATION
		$location = '';

		//We also retrieve data for Latitude/Longitude if needed
		$location_lat = '';
		$location_lon = '';

		// We need this result for lat/lon
		$loc_result = NULL;

		if ( ($loc_table !== false ) AND $settings['locate_from_list'])
		{
			$col_id = $settings['locate_from_list_id'];
			$col_name = $settings['locate_from_list_name'];
			$col_lat = $settings['locate_from_list_lat'];
			$col_lon = $settings['locate_from_list_lon'];

			$loc_id = next($m_elements);

			$loc_result = ORM::factory('smsreport_location')
							  ->where($col_id, $loc_id)
							  ->find();

			if ($loc_result->loaded )
			{
				// Latitude/Longitude of location if useful
				if( $settings['fill_empty_gps_by_loc'])
				{
					$location_lat = $loc_result->$col_lat;
					$location_lon = $loc_result->$col_lon;
				}

				$location = $loc_result->$col_name;

				// Save the location id
				$special['loc_id'] = $loc_id;

			} else {
				// If there is no result, we use the code as location name
				$location = $loc_id;
			}


		} else {
			$location = next($m_elements);
		}
		$post['location_name'] = $location;


		// 6-7 : LATITUDE / LONGITUDE

		$lat = next($m_elements);
		$lon = next($m_elements);

		if ( ($loc_table !== false ) 
			 AND $settings['locate_from_list']
			 AND $settings['fill_empty_gps_by_loc'])
		{
			if ($lat == '' OR $lon == '')
			{
				$lat = $location_lat;
				$lon = $location_lon;
				$special['gps_replaced'] = 1;
			}
		}

		$post['latitude'] = $lat;
		$post['longitude'] = $lon;


		// 8 : CUSTOM FIELDS

		if($elements_count > $min_elements)
		{
			// 8a : FORM
			$post['form_id'] = next($m_elements);

			// Check if integer
			if ( ! ctype_digit($post['form_id']) )
			{
				$this->p_error( 'Invalid form_id',
					'form_id provided is invalid : '. $post['form_id'].
					' Value must be integer');
				return;
			}

			// Form exists ?
			if( ! ORM::factory('form')->is_valid_form($post['form_id']) )
			{
				$this->p_error( 'Invalid form_id',
					'form_id provided is invalid : '. $post['form_id'].
					' This form doesn\'t exists');
				return;
			}

			// Get the forms fields
			$custom_fields = customforms::get_custom_form_fields(FALSE,$post['form_id'],FALSE);
			//var_dump($custom_fields);

			// 8b : NOT TOO MUCH ARGS
			//		The "+1" is for the element "form_id"
			if ( $elements_count - $min_elements > ( count($custom_fields) +1 ))
			{
				$this->p_error( 'Too many args for custom fields',
					'Too many arguments.'.
					'There is '.count($custom_fields).' custom fields in this form '.
				    '(id:'.$post['form_id'].') and you provided '.
					($elements_count - $min_elements - 1).
					' arguments in your message.'.
				    ' You provided :'."\n".
					Kohana::debug(array_slice($m_elements, key($m_elements) +1)));
				return;
			}

			// = START PROCESSING FIELDS =
			// We start from the first field specified
			reset($custom_fields);

			while( ($element = next($m_elements)) !== false)
			{
				// Current field
				list($field_id, $field) = each ($custom_fields);

				$response = '';

				// Muli-values fields, if value by id activated
				if ( $settings['multival_resp_by_id'] 
						AND ( customforms::field_is_multi_value($field)))
				{
					// Get possible answers
					$defaults = explode(',' , $field['field_default']);
					// Get given ids
					$response_ids = explode(',' , $element );

					$response_array = array();

					foreach ($response_ids as $r_id)
					{
						$r_id = trim($r_id); // Without spaces
						// Not numeric
						if ( ! is_numeric($r_id))
						{
							$this->p_error( 'Multi-value by id',
								$field['field_name'] . '[' . $r_id . ']' .
							    ' : The id `'. $r_id . '` is not numeric.');
							return;
						}

						// Don't exists
						if ( ! array_key_exists($r_id, $defaults))
						{
							$this->p_error( 'Multi-value by id',
								$field['field_name'] . 
								'[' . $r_id . ']' . ' does not exist');
							return;
						}

						$response_array[] = trim($defaults[$r_id]);
					}

					// Save responses
					$response = implode(',' , $response_array);

				} else {		// Other fields
					$response = $element;
				}

				$post['custom_field'][$field_id] = $response;
			}
		}

		// 9 - SPECIAL FIELDS

		if ( ($loc_table !== false ) AND $settings['locate_from_list'])
		{
			// Save location code
			$field = $settings['loc_code_field'];
			$field_id = $this->get_cfield_id_by_name($post['form_id'], $field);

			if($field_id !== false)
			{
				$post['custom_field'][$field_id] = $special['loc_id'];
			} // else : do nothing


			if( $settings['fill_empty_gps_by_loc'])
			{
				// Save if gps was replaced
				$field = $settings['coord_type_field'];
				$field_id = $this->get_cfield_id_by_name($post['form_id'], $field);

				if($field_id !== false)
				{
					$post['custom_field'][$field_id] = $special['gps_replaced'];
				} // else : do nothing
			}
		}



		// For debug
		// echo Kohana::debug($post);

		// We re-use the same process as Reports_Controller->submit()
		if (reports::validate($post))
		{
			// STEP 1: SAVE LOCATION
			$location = new Location_Model();
			reports::save_location($post, $location);

			// STEP 2: SAVE INCIDENT
			$incident = new Incident_Model();
			reports::save_report($post, $incident, $location->id);
			
			// STEP 2b: Record Approval/Verification Action
			reports::verify_approve($incident);

			// STEP 2c: SAVE INCIDENT GEOMETRIES
			// We don't have any geometries here
			// reports::save_report_geometry($post, $incident);

			// STEP 3: SAVE CATEGORIES
			reports::save_category($post, $incident);

			// STEP 4: SAVE MEDIA
			// We don't have any media here
			// reports::save_media($post, $incident);

			// STEP 5: SAVE CUSTOM FORM FIELDS
			reports::save_custom_fields($post, $incident);

			// STEP 6: SAVE PERSONAL INFORMATION
			reports::save_personal_info($post, $incident);

			// Don't forget to update the message with Incident Id
			$this->event->incident_id = $incident->id;
			$this->event->save();

			// Run events
			Event::run('ushahidi_action.report_submit', $post);
			Event::run('ushahidi_action.report_add', $incident);

			// For debug
			// echo Kohana::debug($post);
		} else {
			$this->p_error( 'Validation',
				'The SMS did not pass the validation process. '.
				"\n\n" . 'ERROR MESSAGES :' . "\n".
				Kohana::debug($post->errors('report')));

				return;
		}


	}



/*==============================================================================
 * Private functions
 =============================================================================*/

	/**
	 * Returns the field id nammed $name in $form
	 *	or false otherwise
	 *
	 * @param int $form The form id
	 * @param string $name The name to find
	 */
	private function get_cfield_id_by_name($form, $name)
	{
		// empty or '' => nothing to search
		if( ( $name == '') OR ($name  === NULL) )
			return false;

		// Find the field
		$field = ORM::factory('form_field')
			->where('field_name', $name)
			->where('form_id',$form)
			->find();

		if($field->loaded)
		{
			return $field->id;
		} else {
			return false;
		}
	}


	/**
	 * Check to see if we're using the white list, 
	 *		and if so, if our SMSer is whitelisted
	 *
	 *	@param int $from Sender of the SMS
	 */
	private function is_whitelisted($from)
	{		
		$wl = ORM::factory($this->white_table);

		// Check if there is numbers in the table
		if($wl->count_all() > 0)
		{
			//check if the phone number of the incoming text is white listed
			$row = $wl->where('phone_number', $from)->find();

			return $row->loaded;

		} else {
			return true;	// Granted for all if no number specified
		}
	}
	

	/**
	 * Get the minimal number of elements required by settings
	 */
	private function get_min_elements()
	{
		$settings = $this->settings;

		/* Worse case, 8 elements :
		 *	Code, title, category, date, description, location, latitude, 
		 *		longitude
		 *	FormID and custom forms are optionnal
		 */
		$n = 8;

		if ($settings['auto_title'])
		{
			$n--;
		}

		if ($settings['auto_date'])
		{
			$n--;
		}

		if ($settings['auto_desc'])
		{
			$n--;
		}

		return $n;
	}
		

	/**
	 * This function echo the error
	 *  and if "append_error" is set, appends the error to text message
	 */
	private function p_error($head, $msg)
	{
		echo 'ERROR ' . $head . "\n\n" . $msg;

		if ( $this->settings['append_errors'] )
		{
			$this->event->message = 'ERROR ' . $head . "\n\n" . $this->event->message;
			$this->event->message .= "\n\n" . $msg;
			$this->event->save();
		}
	}


}

new smsautomate;
