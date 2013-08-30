<?php
/**-----------------------------------------------------------------------------
 *
 * Install/uninstall methods for the smsreport plugin
 * 
 * File :         librairies/smsreport_install.php
 * Project :      SMS Report
 * Last Modified :ven. 30 août 2013 16:47:47 CEST
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
defined('SYSPATH') or die('No direct script access.');

/**
 * Installer/uninstaller of the plugin
 */
class Smsreport_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();

		$pre = Kohana::config('database.default.table_prefix');

		$this->table = $pre.'smsreport';
		$this->table_white = $pre.'smsreport_whitelist';

	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{

		// Create the settings table
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.$this->table.'` (
							  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
							  `key` varchar(100) NOT NULL DEFAULT \'\',
							  `value` text ,
							  PRIMARY KEY (`id`),
							  UNIQUE KEY `uq_smsreport_settings_key` (`key`)
							) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		// Create whitelist table
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.$this->table_white.'` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`phone_number` varchar(20) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		//Create default settings if not exists
		if( ORM::factory('smsreport')->count_all() == 0)
		{
			$s = ORM::factory('smsreport');

			$s::save_setting('delimiter', '#');
			$s::save_setting('code_word', 'abc');
			$s::save_setting('auto_title', '0');
			$s::save_setting('auto_desc', '0');
			$s::save_setting('auto_date', '1');
			$s::save_setting('auto_approve', '0');
			$s::save_setting('auto_verify', '0');
			$s::save_setting('append_to_desc', '1');
			$s::save_setting('append_to_desc_txt', 'This report was created by SMS Report.');
			$s::save_setting('multival_resp_by_id', '0');
			$s::save_setting('locate_from_list', '0');
			$s::save_setting('fill_empty_gps_by_loc', '0');
			$s::save_setting('locate_from_list_id', NULL);
			$s::save_setting('loc_code_field', NULL);
			$s::save_setting('locate_from_list_name', NULL);
			$s::save_setting('locate_from_list_lat', NULL);
			$s::save_setting('locate_from_list_lon', NULL);
			$s::save_setting('coord_type_field', NULL);
			$s::save_setting('append_errors', '1');
		}

	}


	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.$this->table.'`');
		$this->db->query('DROP TABLE `'.$this->table_white.'`');
	}
}
