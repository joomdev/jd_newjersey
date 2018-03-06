<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: amazon.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2014 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */

class vmAmazonSession {

	public function incrementRetryInvalidPaymentMethodInSession() {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');
		$sessionAmazonData = json_decode($sessionAmazon, true);
		if(isset($sessionAmazonData['RetryInvalidPaymentMethod'])) {
			$sessionAmazonData['RetryInvalidPaymentMethod']++;
		} else {
			$sessionAmazonData['RetryInvalidPaymentMethod'] = 0;
		}
		$session->set('amazon', json_encode($sessionAmazonData), 'vm');

		return $sessionAmazonData['RetryInvalidPaymentMethod'];
	}

	public function getRetryInvalidPaymentMethodFromSession() {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');
		$sessionAmazonData = json_decode($sessionAmazon, true);
		if(isset($sessionAmazonData['RetryInvalidPaymentMethod'])) {
			return $sessionAmazonData['RetryInvalidPaymentMethod'];
		} else {
			return 0;
		}
	}

	public function saveAmazonOrderReferenceId(&$cart, $onlyDigitalGoods, $paymId) {

		$this->_amazonOrderReferenceId = vRequest::getString('session', '');
		self::setAmazonOrderReferenceIdInSession($this->_amazonOrderReferenceId, $onlyDigitalGoods, $paymId);
		//$cart->virtuemart_paymentmethod_id = vRequest::getInt('pm', $paymId);
		//$cart->setCartIntoSession();
		return $this->_amazonOrderReferenceId;
	}

	/**
	 * save the BT and ST in case the shopper has already given one
	 * @param $cart
	 */
	public function saveBTandSTInSession($cart) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');
		$sessionAmazonData = json_decode($sessionAmazon, true);
		// check if it is already saved or not
		if(!isset($sessionAmazonData['BT'])) {
			$sessionAmazonData['BT'] = $cart->BT;
			$sessionAmazonData['ST'] = $cart->ST;
			$session->set('amazon', json_encode($sessionAmazonData), 'vm');
		}

	}


	public function getBTandSTFromSession() {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');
		$address['BT'] = NULL;
		$address['ST'] = NULL;
		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
			if(isset($sessionAmazonData['BT']) OR isset($sessionAmazonData['ST'])) {
				$address['BT'] = $sessionAmazonData['BT'];
				$address['ST'] = $sessionAmazonData['ST'];
			}
		}

		return $address;
	}

	/**
	 * @return null
	 */
	public function getAmazonOrderReferenceIdWeightFromSession($paymId) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');

		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
			if(isset($sessionAmazonData[$paymId])) {
				return $sessionAmazonData[$paymId];
			}
		}

		return NULL;

	}

	/**
	 * @return null
	 */
	public function clearAmazonSession() {

		$session = JFactory::getSession();
		$session->clear('amazon', 'vm');

		return NULL;

	}

	/**
	 * @return null
	 */
	public function getAmazonSalesPriceFromSession($paymId) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');

		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
			if(isset($sessionAmazonData[$paymId])
			and isset($sessionAmazonData[$paymId]['_salesPrices'])
			) {
				return $sessionAmazonData[$paymId]['_salesPrices'];
			}
		}

		return NULL;

	}

	/**
	 * @param $salesPrices
	 */
	public function setSalesPriceInSession($salesPrices, $paymId) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');
		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
		} else {
			$sessionAmazonData = array();
		}

		$sessionAmazonData['virtuemart_paymentmethod_id'] = $paymId;
		$sessionAmazonData[$paymId]['_salesPrices'] = $salesPrices;
		$session->set('amazon', json_encode($sessionAmazonData), 'vm');

	}


	/**
	 * @return null
	 */
	public function getAmazonOrderReferenceIdFromSession($paymId) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');

		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
			if(isset($sessionAmazonData[$paymId])) {
				return $sessionAmazonData[$paymId]['_amazonOrderReferenceId'];
			}
		}

		return NULL;

	}

	/**
	 * @return null
	 */
	public function getisOnlyDigitalGoodsFromSession($paymId) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');

		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
			if(isset($sessionAmazonData[$paymId])) {
				return $sessionAmazonData[$paymId]['isOnlyDigitalGoods'];
			}
		}

		return NULL;

	}

	/**
	 * @param $amazonOrderReferenceId
	 * @param $isOnlyDigitalGoods
	 */
	public function setAmazonOrderReferenceIdInSession($amazonOrderReferenceId, $isOnlyDigitalGoods, $paymId) {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');
		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);
		} else {
			$sessionAmazonData = array();
		}


		$sessionAmazonData['virtuemart_paymentmethod_id'] = $paymId;
		$sessionAmazonData[$paymId]['_amazonOrderReferenceId'] = $amazonOrderReferenceId;
		$sessionAmazonData[$paymId]['isOnlyDigitalGoods'] = $isOnlyDigitalGoods;
		$session->set('amazon', json_encode($sessionAmazonData), 'vm');

	}

	//
	// Session functions
	//

	public function getDataFromSession() {
		$session = JFactory::getSession();
		$sessionAmazon = $session->get('amazon', 0, 'vm');

		if($sessionAmazon) {
			$sessionAmazonData = json_decode($sessionAmazon, true);

			return $sessionAmazonData;
		}

		return false;

	}
}