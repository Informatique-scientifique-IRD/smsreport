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

		$this->settings = ORM::factory('smsautomate',1)->as_array();
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
			'form_id'	  => '',
			'custom_field' => array(),
			'service_id' => 1  // Mode : sms
		);

		//split up the string using the delimiter
		$m_elements = explode($settings['delimiter'], $sms['m']);
		$elements_count = count($m_elements);
		$min_elements = $this->get_min_elements();

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
			$title = 'SMS-Report by '.$sms['from'].' received '.$sms['date'];
		} else {
			$title = next($m_elements);
		}
		$post['incident_title'] = $title;


		// 2 : CATEGORIES
		$post['incident_category'] = explode(",", next($m_elements) );
					

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
		$post['location_name'] = next($m_elements);


		// 6-7 : LATITUDE / LONGITUDE

		//latitude
		$post['latitude'] = next($m_elements);

		//longitude
		$post['longitude'] = next($m_elements);



		// 8 : CUSTOM FIELDS
		if($elements_count > $min_elements)
		{
			$custom_fields = customforms::get_custom_form_fields(FALSE,2,FALSE);

			// 8a : FORM
			$post['form_id'] = next($m_elements);

			if ( ! is_numeric($post['form_id']) )
			{
				$this->p_error( 'Invalid form_id',
					'form_id provided is invalid : '. $post['form_id'].
					' Value must be numeric');
				return;
			}

			// 8b : VALID
			//		The "+1" is for the element "form_id"
			if ( $elements_count - $min_elements > ( count($custom_fields) +1 ))
			{
				$this->p_error( 'Too many args for custom fields',
					'Not enough arguments.'.
					'There is '.count($custom_fields).' custom fields in this form '.
				    '(id:'.$post['form_id'].') and you provided '.
					($elements_count - $min_elements - 1).
					'arguments in your message .'.
				    'You provided :'."\n".
					Kohana::debug(array_slice($m_elements, key($m_elements) +1)));
				return;
			}

			// = START PROCESSING FIELDS =
			reset($custom_fields);

			while( ($element = next($m_elements)) !== false)
			{
				list($field_id, $field) = each ($custom_fields);

				$response = '';

				// Muli-values fields
				if ( $settings['multival_resp_by_id'] 
						AND ( $field['field_type'] >= 5 
								AND $field['field_type'] <=7 ) )
				{
					$defaults = explode(',' , $field['field_default']);
					$response_ids = explode(',' , $element );

					foreach ($response_ids as $r_id)
					{
						$r_id = trim($r_id);
						if ( ! is_numeric($r_id))
						{
							$this->p_error( 'Multi-value by id',
								$field['field_name'] . '[' . $r_id . ']' .
							    ' : The id `'. $r_id . '` is not numeric.');
							return;
						}

						if ( ! array_key_exists($r_id, $defaults))
						{
							$this->p_error( 'Multi-value by id',
								$field['field_name'] . 
								'[' . $r_id . ']' . ' does not exist');
							return;
						}

						if ( $response != '')
						{
							$response .= ',' . trim($defaults[$r_id]);
						} else {
							$response = trim($defaults[$r_id]);
						}
					}

				} else {		// Other fields
					$response = $element;
				}

				$post['custom_field'][$field_id] = $response;
			}
		}


		// We re-use the same process as Reports_Controller->submit()
		if (reports::validate($post))
		{
			// STEP 1: SAVE LOCATION
			$location = new Location_Model();
			reports::save_location($post, $location);

			// STEP 2: SAVE INCIDENT
			$incident = new Incident_Model();
			reports::save_report($post, $incident, $location->id);

			// STEP 2b: SAVE INCIDENT GEOMETRIES
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

		// TODO : Add option to automatically activate & verify reports	

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

			return $row->loaded();

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
