<?php
/*------------------------------------------------------------------------------
 *
 * Settings table model
 * 
 * File :         models/smsreport.php
 * Project :      SMS Report
 * Last Modified :ven. 30 août 2013 11:15:59 CEST
 * Created :      juillet 2013
 *
 * Original Copyright :
 *  This project was originally forked from SMS Automate by John Etherton,
      available at https://github.com/jetherton/smsautomate
 *
 * Author :       G.F
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
 * Model for plugin settings
 *
 * Adapted from Ushahidi Settings_Model class by Team Ushahidi under LGPL
 */
class Smsautomate_Model extends ORM
{
	// Database table name
	protected $table_name = 'smsautomate';
        
	// Prevents cached items from being reloaded
	protected $reload_on_wakeup   = FALSE;

	/**
	 * Given the setting identifier, returns its value. If the identifier
	 * is non-existed, a NULL value is returned
	 *
	 * @param string $key UniqueID of the settings item
	 *
	 * @return string
	 */
	public static function get_setting($key)
	{
		$setting = ORM::factory('smsautomate')->where('key', $key)->find();
		return ($setting->loaded) ? $setting->value : NULL;

	}


	/**
	 * Convenience method for the settings ORM when not loaded
	 * with a specific settings value
	 * @return string
	 */
	public function get($key)
	{
		return self::get_setting($key);
	}


	/**
	 * Returns a key=>value array of the unique setting identifier
	 * and its corresponding value
	 *
	 * @return array
	 */
	public static function get_array()
	{
		$all_settings = ORM::factory('smsautomate')->find_all();
		$settings = array();
		foreach ($all_settings as $setting)
		{
			$settings[$setting->key] = $setting->value;
		}

		return $settings;
	}


	/**
	 * Convenience method to save a single setting value
	 *
	 * @param string key Unique ID of the setting
	 * @param string value Value for the setting item
	 */
	public static function save_setting($key, $value)
	{
			$setting = ORM::factory('smsautomate')->where('key', $key)->find();
			
			$setting->key = $key;
			$setting->value = $value;
			$setting->save();
	}


	/**
	 * Given a validation object, updates the settings table
	 * with the values assigned to its properties
	 *
	 * @param Validation $settings Validation object
	 */
	public static function save_all(Validation $settings)
	{
		
		// Get all the settings
		$all_settings = self::get_array();

		// Settings update query
		$query = sprintf("UPDATE `%ssmsautomate` SET `value` = CASE `key` ", 
		    Kohana::config('database.default.table_prefix'));

		// Used for building the query clauses for the final query
		$values = array();
		$keys = array();
		
		// List of value to skip
		$skip = array();

		$value_expr = new Database_Expression("WHEN :key THEN :value ");
		foreach ($settings as $key => $value)
		{
			// If an item has been marked for skipping or is a 
			// non-existent setting, skip current iteration
			if (in_array($key, $skip) OR empty($key) OR ! array_key_exists($key, $all_settings))
				continue;

			$value_expr->param(':key', $key);
			$value_expr->param(':value', $value);

			$keys[] = $key;
			$values[] = $value_expr->compile();
		}
		
		// Construct the final query
		$query .= implode(" ", $values)."END WHERE `key` IN :keys";

		// Performa batch update
		Database::instance()->query($query, array(':keys' => $keys));
	}

}
