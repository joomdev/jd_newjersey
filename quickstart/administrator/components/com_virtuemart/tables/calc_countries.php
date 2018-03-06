<?php
/**
*
* virtuemart_calc_countries table ( to map calc rules to countries)
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2011 - 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: virtuemart_calc_countries.php 3002 2011-04-08 12:35:45Z alatak $
*/

defined('_JEXEC') or die();

if(!class_exists('VmTableXarray'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmtablexarray.php');

class TableCalc_countries extends VmTableXarray {

	function __construct(&$db){
		parent::__construct('#__virtuemart_calc_countries', 'id', $db);

		$this->setPrimaryKey('virtuemart_calc_id');
		$this->setSecondaryKey('virtuemart_country_id');
	}

}
