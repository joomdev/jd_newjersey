<?php
/**
*
* vendor_user_xref table 
*
* @package	VirtueMart
* @subpackage vendor
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2015 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: vendor_users.php
*/

defined('_JEXEC') or die();

if(!class_exists('VmTableXarray'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmtablexarray.php');

class TableVendor_users extends VmTableXarray {

	/**
	 * @param JDataBase $db database connector object
	 */
	function __construct(&$db){
		parent::__construct('#__virtuemart_vendor_users', 'id', $db);

		$this->setPrimaryKey('virtuemart_vendor_id');
		$this->setSecondaryKey('virtuemart_user_id');
	}

}
