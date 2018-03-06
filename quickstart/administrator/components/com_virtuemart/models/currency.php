<?php
/**
 *
 * Data module for shop currencies
 *
 * @package	VirtueMart
 * @subpackage Currency
 * @author RickG
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: currency.php 9413 2017-01-04 17:20:58Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop Currencies
 *
 * @package	VirtueMart
 * @subpackage Currency
 */
class VirtueMartModelCurrency extends VmModel {


	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('currencies');
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 *
	 * @author Max Milbers
	 */
	function getCurrency($currency_id=0) {
		return $this->getData($currency_id);
	}


	/**
	 * Retireve a list of currencies from the database.
	 * This function is used in the backend for the currency listing, therefore no asking if enabled or not
	 * @author Max Milbers
	 * @return object List of currency objects
	 */
	function getCurrenciesList($search,$vendorId=1) {

		$where = array();

		$user = JFactory::getUser();
		$shared = '';
		if(vmAccess::manager() ){
			$shared = 'OR `shared`="1"';
		}
		$vendorId = vmAccess::isSuperVendor();
		if($vendorId){
			$where[]  = '(`virtuemart_vendor_id` = "'.(int)$vendorId.'" '.$shared.')';
		}

		if(empty($search)){
			$search = vRequest::getString('search', false);
		}
		// add filters
		if($search){
			$db = JFactory::getDBO();
			$search = '"%' . $db->escape( $search, true ) . '%"' ;
			$where[] = '`currency_name` LIKE '.$search.' OR `currency_code_2` LIKE '.$search.' OR `currency_code_3` LIKE '.$search;
		}

		$whereString='';
		if (count($where) > 0) $whereString = ' WHERE '.implode(' AND ', $where) ;

		$data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_currencies`',$whereString,'',$this->_getOrdering());

		return $data;
	}

	function getVendorAcceptedCurrrenciesList($vendorId = 0){

		static $currencies = array();
		if($vendorId===0){
			$multix = Vmconfig::get('multix','none');
			if(strpos($multix,'payment')!==FALSE){
				if (!class_exists('VirtueMartModelVendor'))
					require(VMPATH_ADMIN . DS . 'models' . DS . 'vendor.php');
				$vendorId = VirtueMartModelVendor::getLoggedVendor();

			} else {
				$vendorId = 1;
			}
		}
		if(!isset($currencies[$vendorId])){
			$db = JFactory::getDbo();
			$q = 'SELECT `vendor_accepted_currencies`, `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`=' . $vendorId;
			$db->setQuery($q);
			$vendor_currency = $db->loadAssoc();
			if (!$vendor_currency['vendor_accepted_currencies']) {
				$vendor_currency['vendor_accepted_currencies'] = $vendor_currency['vendor_currency'];
				vmWarn('No accepted currencies defined');
				if(empty($vendor_currency['vendor_accepted_currencies'])) {
					$uri = JFactory::getURI();
					$link = $uri->root().'administrator/index.php?option=com_virtuemart&view=user&task=editshop';
					vmWarn(vmText::sprintf('COM_VIRTUEMART_CONF_WARN_NO_CURRENCY_DEFINED','<a href="'.$link.'">'.$link.'</a>'));
					$currencies[$vendorId] = false;
					return $currencies[$vendorId];
				}
			}
			$q = 'SELECT `virtuemart_currency_id`,CONCAT_WS(" ",`currency_name`,`currency_symbol`) as currency_txt
					FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id` IN ('.$vendor_currency['vendor_accepted_currencies'].')';
			if($vendorId!=1){
				$q .= ' AND (`virtuemart_vendor_id` = "'.$vendorId.'" OR `shared`="1")';
			}
			$q .= '	AND published = "1"
					ORDER BY `ordering`,`currency_name`';

			$db->setQuery($q);
			$currencies[$vendorId] = $db->loadObjectList();
		}

		return $currencies[$vendorId];
	}

	/**
	 * Retireve a list of currencies from the database.
	 *
	 * This is written to get a list for selecting currencies. Therefore it asks for enabled
	 * @author Max Milbers
	 * @return object List of currency objects
	 */
	function getCurrencies($vendorId=1) {
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `#__virtuemart_currencies` WHERE (`virtuemart_vendor_id` = "'.(int)$vendorId.'" OR `shared`="1") AND published = "1" ORDER BY `ordering`,`#__virtuemart_currencies`.`currency_name`';
		$db->setQuery($q);
		return $db->loadObjectList();
	}

	function store(&$data){
		if(!vmAccess::manager('currency')){
			vmWarn('Insufficient permissions to store currency');
			return false;
		}
		return parent::store($data);
	}

	function remove($ids){
		if(!vmAccess::manager('currency')){
			vmWarn('Insufficient permissions to remove currency');
			return false;
		}
		return parent::remove($ids);
	}

}
// pure php no closing tag