<?php
/**-----------------------------------------------------------------------------
 *
 * Controller for SMS Report
 * 
 * File :         controllers/smsreport.php
 * Project :      SMS Report
 * Last Modified :ven. 30 août 2013 10:39:23 CEST
 * Created :      29 août 2013
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
 * Controller of the plugin SMS Report
 */
class Smsreport_Controller extends Controller
{

	/**
	 * Return the location.table specified in config file if it exists.
	 *	If not, returns false
	 */
	public static function get_loc_table()
	{
		// Static variable of the table, so we only check table existence once
		static $table = null;

		// First run
		if(is_null($table))
		{
			$table_name = Kohana::config('smsreport.location.table');

			if( Database::instance()->table_exists($table_name) )
			{
				$table = $table_name;
			} else {
				$table = false;
			}
		}

		return $table;
	}
}
