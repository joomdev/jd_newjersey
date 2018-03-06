<?php
/**
*
* State View
*
* @package	VirtueMart
* @subpackage State
* @author RickG, RolandD
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.json.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the state
 *
 * @package	VirtueMart
 * @subpackage State
 * @author RolandD, jseros
 */
class VirtuemartViewState extends JViewLegacy {

	function display($tpl = null) {

		$states = array();
		$db = JFactory::getDBO();
		//retrieving countries id
		$country_ids = vRequest::getString('virtuemart_country_id');
		$country_ids = explode(',', $country_ids);
		
		foreach($country_ids as $country_id){
			$q= 'SELECT `virtuemart_state_id`, `state_name` FROM `#__virtuemart_states`  WHERE `virtuemart_country_id`= "'.(int)$country_id.'" 
				ORDER BY `#__virtuemart_states`.`state_name`';
			$db->setQuery($q);
			
			$states[$country_id] = $db->loadAssocList();
		}
		
		echo vmJsApi::safe_json_encode($states);
	}
}
// pure php no closing tag
