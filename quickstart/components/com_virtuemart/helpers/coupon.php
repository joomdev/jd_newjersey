<?php
/**
 * Front-end Coupon handling functions
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Oscar van Eijk
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 * @version $Id: coupon.php 9775 2018-03-06 20:49:50Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

abstract class CouponHelper
{

	/**
	 * Check if the given coupon code exists, is (still) valid and valid for the total order amount
	 * @param string $_code Coupon code
	 * @param float $_billTotal Total amount for the order
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @return string Empty when the code is valid, otherwise the error message
	 */
	static public function ValidateCouponCode($_code, $_billTotal){

		if(empty($_code) or $_code == vmText::_('COM_VIRTUEMART_COUPON_CODE_ENTER') or $_code == vmText::_('COM_VIRTUEMART_COUPON_CODE_CHANGE')) {
			return '';
		}
		$couponData = 0;

		JPluginHelper::importPlugin('vmcoupon');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmValidateCouponCode', array($_code, $_billTotal));
		if(!empty($returnValues)){
			foreach ($returnValues as $returnValue) {
				if ($returnValue !== null  ) {
					return $returnValue;
				}
			}
		}
		if(empty($couponData)){
			$_db = JFactory::getDBO();
			$_q = 'SELECT IFNULL( NOW() >= `coupon_start_date` OR `coupon_start_date`="0000-00-00 00:00:00" , 1 ) AS started
    				, `coupon_start_date`
    				,  IFNULL (`coupon_expiry_date`!="0000-00-00 00:00:00" and NOW() > `coupon_expiry_date`,0) AS `ended`
    				, `coupon_expiry_date`
    				, `coupon_value_valid`
    				, `coupon_used`
    				FROM `#__virtuemart_coupons`
    				WHERE `coupon_code` = "' . $_db->escape($_code) . '"';
			$_db->setQuery($_q);
			$couponData = $_db->loadObject();
		}

		if (!$couponData) {
			return vmText::_('COM_VIRTUEMART_COUPON_CODE_INVALID');
		}
		if ($couponData->coupon_used) {
			$session = JFactory::getSession();
			$session_id = $session->getId();
			if ($couponData->coupon_used != $session_id) {
				return vmText::_('COM_VIRTUEMART_COUPON_CODE_INVALID');
			}
		}
		if (!$couponData->started) {
			return vmText::_('COM_VIRTUEMART_COUPON_CODE_NOTYET') . $couponData->coupon_start_date;
		}
		if ($couponData->ended) {
			//self::RemoveCoupon($_code, true);
			return vmText::_('COM_VIRTUEMART_COUPON_CODE_EXPIRED');
		}

		if ($_billTotal < $couponData->coupon_value_valid) {
			if (!class_exists('CurrencyDisplay'))
			    require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance();

			$coupon_value_valid = $currency->priceDisplay($couponData->coupon_value_valid);
			return vmText::_('COM_VIRTUEMART_COUPON_CODE_TOOLOW') . " ".$coupon_value_valid;
		}

		return '';
	}

	/**
	 * Get the details for a given coupon
	 * @param string $_code Coupon code
	 * @author Oscar van Eijk
	 * @return object Coupon details
	 */
	static public function getCouponDetails($_code)
	{
		$_db = JFactory::getDBO();
		$_q = 'SELECT `percent_or_total` '
			. ', `coupon_type` '
			. ', `coupon_value` '
			. 'FROM `#__virtuemart_coupons` '
			. 'WHERE `coupon_code` = "' . $_db->escape($_code) . '"';
		$_db->setQuery($_q);
		return $_db->loadObject();
	}

	/**
	 * Remove a coupon from the database
	 * @param $_code Coupon code
	 * @param $_force True if the remove is forced. By default, only gift coupons are removed
	 * @author Oscar van Eijk
	 * @return boolean True on success
	 */
	static public function RemoveCoupon($_code, $_force = false)
	{
		JPluginHelper::importPlugin('vmcoupon');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmRemoveCoupon', array($_code, $_force));
		if(!empty($returnValues)){
			foreach ($returnValues as $returnValue) {
				if ($returnValue !== null  ) {
					return $returnValue;
				}
			}
		}

		if ($_force !== true) {
			$_data = self::getCouponDetails($_code);
			if (!empty($_data) and $_data->coupon_type != 'gift') {
				return true;
			}
		}
		$_db = JFactory::getDBO();
		$_q = 'DELETE FROM `#__virtuemart_coupons` '
			. 'WHERE `coupon_code` = "' . $_db->escape($_code) . '"';
		$_db->setQuery($_q);
		return ($_db->query() !== false);
	}
	/**
	 * Remove a coupon from the database
	 * @param $_code Coupon code
	 * @param $_force True if the remove is forced. By default, only gift coupons are removed
	 * @author Valérie Isaksen
	 * @return boolean True on success
	 */
	static public function setInUseCoupon($code, $in_use=true, $coupon_used = null){
		JPluginHelper::importPlugin('vmcoupon');
		$dispatcher = JDispatcher::getInstance();
		$returnValues = $dispatcher->trigger('plgVmCouponInUse', array($code));
		if(!empty($returnValues)){
			foreach ($returnValues as $returnValue) {
				if ($returnValue !== null  ) {
					return $returnValue;
				}
			}
		}
		$session = JFactory::getSession();
		if($coupon_used===null)$coupon_used = $session->getId();
		$db = JFactory::getDBO();
		if (!$in_use) {
			$db = JFactory::getDBO();
			$q = 'SELECT `coupon_used` '
				. 'FROM `#__virtuemart_coupons` '
				. 'WHERE `coupon_code` = "' . $db->escape($code) . '"';
			$db->setQuery($q);
			$coupon_session_id=$db->loadResult();
			if ($coupon_used !=$coupon_session_id) {
				return;
			}
			$coupon_used=0;
		}


		$q = 'UPDATE `#__virtuemart_coupons` SET `coupon_used` = "' . $coupon_used . '" WHERE `coupon_type`= \'gift\' AND `coupon_code` = "' . $db->escape($code) . '"';
		$db->setQuery($q);

		return ($db->query() !== false);
	}
}
