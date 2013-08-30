<?php
/**-----------------------------------------------------------------------------
 *
 * Configuration file of the plugin
 * 
 * File :         config/smsreport.php
 * Project :      SMS Report
 * Last Modified :ven. 30 août 2013 16:02:09 CEST
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

$config = array(

	'location' => array(

		/* Table in the database where to find 
		 * locations by codes and their corresponding names
		 *
		 * To disable the option, let this config empty
		 */
		'table' => '',  
	),
);
