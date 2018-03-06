<?php
/**
*
* Data module for shop coupons
*
* @package	VirtueMart
* @subpackage Coupon
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop coupons
 *
 * @package	VirtueMart
 * @subpackage Coupon
 */
class VirtueMartModelCoupon extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('coupons');
	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     */
	function getCoupon($id = 0){
		return $this->getData($id);
	}

	/**
	 * Bind the post data to the coupon table and save it
     *
     * @return mixed False if the save was unsuccessful, the coupon ID otherwise.
	 */
    function store(&$data) {
		if(!vmAccess::manager('coupon.edit')){
			vmWarn('Insufficient permission to store coupons');
			return false;
		} else if( empty($data['virtuemart_coupon_id']) and !vmAccess::manager('coupon.create')){
			vmWarn('Insufficient permission to create coupons');
			return false;
		}
		$table = $this->getTable('coupons');

		// Convert selected dates to MySQL format for storing.
		if ($data['coupon_start_date']) {
		    $startDate = JFactory::getDate($data['coupon_start_date']);
		    $data['coupon_start_date'] = $startDate->toSQL();
		}
		if ($data['coupon_expiry_date']) {
		    $expireDate = JFactory::getDate($data['coupon_expiry_date']);
		    $data['coupon_expiry_date'] = $expireDate->toSQL();
		}
		$table->bindChecknStore($data);
		$data['virtuemart_coupon_id'] = $table->virtuemart_coupon_id;

        return $table->virtuemart_coupon_id;
	}


	/**
	 * Retireve a list of coupons from the database.
	 *
	 * @return object List of coupon objects
	 */
	function getCoupons($filterCoupon = false) {

		$virtuemart_vendor_id = vmAccess::getVendorId();
		$where = array();

		if(!empty($virtuemart_vendor_id)){
			$where[] = '`virtuemart_vendor_id`="'.$virtuemart_vendor_id.'"';
		}
		if($filterCoupon) {

			$filterCouponS = '"%' . $this->_db->escape( $filterCoupon, true ) . '%"' ;
			$where[] = '`coupon_code` LIKE '.$filterCouponS;

		}

		$whereString = '';
		if (count($where) > 0) $whereString = ' WHERE '.implode(' AND ', $where) ;

		return $this->_data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_coupons`',$whereString,'',$this->_getOrdering());
	}

	function remove($ids){
		if(!vmAccess::manager('coupon.delete')){
			vmWarn('Insufficient permissions to remove state');
			return false;
		}
		return parent::remove($ids);
	}
}

// pure php no closing tag