<?php
/*------------------------------------------------------------------------------
 *
 * Location table model
 * 
 * File :         models/smsreport_location.php
 * Project :      SMS Report
 * Last Modified :ven. 30 août 2013 11:27:44 CEST
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
 * Model for plugin location table
 *
 * (table can be configured manually in config file)
 */
class Smsreport_location_Model extends ORM
{
	// Database table name
	protected $table_name = '';

	/**
	 * Special constructor, 
	 * construct object with table given in config file
	 */
	function __construct($id = NULL)
	{
		$this->table_name = Kohana::config('smsreport.location.table');
		parent::__construct($id);
	}

}
