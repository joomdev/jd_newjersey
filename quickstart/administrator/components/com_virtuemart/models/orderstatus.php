<?php
/**
 *
 * Data module for the order status
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: orderstatus.php 9420 2017-01-12 09:35:36Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for the order status
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 */
class VirtueMartModelOrderstatus extends VmModel {

	private $_renderStatusList = array();
	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		vmLanguage::loadJLang('com_virtuemart_orders',TRUE);
		$this->setMainTable('orderstates');
	}

	function getVMCoreStatusCode(){
		return array( 'P','S','X');
	}

	/**
	 * Retrieve a list of order statuses from the database.
	 * @return object List of order status objects
	 */
	function getOrderStatusList($published=true)
	{

		if (vRequest::getCmd('view') !== 'orderstatus') $ordering = ' order by `ordering` ';
		else $ordering = $this->_getOrdering();
		$this->_noLimit=true;
		if($published){	
			$published = 'WHERE published = "1"';
		} else {
			$published = '';
		}
		$this->_data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_orderstates`',$published,'',$ordering);
		// 		vmdebug('order data',$this->_data);
		return $this->_data ;
	}
	/**
	 * Return the order status names
	 *
	 * @author Kohl Patrick
	 * @access public
	 *
	 * @param char $_code Order status code
	 * @return string The name of the order status
	 */
	public function getOrderStatusNames ($published = true) {
		static $orderStatusNames=0;
		if(empty($orderStatusNames)){
			if($published){
				$published = 'WHERE published = "1"';
			} else {
				$published = '';
			}
			$q = 'SELECT `order_status_name`,`order_status_code` FROM `#__virtuemart_orderstates` '.$published.'order by `ordering` ';
			$db = JFactory::getDBO();
			$db->setQuery($q);
			$orderStatusNames = $db->loadAssocList('order_status_code');
		}

		return $orderStatusNames;

	}

	function renderOSList($value,$name = 'order_status',$multiple=FALSE,$attrs='',$langkey=''){

		$idA = $id = $name;
 		$attrs .= ' class="inputbox" ';
		if ($multiple) {
			$attrs .= ' multiple="multiple" ';
			if(empty($langkey)) $langkey = 'COM_VIRTUEMART_DRDOWN_SELECT_SOME_OPTIONS';
			$attrs .=  ' data-placeholder="'.vmText::_($langkey).'"';
			$idA .= '[]';
		} else {
			if(empty($langkey)) $langkey = 'COM_VIRTUEMART_LIST_EMPTY_OPTION';
		}

		if(is_array($value)){
			$hashValue = implode($value);
		} else {
			$hashValue = $value;
		}

		$hash = md5($hashValue.$name.$attrs);
		if (!isset($this->_renderStatusList[$hash])) {
			$orderStates = $this->getOrderStatusNames();
			$emptyOption = JHtml::_ ('select.option', -1, vmText::_ ($langkey), 'order_status_code', 'order_status_name');
			array_unshift ($orderStates, $emptyOption);
			if ($multiple) {
				$attrs .=' size="'.count($orderStates).'" ';
			}

			$this->_renderStatusList[$hash] = JHtml::_('select.genericlist', $orderStates, $idA, $attrs, 'order_status_code', 'order_status_name', $value,$id,true);
		}
		return $this->_renderStatusList[$hash] ;
	}

	function store(&$data){
		if(!vmAccess::manager('orderstatus')){
			vmWarn('Insufficient permissions to store orderstatus');
			return false;
		}
		return parent::store($data);
	}

	function remove($ids){
		if(!vmAccess::manager('orderstatus')){
			vmWarn('Insufficient permissions to remove orderstatus');
			return false;
		}
		return parent::remove($ids);
	}

}

//No Closing tag
