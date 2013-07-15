<?php
/**
 * Performs install/uninstall methods for the smsautomate plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   smsautomate Installer
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Smsautomate_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
		$this->table = 'smsautomate';
		$this->table_white = 'smsautomate_whitelist';

		$this->pre = Kohana::config('database.default.table_prefix');
	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.$this->pre.$this->table.'` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`delimiter` varchar(1) NOT NULL,
			`code_word` varchar(11) NOT NULL,
			`auto_title` BOOLEAN NOT NULL,
			`auto_desc` BOOLEAN NOT NULL,
                        `auto_date` BOOLEAN NOT NULL,
			`auto_approve` BOOLEAN NOT NULL,
			`auto_verify` BOOLEAN NOT NULL,
			`append_to_desc` BOOLEAN NOT NULL,
			`append_to_desc_txt` text NOT NULL DEFAULT "",
			`multival_resp_by_id` BOOLEAN NOT NULL,
			`locate_from_list` BOOLEAN NOT NULL,
			`fill_empty_gps_by_loc` BOOLEAN NOT NULL,
			`locate_from_list_table` varchar(50) NULL DEFAULT NULL,
			`locate_from_list_id` varchar(50) NULL DEFAULT NULL,
			`locate_from_list_name` varchar(50) NULL DEFAULT NULL,
			`locate_from_list_lat` varchar(50) NULL DEFAULT NULL,
			`locate_from_list_lon` varchar(50) NULL DEFAULT NULL,
			`append_errors` BOOLEAN NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8');

		$this->db->query('CREATE TABLE IF NOT EXISTS `'.$this->pre.$this->table_white.'` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`phone_number` varchar(20) NOT NULL,
			PRIMARY KEY (`id`)
		) DEFAULT CHARSET=utf8');

		//Create default settings if not exists
		if( ORM::factory($this->table, 1)->count_all() == 0)
		{
			$settings = ORM::factory($this->table);
			$settings->id = 1;
			$settings->delimiter = '#';
			$settings->code_word = 'abc';
			$settings->auto_title = false;
			$settings->auto_desc = false;
			$settings->auto_date = true;
			$settings->auto_approve = false;
			$settings->auto_verify = false;
			$settings->append_to_desc = true;
			$settings->append_to_desc_txt = 'This reported was created automatically via SMS.';
			$settings->multival_resp_by_id = false;
			$settings->locate_from_list = false;
			$settings->fill_empty_gps_by_loc = false;
			$settings->locate_from_list_table = NULL;
			$settings->locate_from_list_id = NULL;
			$settings->locate_from_list_name = NULL;
			$settings->locate_from_list_lat = NULL;
			$settings->locate_from_list_lon = NULL;
			$settings->append_errors = true;

			$settings->save();
	}

	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.$this->pre.$this->table.'`');
		$this->db->query('DROP TABLE `'.$this->pre.$this->table_white.'`');
	}
	}
