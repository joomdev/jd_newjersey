<?php
/**
*
* Users table
*
* @package	VirtueMart
* @subpackage User
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: user_shoppergroup.php 2420 2010-06-01 21:12:57Z oscar $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmTableData'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmtabledata.php');


class TableCarts extends VmTableData {

	/** @var int Vendor ID */
	var $virtuemart_cart_id		= 0;
	var $virtuemart_user_id 	= 0;
	var $virtuemart_vendor_id	= 0;
	var $cartData 	= 0;


	function __construct(&$db)
	{
		parent::__construct('#__virtuemart_carts', 'virtuemart_cart_id', $db);

		$this->setPrimaryKey('virtuemart_user_id');

		$this->setLoggable();

		$this->setTableShortCut('cart');
	}


}
