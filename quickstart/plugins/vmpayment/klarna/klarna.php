<?php

defined ('_JEXEC') or die();

/**
 * @version $Id: klarna.php 9560 2017-05-30 14:13:21Z Milbo $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

if (!class_exists('VmConfig')) {
	require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
}

if (!class_exists ('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

	require (VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
if (!class_exists ('Klarna')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarna.php');
}
if (!class_exists ('klarna_virtuemart')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_virtuemart.php');
}
if (!class_exists ('PCStorage')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'pclasses' . DS . 'storage.intf.php');
}

if (!class_exists ('KlarnaConfig')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaconfig.php');
}
if (!class_exists ('KlarnaPClass')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnapclass.php');
}
if (!class_exists ('KlarnaCalc')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnacalc.php');
}

if (!class_exists ('KlarnaHandler')) {
	require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
}

require_once (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'transport' . DS . 'xmlrpc-3.0.0.beta' . DS . 'lib' . DS . 'xmlrpc.inc');
require_once (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'transport' . DS . 'xmlrpc-3.0.0.beta' . DS . 'lib' . DS . 'xmlrpc_wrappers.inc');

if (is_file (VMKLARNA_CONFIG_FILE)) {
	require_once (VMKLARNA_CONFIG_FILE);
}

class plgVmPaymentKlarna extends vmPSPlugin {

	function __construct (& $subject, $config) {

		parent::__construct ($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$varsToPush = $this->getVarsToPush ();
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);

	}

	/**
	 * @return string
	 */
	public function getVmPluginCreateTableSQL () {

		return $this->createTableSQL ('Payment Klarna Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields () {

		$SQLfields = array(
			'id'                          => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'         => 'int(1) UNSIGNED',
			'order_number'                => ' char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name'                => 'varchar(5000)',
			'payment_order_total'         => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
			'payment_fee'                 => 'decimal(10,2)',
			'tax_id'                      => 'smallint(1)',
			'klarna_eid'                  => 'int(10)',
			'klarna_status_code'          => 'tinyint(4)',
			'klarna_status_text'          => 'varchar(255)',
			'klarna_invoice_no'           => 'varchar(255)',
			'klarna_log'                  => 'varchar(255)',
			'klarna_pclass'               => 'int(1)',
			'klarna_pdf_invoice'          => 'varchar(512)',
		);
		return $SQLfields;
	}

	/**
	 * @param $name
	 * @param $id
	 * @param $data
	 * @return bool
	 */
	function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}

	/**
	 * @param $name
	 * @param $id
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table) {

		return $this->setOnTablePluginParams ($name, $id, $table);
	}

	/**
	 * @param $product
	 * @param $productDisplay
	 * @return bool
	 */
	function plgVmOnProductDisplayPayment ($product, &$productDisplay) {

		$vendorId = 1;
		if ($this->getPluginMethods ($vendorId) === 0) {
			return FALSE;
		}
		if (!class_exists ('klarna_productPrice')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_productprice.php');
		}
		if (!class_exists ('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart ();

		foreach ($this->methods as $method) {
			$type = NULL;
			$cData = KlarnaHandler::getcData ($method, $this->getCartAddress ($cart, $type, FALSE));
			if ($cData['active'] and in_array ('part', $cData['payments_activated'])) {
				//if (!empty($product->prices)) { // no price is set
				if (!empty($product->prices['salesPrice'])) {
					$productPrice = new klarna_productPrice($cData);
					if ($productViewData = $productPrice->showProductPrice ($product, $cart)) {
						$productDisplayHtml = $this->renderByLayout ('productprice_layout', $productViewData, $method->payment_element, 'payment');
						$productDisplay[] = $productDisplayHtml;
					}
				}
			}
		}
		return TRUE;
	}

	/*
		*
		*/

	/**
	 * @param        $cart
	 * @param        $countryCode
	 * @param        $countryId
	 * @param string $fld
	 */
	function _getCountryCode ($cart = NULL, &$countryCode, &$countryId, $fld = 'country_3_code') {

		if ($cart == '') {
			if (!class_exists ('VirtueMartCart')) {
				require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			}
			$cart = VirtueMartCart::getCart ();
		}
		$type = NULL;
		$address = $this->getCartAddress ($cart, $type, FALSE);
		if (vRequest::getVar ('klarna_country_2_code') == 'se') {
			$countryId = ShopFunctions::getCountryIDByName ('se');
			$countryCode = shopFunctions::getCountryByID ($countryId, $fld);
		} elseif (!isset($address['virtuemart_country_id']) or empty($address['virtuemart_country_id'])) {
			$countryCode = KlarnaHandler::getVendorCountry ($fld);
			$countryId = ShopFunctions::getCountryIDByName ($countryCode);
		} else {
			$countryId = $address['virtuemart_country_id'];
			$countryCode = shopFunctions::getCountryByID ($address['virtuemart_country_id'], $fld);
		}
	}

	/**
	 * @param      $cart
	 * @param      $type
	 * @param bool $STsameAsBT
	 * @return mixed
	 */
	function getCartAddress ($cart, &$type, $STsameAsBT = TRUE) {

		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$st = $cart->BT;
			$type = 'BT';
			if ($STsameAsBT and $cart->ST and !$cart->STsameAsBT) {
				vmInfo (vmText::_ ('VMPAYMENT_KLARNA_SHIPTO_SAME_AS_BILLTO'));
				$cart->STsameAsBT = 1;
				$cart->setCartIntoSession ();
			}
		} elseif ($cart->BT == 0 or empty($cart->BT)) {
			$st = $cart->BT;
			$type = 'BT';
		} else {
			$st = $cart->ST;
			$type = 'ST';
		}
		return $st;
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param string         $fld
	 * @return null
	 */
	function _getCartAddressCountryId (VirtueMartCart $cart, $fld = 'country_3_code') {

		$type = "";
		$address = $this->getCartAddress ($cart, $type, FALSE);
		if (!isset($address['virtuemart_country_id'])) {
			return NULL;
		}
		return $address['virtuemart_country_id'];
	}

	/**
	 * @param        $virtuemart_order_id
	 * @param string $fld
	 * @return string
	 */
	function getCountryCodeByOrderId ($virtuemart_order_id, $fld = 'country_3_code') {

		$db = JFactory::getDBO ();
		$q = 'SELECT `virtuemart_country_id`,  `address_type` FROM #__virtuemart_order_userinfos  WHERE virtuemart_order_id=' . $virtuemart_order_id;

		$db->setQuery ($q);
		$results = $db->loadObjectList ();
		if (count ($results) == 1) {
			$virtuemart_country_id = $results[0]->virtuemart_country_id;
		} else {
			foreach ($results as $result) {
				if ($result->address_type == 'ST') {
					$virtuemart_country_id = $result->virtuemart_country_id;
					break;
				}
			}
		}
		return shopFunctions::getCountryByID ($virtuemart_country_id, $fld);
	}

	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the plugin methods in the cart (edit shipment/payment) for example
	 *
	 * @param object  $cart     Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 *         On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 */
	public function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		$html = $this->displayListFEPayment ($cart, $selected);
		if (!empty($html)) {
			$htmlIn[] = $html;
		}
	}


	/**
	 * @param VirtueMartCart $cart VirtueMartCart
	 * @param                $selected
	 * @return array|bool
	 */
	protected function displayListFEPayment (VirtueMartCart $cart, $selected) {

		if (!class_exists ('Klarna_payments')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_payments.php');
		}

		if ($this->getPluginMethods ($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication ();
				$app->enqueueMessage (vmText::_ ('COM_VIRTUEMART_CART_NO_' . strtoupper ($this->_psType)));
				return FALSE;
			} else {
				return FALSE;
			}
		}

		$html = array();
		foreach ($this->methods as $method) {
			$temp = $this->getListFEPayment ($cart, $method);
			if (!empty($temp)) {
				$html[] = $temp;
			}
		}
		if (!empty($html)) {
			$this->loadScriptAndCss ();
		}
		return $html;
	}


	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @return null|string
	 */
	protected function getListFEPayment (VirtueMartCart $cart, $method) {

		$cart_currency_code = ShopFunctions::getCurrencyByID ($cart->pricesCurrency, 'currency_code_3');
		$country_code = NULL;
		$countryId = 0;
		$this->_getCountryCode ($cart, $country_code, $countryId);
		if (!($cData = $this->checkCountryCondition ($method, $country_code, $cart))) {
			return NULL;
		}
		try {
			$pclasses = KlarnaHandler::getPClasses (NULL, KlarnaHandler::getKlarnaMode ($method, $cData['country_code_3']), $cData);
		}
		catch (Exception $e) {
			vmError ($e->getMessage (), $e->getMessage ());
			return NULL;
		}
		$specCamp = 0;
		$partPay = 0;
		$this->getNbPClasses ($pclasses, $specCamp, $partPay);
		$sessionKlarnaData = $this->getKlarnaSessionData ();

		$klarna_paymentmethod = "";
		if (isset($sessionKlarnaData->klarna_paymentmethod)) {
			$klarna_paymentmethod = $sessionKlarnaData->klarna_paymentmethod;
		}

		$html = '';
		$checked = 'checked="checked"';
		$payments = new klarna_payments($cData, KlarnaHandler::getShipToAddress ($cart));

		if (in_array ('invoice', $cData['payments_activated'])) {
			$payment_params = $payments->get_payment_params ($method, 'invoice', $cart);
			$payment_form = $this->renderByLayout ('payment_form',
				array('payment_params' => $payment_params,
					'payment_currency_info'       => $payment_params['payment_currency_info']
				)
			);
			$selected = ($klarna_paymentmethod == 'klarna_invoice' AND $method->virtuemart_paymentmethod_id == $cart->virtuemart_paymentmethod_id) ? $checked : "";
			$html .= $this->renderByLayout ('displaypayment', array(
				'stype'                       => 'invoice',
				'id'                          => $payment_params['id'],
				'module'                      => $payment_params['module'],
				'klarna_form'                 => $payment_form,
				'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id,
				'klarna_paymentmethod'        => $klarna_paymentmethod,
				'selected'                    => $selected
			));
		}

		if (in_array ('part', $cData['payments_activated'])) {
			if (strtolower ($country_code) == 'nld') {
				//  Since 12/09/12: merchants can sell goods with Klarna Invoice up to thousands of euros. So the price check has been moved here
				if (!KlarnaHandler::checkPartNLpriceCondition ($cart)) {
					// We can't show our payment options for Dutch customers
					// if price exceeds 250 euro. Will be replaced with ILT in
					// the future.
					$partPay = 0;
				}
			}
			if (!KlarnaHandler::checkPartpriceCondition ($cData, $cart)) {
				$partPay = 0;
			}

			if ($partPay > 0) {
				if ($payment_params = $payments->get_payment_params ($method, 'part', $cart, $cData['virtuemart_currency_id'], $cData['vendor_currency'])) {
					$payment_form = $this->renderByLayout ('payment_form', array('payment_params' => $payment_params, 'payment_currency_info'       => $payment_params['payment_currency_info'],), 'klarna', 'payment');
					$selected = ($klarna_paymentmethod == 'klarna_part' AND $method->virtuemart_paymentmethod_id == $cart->virtuemart_paymentmethod_id) ? $checked : "";
					$html .= $this->renderByLayout ('displaypayment', array(
						'stype'                       => 'part',
						'id'                          => $payment_params['id'],
						'module'                      => $payment_params['module'],
						'klarna_form'                 => $payment_form,
						'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id,
						'klarna_paymentmethod'        => $klarna_paymentmethod,
						'selected'                    => $selected
					));
				}
			}
		}
		// not tested yet
		/*
		if ( $specCamp > 0) {
			if ($payment_params = $payments->get_payment_params ($method, 'spec', $cart, $cData['virtuemart_currency_id'])) {
				$payment_form = $this->renderByLayout ('payment_form', array('payment_params' => $payment_params, 'payment_currency_info'       => $payment_params['payment_currency_info'],), 'klarna', 'payment');
				$selected = ($klarna_paymentmethod == 'klarna_spec' AND $method->virtuemart_paymentmethod_id == $cart->virtuemart_paymentmethod_id) ? $checked : "";
				$html .= $this->renderByLayout ('displaypayment', array(
					'stype'                       => 'spec',
					'id'                          => $payment_params['id'],
					'module'                      => $payment_params['module'],
					'klarna_form'                 => $payment_form,
					'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id,
					'klarna_paymentmethod'        => $klarna_paymentmethod,
					'selected'                    => $selected
				));
			}
		}
		*/
		return $html;
	}

	/**
	 * Count the number of Payment Classes: Partial Payments, and Special campaigns
	 *
	 * @param $pClasses
	 * @param $specCamp
	 * @param $partPay
	 */
	function getNbPClasses ($pClasses, &$specCamp, &$partPay) {

		$specCamp = 0;
		$partPay = 0;
		foreach ($pClasses as $pClass) {
			if ($pClass->getType () == KlarnaPClass::SPECIAL) {
				$specCamp += 1;
			}
			if ($pClass->getType () == KlarnaPClass::CAMPAIGN ||
				$pClass->getType () == KlarnaPClass::ACCOUNT ||
				$pClass->getType () == KlarnaPClass::FIXED ||
				$pClass->getType () == KlarnaPClass::DELAY
			) {
				$partPay += 1;
			}
		}
	}

	/**
	 * @return mixed|null
	 */
	function getKlarnaSessionData () {

		$session = JFactory::getSession ();
		$sessionKlarna = $session->get ('Klarna', 0, 'vm');
		if ($sessionKlarna) {
			$sessionKlarnaData = (object) json_decode ($sessionKlarna ,true);
			$sessionKlarnaData->KLARNA_DATA=(array)$sessionKlarnaData->KLARNA_DATA;
			return $sessionKlarnaData;
		}
		return NULL;
	}

	/**
	 * @param $method
	 * @param $country_code
	 * @param $cart
	 * @return array|bool|null
	 */
	function checkCountryCondition ($method, $country_code, $cart) {

		if (!class_exists ('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$active_country = "klarna_active_" . strtolower ($country_code);
		if (!isset($method->$active_country) or !$method->$active_country) {
			return FALSE;
		}
		if (empty($country_code)) {
			$msg = vmText::_ ('VMPAYMENT_KLARNA_GET_SWEDISH_ADDRESS');
			$country_code = "swe";
			vmWarn ($msg);
			//return false;
		}
		/*
		if (strtolower ($country_code) == 'nld') {
			if(! KlarnaHandler::checkPartNLpriceCondition ($cart)) {
				// We can't show our payment options for Dutch customers
			// if price exceeds 250 euro. Will be replaced with ILT in
			// the future.
			return FALSE;
			}
		}
		*/
		// Get the country settings
		if (!class_exists ('KlarnaHandler')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
		}

		$cData = KlarnaHandler::getCountryData ($method, $country_code);
		if ($cData['eid'] == '' || $cData['eid'] == 0) {
			return FALSE;
		}

		return $cData;
	}

	/**
	 * @param $cart
	 * @param $order
	 * @return bool|null
	 */
	function plgVmConfirmedOrder ($cart, $order) {

		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}
		$this->setInConfirmOrder($cart);
		$sessionKlarnaData = $this->getKlarnaSessionData ();

		try {
			$result = KlarnaHandler::addTransaction ($method, $order, $sessionKlarnaData->KLARNA_DATA['pclass']);
		}
		catch (Exception $e) {
			$log = $e->getMessage ();
			vmError ($e->getMessage () . ' #' . $e->getCode (), $e->getMessage () . ' #' . $e->getCode ());
			return;
			//KlarnaHandler::redirectPaymentMethod('error', $e->getMessage() . ' #' . $e->getCode());
		}
		//vmdebug('addTransaction result', $result);
		// Delete all Klarna data
		//unset($sessionKlarnaData->KLARNA_DATA, $_SESSION['SSN_ADDR']);
		$shipTo = KlarnaHandler::getShipToAddress ($cart);
		$modelOrder = VmModel::getModel ('orders');
		if ($result['status_code'] == KlarnaFlags::DENIED) {
			$order['customer_notified'] = 0;
			$order['order_status'] = $method->status_denied;
			$order['comments'] = vmText::sprintf ('VMPAYMENT_KLARNA_PAYMENT_KLARNA_STATUS_DENIED');
			if ($method->delete_order) {
				$order['comments'] .= "<br />" . $result['status_text'];
			}
			$modelOrder->updateStatusForOneOrder ($order['details']['BT']->virtuemart_order_id, $order, TRUE);
			vmdebug ('addTransaction remove order?', $method->delete_order);
			if ($method->delete_order) {
				$modelOrder->remove (array('virtuemart_order_id' => $order['details']['BT']->virtuemart_order_id));
			} else {
				$dbValues['order_number'] = $order['details']['BT']->order_number;
				$dbValues['payment_name'] = $this->renderKlarnaPluginName ($method, $order['details']['BT']->virtuemart_country_id, $shipTo, $order['details']['BT']->order_total, $order['order_currency']);
				$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
				$dbValues['order_payment'] = $order['details']['BT']->order_payment;
				$dbValues['klarna_pclass'] = $sessionKlarnaData->KLARNA_DATA['PCLASS'];
				$dbValues['klarna_log'] = '';
				$dbValues['klarna_status_code'] = $result['status_code'];
				$dbValues['klarna_status_text'] = $result['status_text'];
				$this->storePSPluginInternalData ($dbValues);
			}
			$app = JFactory::getApplication ();
			$app->enqueueMessage ($result['status_text']);
			$app->redirect (JRoute::_ ('index.php?option=com_virtuemart&view=cart&task=editpayment'));
		} else {
			$invoiceno = $result[1];
			if ($invoiceno && is_numeric ($invoiceno)) {
				//Get address id used for this order.
				//$country = $sessionKlarnaData->KLARNA_DATA['country'];
				// $lang = KlarnaHandler::getLanguageForCountry($method, KlarnaHandler::convertToThreeLetterCode($country));
				// $d['order_payment_name'] = $kLang->fetch('MODULE_INVOICE_TEXT_TITLE', $lang);
				// Add a note in the log
				$log = vmText::sprintf ('VMPAYMENT_KLARNA_INVOICE_CREATED_SUCCESSFULLY', $invoiceno);

				// Prepare data that should be stored in the database
				$dbValues['order_number'] = $order['details']['BT']->order_number;
				$dbValues['payment_name'] = $this->renderKlarnaPluginName ($method, $order['details']['BT']->virtuemart_country_id, $shipTo, $order['details']['BT']->order_total, $order['details']['BT']->order_currency);
				$dbValues['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
				$dbValues['order_payment'] = $order['details']['BT']->order_payment;
				$dbValues['order_payment_tax'] = $order['details']['BT']->order_payment_tax;
				$dbValues['klarna_pclass'] = $sessionKlarnaData->KLARNA_DATA['pclass'];
				$dbValues['klarna_invoice_no'] = $invoiceno;
				$dbValues['klarna_log'] = $log;
				$dbValues['klarna_eid'] = $result['eid'];
				$dbValues['klarna_status_code'] = $result['status_code'];
				$dbValues['klarna_status_text'] = $result['status_text'];

				$this->storePSPluginInternalData ($dbValues);

				/*
				 * Klarna's order status
				 *  Integer - 1,2 or 3.
				 *  1 = OK: KlarnaFlags::ACCEPTED
				 *  2 = Pending: KlarnaFlags::PENDING
				 *  3 = Denied: KlarnaFlags::DENIED
				 */
				if ($result['status_code'] == KlarnaFlags::PENDING) {
					/* if Klarna's order status is pending: add it in the history */
					/* The order is under manual review and will be accepted or denied at a later stage.
											Use cronjob with checkOrderStatus() or visit Klarna Online to check to see if the status has changed.
											You should still show it to the customer as it was accepted, to avoid further attempts to fraud. */
					$order['order_status'] = $method->status_pending;
				} else {
					$order['order_status'] = $method->status_success;
				}
				$order['customer_notified'] = 1;
				$order['comments'] = $log;
				$modelOrder->updateStatusForOneOrder ($order['details']['BT']->virtuemart_order_id, $order, TRUE);

				$html = $this->renderByLayout ('orderdone', array(
					'payment_name'     => $dbValues['payment_name'],
					'klarna_invoiceno' => $invoiceno));

				if ($result['eid'] == VMPAYMENT_KLARNA_MERCHANT_ID_DEMO) {
					$html .= "<br />" . vmText::_ ('VMPAYMENT_KLARNA_WARNING') . "<br />";
				}

				$session = JFactory::getSession ();
				$session->clear ('Klarna', 'vm');
				//We delete the old stuff

				$cart->emptyCart ();
				vRequest::setVar ('html', $html);
				return TRUE;
			} else {
				vmError ('Error with invoice number');
			}
		}
	}

	/**
	 * @param $orderDetails
	 * @param $data
	 * @return null
	 */
	function plgVmOnUserInvoice ($orderDetails, &$data) {

		if (!($method = $this->getVmPluginMethod ($orderDetails['virtuemart_paymentmethod_id']))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return NULL;
		}
		$data['invoice_number'] = 'reservedByPayment_' . $orderDetails['order_number']; // Nerver send the invoice via email
	}



	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmGetPaymentCurrency ($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		$paymentCurrencyId = $this->getKlarnaPaymentCurrency ($method);
	}

	/**
	 * @param $method
	 * @return int
	 */
	function getKlarnaPaymentCurrency ($method) {

		if (!class_exists ('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart (FALSE);
		$country = NULL;
		$countryId = 0;
		$this->_getCountryCode ($cart, $country, $countryId);
		$cData = KlarnaHandler::countryData ($method, $country);
		return shopFunctions::getCurrencyIDByName ($cData['currency_code']);
	}


	/**
	 *
	 * An order gets cancelled, because order status='X'
	 *
	 * @param $order
	 * @param $old_order_status
	 * @return bool|null
	 */
	function plgVmOnCancelPayment ($order, $old_order_status) {

		if (!$this->selectedThisByMethodId ($order->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($method = $this->getVmPluginMethod ($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($payments = $this->_getKlarnaInternalData ($order->virtuemart_order_id))) {
			vmError (vmText::sprintf ('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id));
			return NULL;
		}
		// Status code is === 3==> active invoice. Cannot be be deleted
		// the invoice is active
		//if ($order->order_status == $method->status_success) {
		if ($invNo = $this->_getKlarnaInvoiceNo ($payments)) {
			//vmDebug('order',$order);return;
			$country = $this->getCountryCodeByOrderId ($order->virtuemart_order_id);
			$klarna = new Klarna_virtuemart();
			$cData = KlarnaHandler::countryData ($method, $country);
			$klarna->config ($cData['eid'], $cData['secret'], $cData['country_code'], NULL, $cData['currency_code'], $cData['mode']);
			try {
				//remove a passive invoice from Klarna.
				$result = $klarna->deleteInvoice ($invNo);
				if ($result) {
					$message = vmText::_ ('VMPAYMENT_KLARNA_INVOICE_DELETED') . ":" . $invNo;
				} else {
					$message = vmText::_ ('VMPAYMENT_KLARNA_INVOICE_NOT_DELETED') . ":" . $invNo;
				}
				$dbValues['order_number'] = $order->order_number;
				$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
				$dbValues['virtuemart_paymentmethod_id'] = $order->virtuemart_paymentmethod_id;
				$dbValues['klarna_invoice_no'] = 0; // it has been deleted
				$dbValues['klarna_pdf_invoice'] = 0; // it has been deleted
				$dbValues['klarna_log'] = $message;
				$dbValues['klarna_eid'] = $cData['eid'];
				$this->storePSPluginInternalData ($dbValues);
				VmInfo ($message);
			}
			catch (Exception $e) {
				$log = $e->getMessage () . " (#" . $e->getCode () . ")";
				if ($e->getCode () == '8113') {
					VmError ('invoice_not_passive');
				}
				if ($e->getCode () != 8101) { // unkown_order
					$this->_updateKlarnaInternalData ($order, $log, $invNo);
					VmError ($e->getMessage () . " (#" . $e->getCode () . ")");
					return FALSE;
				}

			}
		}

		return TRUE;
	}

	/**
	 * @param        $payments
	 * @param string $primaryKey
	 * @return mixed
	 */
	function _getKlarnaInvoiceNo ($payments, &$primaryKey = '') {

		$nb = count ($payments);
		$primaryKey = $payments[$nb - 1]->id;
		return $payments[$nb - 1]->klarna_invoice_no;
	}


	/**
	 * @param $payments
	 * @return mixed
	 */
	function _getKlarnaPlcass ($payments) {

		$nb = count ($payments);
		return $payments[$nb - 1]->klarna_pclass;
	}

	/**
	 * @param $payments
	 * @return mixed
	 */
	function _getKlarnaStatusCode ($payments) {

		$nb = count ($payments);
		return $payments[$nb - 1]->klarna_status_code;
	}

	/**
	 * @param $type
	 * @param $name
	 * @param $render
	 */
	/*
	function plgVmOnSelfCallFE ($type, $name, &$render) {
		if ($name != $this->_name || $type != 'vmpayment') {
            return FALSE;
        }
		//Klarna Ajax
		require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/klarna_ajax.php');

		if (!class_exists ('VmModel')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmmodel.php');
		}
		$model = VmModel::getModel ('paymentmethod');
		$payment = $model->getPayment ();
		if (!class_exists ('vmParameters')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'parameterparser.php');
		}
		$parameters = new vmParameters($payment, $payment->payment_element, 'plugin', 'vmpayment');
		$method = $parameters->getParamByName ('data');

		$country = vRequest::getWord ('country');
		$country = KlarnaHandler::convertToThreeLetterCode ($country);

		if (!class_exists ('klarna_virtuemart')) {
			require (JPATH_VMKLARNAPLUGIN . '/klarna/helpers/klarna_virtuemart.php');
		}

		$settings = KlarnaHandler::getCountryData ($method, $country);

		$klarna = new Klarna_virtuemart();
		$klarna->config ($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], KlarnaHandler::getKlarnaMode ($method, $settings['country_code_3']), VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type (), TRUE);

		$SelfCall = new KlarnaAjax($klarna, (int)$settings['eid'], JPATH_VMKLARNAPLUGIN, Juri::base ());
		$action = vRequest::getWord ('action');
		$jlang = JFactory::getLanguage ();
		$currentLang = substr ($jlang->getDefault (), 0, 2);
		$newIso = vRequest::getWord ('newIso');
		if ($currentLang != $newIso) {
			$iso = array(
				"sv" => "sv-SE",
				"da" => "da-DK",
				"en" => "en-GB",
				"de" => "de-DE",
				"nl" => "nl-NL",
				"nb" => "nb-NO",
				"fi" => "fi-FI");
			if (array_key_exists ($newIso, $iso)) {
				$jlang->load ('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $iso[$newIso], TRUE);
			}
		}
		echo $SelfCall->$action();
		jexit ();
	}
*/
	/**
	 * @author Patrick Kohl
	 * @param $type
	 * @param $name
	 * @param $render
	 */
	function plgVmOnSelfCallBE ($type, $name, &$render) {
		if ($name != $this->_name || $type != 'vmpayment') {
            return FALSE;
        }
		// fetches PClasses From XML file
		$call = vRequest::getWord ('call');
		$this->$call();
		// 	jexit();
	}


	/**
	 *
	 * Download Pdf Invoice
	 *
	 * @author Valérie Isaksen
	 *
	 */

	/**
	 * @return int|null|string
	 */
	function downloadInvoicePdf () {

		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		if (!class_exists ('JFile')) {
			require(JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'filesystem' . DS . 'file.php');
		}

		$payment_methodid = vRequest::getInt ('payment_methodid');
		$orderNumber = vRequest::getString ('order_number');
		$orderPass = vRequest::getString ('order_pass');

		if (!($method = $this->getVmPluginMethod ($payment_methodid))) {
			return NULL; // Another method was selected, do nothing
		}
		$modelOrder = VmModel::getModel ('orders');
		// If the user is not logged in, we will check the order number and order pass
		$virtuemart_order_id = $modelOrder->getOrderIdByOrderPass ($orderNumber, $orderPass);
		if (empty($virtuemart_order_id)) {
			VmError ('Invalid order_number/password ' . vmText::_ ('COM_VIRTUEMART_RESTRICTED_ACCESS'), 'Invalid order_number/password ' . vmText::_ ('COM_VIRTUEMART_RESTRICTED_ACCESS'));
			return 0;
		}
		if (!($payments = $this->_getKlarnaInternalData ($virtuemart_order_id))) {
			return '';
		}
		foreach ($payments as $payment) {
			if (!empty($payment->klarna_pdf_invoice)) {
				$path = VmConfig::get ('forSale_path', 0);
				$path .= DS . 'invoices' . DS;
				$fileName = $path . $payment->klarna_pdf_invoice;
				break;
			}
		}
		if (file_exists ($fileName)) {
			header ("Cache-Control: public");
			header ("Content-Transfer-Encoding: binary\n");
			header ('Content-Type: application/pdf');
			$contentDisposition = 'attachment';
			$agent = strtolower ($_SERVER['HTTP_USER_AGENT']);
			if (strpos ($agent, 'msie') !== FALSE) {
				$fileName = preg_replace ('/\./', '%2e', $fileName, substr_count ($fileName, '.') - 1);
			}

			header ("Content-Disposition: $contentDisposition; filename=\"$payment->klarna_pdf_invoice\"");
			$contents = file_get_contents ($fileName);
			echo $contents;
		}

		return;
	}

	/*
	 * @author Valérie Isaksen
	 *
		  * @return int|null
		  */
	function checkOrderStatus () {

		if (!class_exists ('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}

		$payment_methodid = vRequest::getInt ('payment_methodid');
		//$invNo = vRequest::getWord ('invNo');
		$orderNumber = vRequest::getString ('order_number');
		$orderPass = vRequest::getString ('order_pass');

		if (!($method = $this->getVmPluginMethod ($payment_methodid))) {
			return NULL; // Another method was selected, do nothing
		}

		$modelOrder = VmModel::getModel ('orders');
		// If the user is not logged in, we will check the order number and order pass
		$orderId = $modelOrder->getOrderIdByOrderPass ($orderNumber, $orderPass);
		if (empty($orderId)) {
			VmError ('Invalid order_number/password ' . vmText::_ ('COM_VIRTUEMART_RESTRICTED_ACCESS'), 'Invalid order_number/password ' . vmText::_ ('COM_VIRTUEMART_RESTRICTED_ACCESS'));
			return 0;
		}
		if (!($payments = $this->_getKlarnaInternalData ($orderId))) {
			return '';
		}
		$invNo = $this->_getKlarnaInvoiceNo ($payments);
		$country = $this->getCountryCodeByOrderID ($orderId);
		$settings = KlarnaHandler::countryData ($method, $country);
		$klarna_order_status = KlarnaHandler::checkOrderStatus ($settings, KlarnaHandler::getKlarnaMode ($method, $settings['country_code_3']), $orderNumber);
		vmdebug ('Klarna status', $klarna_order_status, $invNo);
		if ($klarna_order_status == KlarnaFlags::ACCEPTED) {
			/* if Klarna's order status is pending: add it in the history */
			/* The order is under manual review and will be accepted or denied at a later stage.
							Use cronjob with checkOrderStatus() or visit Klarna Online to check to see if the status has changed.
							You should still show it to the customer as it was accepted, to avoid further attempts to fraud. */
			$order['order_status'] = $method->status_success;
			$order['comments'] = vmText::_ ('VMPAYMENT_KLARNA_PAYMENT_ACCEPTED');
			$order['customer_notified'] = 0;
			$dbValues['klarna_log'] = vmText::_ ('VMPAYMENT_KLARNA_PAYMENT_ACCEPTED');
		} elseif ($klarna_order_status == KlarnaFlags::DENIED) {
			$order['order_status'] = $method->status_denied;
			$order['customer_notified'] = 1;
			$dbValues['klarna_log'] = vmText::_ ('VMPAYMENT_KLARNA_PAYMENT_NOT_ACCEPTED');
			$order['comments'] = vmText::_ ('VMPAYMENT_KLARNA_PAYMENT_NOT_ACCEPTED');
		} else {
			if ($klarna_order_status == KlarnaFlags::PENDING) {
				$dbValues['klarna_log'] = vmText::_ ('VMPAYMENT_KLARNA_PAYMENT_PENDING');
			} else {
				$dbValues['klarna_log'] = $klarna_order_status;
			}
			$order['comments'] = $dbValues['klarna_log'];
			$order['customer_notified'] = 0;
		}
		$dbValues['order_number'] = $orderNumber;
		$dbValues['virtuemart_order_id'] = $orderId;
		$dbValues['virtuemart_paymentmethod_id'] = $payment_methodid;
		$dbValues['klarna_invoice_no'] = $invNo;
		$this->storePSPluginInternalData ($dbValues);

		$modelOrder->updateStatusForOneOrder ($orderId, $order, FALSE);
		$app = JFactory::getApplication ();
		$app->redirect ('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $orderId);
		// 	jexit();
	}

	/**
	 * @param $virtuemart_order_id
	 * @return mixed|string
	 */
	function _getTablepkeyValue ($virtuemart_order_id) {

		$db = JFactory::getDBO ();
		$q = 'SELECT ' . $this->_tablepkey . ' FROM `' . $this->_tablename . '` ' . 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($q);

		if (!($pkey = $db->loadResult ())) {
			JError::raiseWarning (500, $db->getErrorMsg ());
			return '';
		}
		return $pkey;
	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $payment_method_id, $order) {

		if (!($this->selectedThisByMethodId ($payment_method_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($payments = $this->_getKlarnaInternalData ($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		if (!($method = $this->getVmPluginMethod ($payment_method_id))) {
			return NULL; // Another method was selected, do nothing
		}
		$html = '<table class="adminlist table" >' . "\n";
		$html .= $this->getHtmlHeaderBE ();

		$code = "klarna_";
		$class = 'class="row1"';
		foreach ($payments as $payment) {

			$html .= '<tr class="row1"><td>' . vmText::_ ('VMPAYMENT_KLARNA_DATE') . '</td><td align="left">' . $payment->created_on . '</td></tr>';
			//$html .= $this->getHtmlRow('KLARNA_DATE', "<strong>".$payment->created_on."</strong>", $class);
			if ($payment->payment_name) {
				$html .= $this->getHtmlRowBE ('KLARNA_PAYMENT_NAME', $payment->payment_name);
			}
			foreach ($payment as $key => $value) {
				if ($value) {
					if (substr ($key, 0, strlen ($code)) == $code) {
						if ($key == 'klarna_pdf_invoice' and !empty($value)) {
							// backwards compatible
							if (false) {
								$invoicePdfLink = JURI::root () . 'administrator/index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarna&call=downloadInvoicePdf&payment_methodid=' . (int)$payment_method_id . '&order_number=' . $order['details']['BT']->order_number . '&order_pass=' . $order['details']['BT']->order_pass;
								$value = '<a target="_blank" href="' . $invoicePdfLink . '">' . vmText::_ ('VMPAYMENT_KLARNA_DOWNLOAD_INVOICE') . '</a>';
							} else {
								$value = '<a target="_blank" href="' . $value . '">' . vmText::_('VMPAYMENT_KLARNA_VIEW_INVOICE') . '</a>';

							}

						}
						$html .= $this->getHtmlRowBE ($key, $value);
					}
				}
			}
		}

		if ($order['details']['BT']->order_status == $method->status_pending) {
			if (!class_exists ('VirtueMartModelOrders')) {
				require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
			}

			$country = $this->getCountryCodeByOrderId ($virtuemart_order_id);
			$invNo = $this->_getKlarnaInvoiceNo ($payments);
			vmDebug ('plgVmOnShowOrderBEPayment', $invNo);
			$checkOrderStatus = JURI::root () . 'administrator/index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarna&call=checkOrderStatus&payment_methodid=' . (int)$payment_method_id . '&order_number=' . $order['details']['BT']->order_number . '&order_pass=' . $order['details']['BT']->order_pass . '&country=' . $country;

			$link = '<a href="' . $checkOrderStatus . '">' . vmText::_ ('VMPAYMENT_KLARNA_GET_NEW_STATUS') . '</a>';
			$html .= $this->getHtmlRowBE ('KLARNA_PAYMENT_CHECK_ORDER_STATUS', $link);
		}
		$html .= '</table>' . "\n";
		return $html;
	}

	/**
	 * @param        $virtuemart_order_id
	 * @param string $order_number
	 * @return mixed|string
	 */
	function _getKlarnaInternalData ($virtuemart_order_id, $order_number = '') {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		if ($order_number) {
			$q .= " `order_number` = '" . $order_number . "'";
		} else {
			$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
		}

		$db->setQuery ($q);
		if (!($payments = $db->loadObjectList ())) {
			return '';
		}
		return $payments;
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @param                $cart_prices
	 * @return int
	 */
	function getCosts (VirtueMartCart $cart, $method, $cart_prices) {

		$country_code = NULL;
		$countryId = 0;
		$this->_getCountryCode ($cart, $country_code, $countryId);
		return KlarnaHandler::getInvoiceFee ($method, $country_code);
	}

	/**
	 * Save updated order data to the method specific table
	 *
	 * @param array $order Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.

	 */
	public function plgVmOnUpdateOrderPayment (&$order, $old_order_status) {

		/*if (!$this->selectedThisByMethodId ($order->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}*/

		if (!($method = $this->getVmPluginMethod ($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!$this->selectedThisElement($method->payment_element)) {
			return NULL;
		}

		if (!($payments = $this->_getKlarnaInternalData ($order->virtuemart_order_id))) {
			vmError (vmText::sprintf ('VMPAYMENT_KLARNA_ERROR_NO_DATA', $order->virtuemart_order_id));
			return NULL;
		}

		if (!($invNo = $this->_getKlarnaInvoiceNo ($payments))) {
			return NULL;
		}
		// to activate the order
		if ($order->order_status == $method->status_shipped) {
			$country = $this->getCountryCodeByOrderId ($order->virtuemart_order_id);
			$klarna_vm = new Klarna_virtuemart();
			$cData = KlarnaHandler::countryData ($method, $country);
			/*
		   * The activateInvoice function is used to activate a passive invoice.
		   * Please note that this function call cannot activate an invoice created in test mode.
		   * It is however possible to manually activate that type of invoices.
		   */
			$force_emailInvoice = FALSE;
			$klarna_vm->config ($cData['eid'], $cData['secret'], $cData['country_code'], NULL, $cData['currency_code'], KlarnaHandler::getKlarnaMode ($method, $cData['country_code_3']));

			try {
				//You can specify a new pclass ID if the customer wanted to change it before you activate.

				$klarna_vm->activateInvoice ($invNo);
				$invoice_url=$this->getInvoice($invNo, $invoice_url);

				//The url points to a PDF file for the invoice.
				//Invoice activated, proceed accordingly.
			}
			catch (Exception $e) {
				$log = $e->getMessage () . " (#" . $e->getCode () . ")";
				if ($e->getCode () != 8111) { // (invoice_not_passive_or_frozen)
					$this->_updateKlarnaInternalData ($order, $log);
					VmError ($e->getMessage () . " (#" . $e->getCode () . ")");
					return FALSE;
				} else {
					$force_emailInvoice = TRUE;
				}
			}

			$emailInvoice = $this->emailInvoice ($method, $klarna_vm, $invNo, $force_emailInvoice);
			$dbValues['order_number'] = $order->order_number;
			$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
			$dbValues['virtuemart_paymentmethod_id'] = $order->virtuemart_paymentmethod_id;
			$dbValues['klarna_invoice_no'] = $invNo;
			$dbValues['klarna_log'] = vmText::sprintf ('VMPAYMENT_KLARNA_ACTIVATE_INVOICE', $invNo);
			if ($emailInvoice) {
				$dbValues['klarna_log'] .= "<br />" . vmText::sprintf ('VMPAYMENT_KLARNA_EMAIL_INVOICE', $invNo);
			} else {
				$dbValues['klarna_log'] .= "<br />" . vmText::_ ('VMPAYMENT_KLARNA_EMAIL_INVOICE_NOT_SENT');
			}
			$dbValues['klarna_eid'] = $cData['eid'];
			//$dbValues['klarna_status_code'] = KLARNA_INVOICE_ACTIVE; // Invoice is active
			//$dbValues['klarna_status_text'] = '';
			$dbValues['klarna_pdf_invoice'] = $invoice_url;

			$this->storePSPluginInternalData ($dbValues);
			return TRUE;
		}
		return NULL;
	}
	/**
	 * @param $klarna_invoice_pdf
	 * @param $vm_invoice_name
	 * @return bool
	 */
	function getInvoice ($invoice_number, &$vm_invoice_name) {


		//$klarna_invoice = explode ('/', $klarna_invoice_pdf);
		if ($this->method->server =='live') {
			$klarna_invoice_name = "https://online.klarna.com/invoices/" . $invoice_number . '.pdf';
		} else {
			$klarna_invoice_name = "https://online.testdrive.klarna.com/invoices/" . $invoice_number . '.pdf';
		}

		$vm_invoice_name = 'klarna_' . $invoice_number . '.pdf';

		return $klarna_invoice_name;
	}
	private function emailInvoice ($method, $klarna_vm, $invNo, $force_emailInvoice) {

		if ($method->send_invoice or $force_emailInvoice) {

			try {
				$result = $klarna_vm->emailInvoice ($invNo);

				/* Invoice sent to customer via email, proceed accordingly.
					   $result contains the invoice number of the emailed invoice. */
			}
			catch (Exception $e) {
				//Something went wrong, print the message:
				VmError ($e->getMessage () . " (#" . $e->getCode () . ")");
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param $order
	 * @param $log
	 */
	function _updateKlarnaInternalData ($order, $log) {

		$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
		$dbValues['order_number'] = $order->order_number;
		$dbValues['klarna_log'] = $log;
		$this->storePSPluginInternalData ($dbValues);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id) {

		if ($jplugin_id != $this->_jid) {
			return FALSE;
		}
		if (!class_exists ('JFile')) {
			require(JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'filesystem' . DS . 'file.php');
		}
		/*
		 * if the file Klarna.cfg does not exist, then create it
		 */
		$filename = VMKLARNA_CONFIG_FILE;
		if (!JFile::exists ($filename)) {
			$fileContents = "<?php defined('_JEXEC') or die();
	define('VMKLARNA_SHIPTO_SAME_AS_BILLTO', '1');
	define('VMKLARNA_SHOW_PRODUCTPRICE', '1');
	?>";
			$result = JFile::write ($filename, $fileContents);
			if (!$result) {
				VmError (vmText::sprintf ('VMPAYMENT_KLARNA_CANT_WRITE_CONFIG', $filename, $result));
			}
		}

		$method = $this->getPluginMethod (vRequest::getInt ('virtuemart_paymentmethod_id'));
		if (KlarnaHandler::createKlarnaFolder ()) {
			$results = KlarnaHandler::fetchAllPClasses ($method);
			if (is_array ($results) and $results['msg']) {
				vmError ($results['msg']);
			}
		}
		vmDebug ('PClasses fetched for : ', $results['notice']);

		// we have to check that the following Shopper fields are there
		$required_shopperfields_vm = Klarnahandler::getKlarnaVMGenericShopperFields ();

		$required_shopperfields_bycountry = KlarnaHandler::getKlarnaSpecificShopperFields ();

		$userFieldsModel = VmModel::getModel ('UserFields');
		$switches['published'] = TRUE;
		$userFields = $userFieldsModel->getUserFields ('', $switches);
		// TEST that all Vm shopperfields are there
		foreach ($userFields as $userField) {
			$fields_name_vm[] = $userField->name;
		}
		$result = array_intersect ($fields_name_vm, $required_shopperfields_vm);
		$vm_required_not_found = array_diff ($required_shopperfields_vm, $result);
		if (count ($vm_required_not_found)) {
			VmError (vmText::sprintf ('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_NOT_FOUND', implode (", ", $vm_required_not_found)));
		} else {
			VmInfo (vmText::_ ('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_OK'));
		}
		vmLanguage::loadJLang('com_virtuemart_shoppers', true);

		$klarna_required_not_found = array();
		// TEST that all required Klarna shopper fields are there, if not create them
		foreach ($required_shopperfields_bycountry as $key => $shopperfield_country) {
			$active = 'klarna_active_' . strtolower ($key);
			if ($method->$active) {
				$resultByCountry = array_intersect ($fields_name_vm, $shopperfield_country);
				$klarna_required_country_not_found = array_diff ($shopperfield_country, $resultByCountry);
				$klarna_required_not_found = array_merge ($klarna_required_country_not_found, $klarna_required_not_found);
			}
		}
		$klarna_required_not_found = array_unique ($klarna_required_not_found, SORT_STRING);
		if (count ($klarna_required_not_found)) {
			VmError (vmText::sprintf ('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_NOT_FOUND', implode (", ", $klarna_required_not_found)));
			$shopperFieldsType = KlarnaHandler::getKlarnaShopperFieldsType ();
			$userfieldsModel = VmModel::getModel ('userfields');
			$data['virtuemart_userfield_id'] = 0;

			$data['published'] = 1;
			$data['required'] = 0;
			$data['account'] = 1;
			$data['shipment'] = 0;
			$data['vNames'] = array();
			$data['vValues'] = array();
			vmLanguage::loadJLang('com_virtuemart_shoppers', true);

			foreach ($klarna_required_not_found as $requiredfield) {
				$data['name'] = $requiredfield;
				$data['type'] = $shopperFieldsType[$requiredfield];
				$data['title'] = strtoupper ('COM_VIRTUEMART_SHOPPER_FORM_' . $requiredfield);
				$ret = $userfieldsModel->store ($data);
				if (!$ret) {
					vmError (vmText::_ ('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_ERROR_STORING') . $requiredfield);
				} else {
					vmInfo (vmText::_ ('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_CREATE_OK') . $requiredfield);
				}
			}

		} else {
			VmInfo (vmText::_ ('VMPAYMENT_KLARNA_REQUIRED_USERFIELDS_OK'));
		}

		$result = $this->onStoreInstallPluginTable ($jplugin_id);

		return $result;
	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart, &$msg) {

		if (!$this->selectedThisByMethodId ($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($method = $this->getVmPluginMethod ($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!class_exists ('KlarnaAddr')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');
		}

		$session = JFactory::getSession ();
		$sessionKlarna = new stdClass();
		$errors = array();
		$klarnaData_paymentmethod = vRequest::getVar ('klarna_paymentmethod', '');
		if ($klarnaData_paymentmethod == 'klarna_invoice') {
			$sessionKlarna->klarna_option = 'invoice';
		} elseif ($klarnaData_paymentmethod == 'klarna_partPayment') {
			$sessionKlarna->klarna_option = 'part';
		} elseif ($klarnaData_paymentmethod == 'klarna_speccamp') {
			$sessionKlarna->klarna_option = 'spec';
		} else {
			return NULL;

		}

		// Store payment_method_id so we can activate the
		// right payment in case something goes wrong.
		$sessionKlarna->virtuemart_payment_method_id = $cart->virtuemart_paymentmethod_id;
		$sessionKlarna->klarna_paymentmethod = $klarnaData_paymentmethod;
		$country3 = NULL;
		$countryId = 0;
		$this->_getCountryCode ($cart, $country3, $countryId, 'country_3_code');
		// $country2=  strtolower($country2);
		if (empty($country3)) {
			$country3 = "SWE";
			$countryId = ShopFunctions::getCountryIDByName ($country3);
		}

		$cData = KlarnaHandler::countryData ($method, strtoupper ($country3));

		$klarnaData = KlarnaHandler::getDataFromEditPayment ();

		if ($msg = KlarnaHandler::checkDataFromEditPayment ($klarnaData, $cData['country_code_3'])) {
			//vmInfo($msg); // meanwhile the red baloon works
			$session->set ('Klarna', json_encode ($sessionKlarna), 'vm');
			return FALSE;
		}

		$klarnaData['country'] = $cData['country_code'];
		$klarnaData['country3'] = $cData['country_code_3'];

		//$country = $cData['country_code']; //KlarnaHandler::convertCountry($method, $country2);
		//$lang = $cData['language_code']; //KlarnaHandler::getLanguageForCountry($method, $country);
		// Get the correct data
		//Removes spaces, tabs, and other delimiters.
		// If it is a swedish customer we use the information from getAddress
		if (strtolower ($cData['country_code']) == "se") {
			$swedish_addresses = KlarnaHandler::getAddresses ($klarnaData['socialNumber'], $cData, $method);
			if (empty($swedish_addresses)) {
				$msg = vmText::_ ('VMPAYMENT_KLARNA_ERROR_TITLE_2');
				$msg .= vmText::_ ('VMPAYMENT_KLARNA_NO_GETADDRESS');
				$session->set ('Klarna', json_encode ($sessionKlarna), 'vm');
				return FALSE;
			}
			//This example only works for GA_GIVEN.
			foreach ($swedish_addresses as $address) {
				if ($address->isCompany) {
					$klarnaData['company_name'] = $address->getCompanyName ();
					$klarnaData['first_name'] = "-";
					$klarnaData['last_name'] = "-";
				} else {
					$klarnaData['first_name'] = $address->getFirstName ();
					$klarnaData['last_name'] = $address->getLastName ();
				}
				$klarnaData['street'] = $address->getStreet ();
				$klarnaData['zip'] = $address->getZipCode ();
				$klarnaData['city'] = $address->getCity ();
				$klarnaData['country'] = $address->getCountryCode ();
				$countryId = $klarnaData['virtuemart_country_id'] = shopFunctions::getCountryIDByName ($klarnaData['country']);
			}
			foreach ($klarnaData as $key => $value) {
				$klarnaData[$key] = mb_convert_encoding ($klarnaData[$key], 'UTF-8', 'ISO-8859-1');
			}
		}
		$address_type = NULL;
		$st = $this->getCartAddress ($cart, $address_type, TRUE);
		vmDebug ('getCartAddress', $st);
		if ($address_type == 'BT') {
			$prefix = '';
		} else {
			$prefix = 'shipto_';
		}

		// Update the Shipping Address to what is specified in the register.
		$update_data = array(
			$prefix . 'address_type_name'     => 'Klarna',
			$prefix . 'company'               => $klarnaData['company_name'],
			$prefix . 'title'                 => $klarnaData['title'],
			$prefix . 'first_name'            => $klarnaData['first_name'],
			$prefix . 'middle_name'           => $st['middle_name'],
			$prefix . 'last_name'             => $klarnaData['last_name'],
			$prefix . 'address_1'             => $klarnaData['street'],
			$prefix . 'address_2'             => $klarnaData['house_ext'],
			$prefix . 'house_no'              => $klarnaData['house_no'],
			$prefix . 'zip'                   => html_entity_decode ($klarnaData['zip']),
			$prefix . 'city'                  => $klarnaData['city'],
			$prefix . 'virtuemart_country_id' => $countryId, //$klarnaData['virtuemart_country_id'],
			$prefix . 'state'                 => '',
			$prefix . 'phone_1'               => $klarnaData['phone'],
			$prefix . 'phone_2'               => $st['phone_2'],
			$prefix . 'fax'                   => $st['fax'],
			//$prefix . 'birthday'              => empty($klarnaData['birthday']) ? $st['birthday'] : $klarnaData['birthday'],
			//$prefix . 'socialNumber'          => empty($klarnaData['pno']) ? $klarnaData['socialNumber'] : $klarnaData['pno'],
			'address_type'                    => $address_type
		);
		if ($address_type == 'BT') {
			$update_data ['email'] = $klarnaData['email'];
		}

		if (!empty($st)) {
			$update_data = array_merge ($st, $update_data);
		}
		// save address in cart if different
		// 	if (false) {
		$cart->saveAddressInCart ($update_data, $update_data['address_type'], TRUE);
		//vmdebug('plgVmOnSelectCheckPayment $cart',$cart);
		//vmInfo(vmText::_('VMPAYMENT_KLARNA_ADDRESS_UPDATED_NOTICE'));
		// 	}
		//}
		// Store the Klarna data in a session variable so
		// we can retrevie it later when we need it
		//$klarnaData['pclass'] = ($klarnaData_paymentmethod == 'klarna_invoice' ? -1 : intval(vRequest::getVar($kIndex . "paymentPlan")));
		$klarnaData['pclass'] = ($klarnaData_paymentmethod == 'klarna_invoice' ? -1 : intval (vRequest::getVar ("part_klarna_paymentPlan")));

		$sessionKlarna->KLARNA_DATA =(object) $klarnaData;

		// 2 letters small
		//$settings = KlarnaHandler::getCountryData($method, $cart_country2);

		try {
			$address = new KlarnaAddr(
				$klarnaData['email'],
				$klarnaData['phone'],
				"", //mobile
				$klarnaData['first_name'],
				$klarnaData['last_name'], '',
				$klarnaData['street'],
				$klarnaData['zip'],
				$klarnaData['city'],
				$klarnaData['country'], // $settings['country'],
				$klarnaData['house_no'],
				$klarnaData['house_ext']
			);
		}
		catch (Exception $e) {
			VmInfo ($e->getMessage ());
			return FALSE;
			//KlarnaHandler::redirectPaymentMethod('message', $e->getMessage());
		}

		if (isset($errors) && count ($errors) > 0) {
			$msg = vmText::_ ('VMPAYMENT_KLARNA_ERROR_TITLE_1');
			foreach ($errors as $error) {
				$msg .= "<li> -" . $error . "</li>";
			}
			$msg .= vmText::_ ('VMPAYMENT_KLARNA_ERROR_TITLE_2');
			unset($errors);
			VmError ($msg);
			return FALSE;
			//KlarnaHandler::redirectPaymentMethod('error', $msg);
		}
		$session->set ('Klarna', json_encode ($sessionKlarna), 'vm');

		return TRUE;
	}

	/**
	 * plgVmOnSelectedCalculatePricePayment
	 * Calculate the price (value, tax_id) of the selected method
	 * It is called by the calculator
	 * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	public function plgVmOnSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onKlarnaSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
	}

	/**
	 * @param VirtuemartViewUser $user
	 * @param                    $html
	 * @param string             $from_cart
	 * @return bool|null
	 */
	function plgVmDisplayLogin (VmView $view, &$html, $from_cart = FALSE) {

		// only to display it in the cart, not in list orders view
		if (!$from_cart) {
			return NULL;
		}

		$vendorId = 1;
		if (!class_exists ('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart ();
		if ($cart->BT != 0 or $cart->virtuemart_paymentmethod_id) {
			return;
		}
		if ($this->getPluginMethods ($vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication ();
				$app->enqueueMessage (vmText::_ ('COM_VIRTUEMART_CART_NO_' . strtoupper ($this->_psType)));
				return FALSE;
			} else {
				return FALSE;
			}
		}
		//vmdebug('plgVmDisplayLogin', $user);
		//$html = $this->renderByLayout('displaylogin', array('klarna_pm' => $klarna_pm, 'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id, 'klarna_paymentmethod' => $klarna_paymentmethod));
		$link = JRoute::_ ('index.php?option=com_virtuemart&view=cart&task=editpayment&klarna_country_2_code=se', false);
		foreach ($this->methods as $method) {
			if ($method->klarna_active_swe) {
				$html .= $this->renderByLayout ('displaylogin', array('editpayment_link' => $link));
			}
		}
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	private function onKlarnaSelectedCalculatePrice (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		if (!($method = $this->selectedThisByMethodId ($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($method = $this->getVmPluginMethod ($cart->virtuemart_paymentmethod_id))) {
			return NULL;
		}

		$sessionKlarnaData = $this->getKlarnaSessionData ();
		if (empty($sessionKlarnaData)) {
			return NULL;
		}

		$cart_prices_name = '';
		$cart_prices[$this->_psType . '_tax_id'] = 0;
		$cart_prices['cost'] = 0;
		//vmdebug('cart prices',  $cart_prices);
		$country_code = NULL;
		$countryId = 0;
		$this->_getCountryCode ($cart, $country_code, $countryId, 'country_2_code');
		if (isset($sessionKlarnaData->KLARNA_DATA) AND strcasecmp ($country_code, $sessionKlarnaData->KLARNA_DATA['country']) != 0) {
			return FALSE;
		}
		//$paramsName       = $this->_psType . '_params';
		$type = NULL;
		$address = $this->getCartAddress ($cart, $type, FALSE);
		if (empty($address)) {
			return FALSE;
		}
		$shipTo = KlarnaHandler::getShipToAddress ($cart);
		$cart_prices_name = $this->renderKlarnaPluginName ($method, $address['virtuemart_country_id'], $shipTo, $cart_prices['withTax'], $cart->pricesCurrency);
		if (isset($sessionKlarnaData->klarna_option) AND $sessionKlarnaData->klarna_option == 'invoice') {
			$this->setCartPrices ($cart, $cart_prices, $method);
		}
		return TRUE;
	}

	/**
	 * update the plugin cart_prices
	 *
	 * @author Valérie Isaksen
	 * @param VirtueMartCart $cart
	 * @param                $cart_prices
	 * @param                $method
	 */
	function setCartPrices (VirtueMartCart $cart, &$cart_prices, $method, $progressive = true) {

		$country = NULL;
		$countryId = 0;
		$this->_getCountryCode (NULL, $country, $countryId);
		$invoice_fee = KlarnaHandler::getInvoiceFee ($method, $country);
		$invoice_tax_id = KlarnaHandler::getInvoiceTaxId ($method, $country);

		$_psType = ucfirst ($this->_psType);

		$taxrules = array();
		if (!empty($invoice_tax_id)) {
			$db = JFactory::getDBO ();
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $invoice_tax_id . '" ';
			$db->setQuery ($q);
			$taxrules = $db->loadAssocList ();
		}
		if (!class_exists ('calculationHelper')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
		}
		$calculator = calculationHelper::getInstance ();
		if (count ($taxrules) > 0) {
			$calculator->setRevert (TRUE);
			$invoiceFeeWithoutTax = $calculator->roundInternal ($calculator->executeCalculation ($taxrules, $invoice_fee));
			$cart_prices['salesPricePayment'] = $invoice_fee;
			$cart_prices['paymentTax'] = $invoice_fee - $calculator->roundInternal ($invoiceFeeWithoutTax);
			$cart_prices['paymentValue'] = $invoiceFeeWithoutTax;
			$calculator->setRevert (FALSE);
			$cart_prices[$this->_psType . '_calc_id'] = $taxrules[0]['virtuemart_calc_id'];
		} else {
			$cart_prices['paymentValue'] = $invoice_fee;
			$cart_prices['salesPricePayment'] = $invoice_fee;
			$cart_prices['paymentTax'] = 0;
			$cart_prices[$this->_psType . '_calc_id'] = 0;
		}

	}

	/**
	 * @param $method
	 * @param $virtuemart_country_id
	 * @param $shipTo
	 * @param $total
	 * @return string
	 */
	protected function renderKlarnaPluginName ($method, $virtuemart_country_id, $shipTo, $total, $cartPricesCurrency) {

		$session = JFactory::getSession ();
		$sessionKlarna = $session->get ('Klarna', 0, 'vm');
		if (empty($sessionKlarna)) {
			return '';
		}
		$sessionKlarnaData = (object)json_decode ($sessionKlarna,true);
		$sessionKlarnaData->KLARNA_DATA=(array)$sessionKlarnaData->KLARNA_DATA;

		$address['virtuemart_country_id'] = $virtuemart_country_id;
		$cData = KlarnaHandler::getcData ($method, $address);
		$country2 = strtolower (shopFunctions::getCountryByID ($virtuemart_country_id, 'country_2_code'));
		$text = "";
		if (isset($sessionKlarnaData->klarna_option)) {
			switch ($sessionKlarnaData->klarna_option) {
				case 'invoice':
					$sType='invoice';
					$image = '/klarna_invoice_' . $country2 . '.png';
					//$logo = VMKLARNAPLUGINWEBASSETS . '/images/' . 'logo/klarna_' . $sType . '_' . $code2 . '.png';
					$image ="https://cdn.klarna.com/public/images/".strtoupper($country2)."/badges/v1/". $sType ."/".$country2."_". $sType ."_badge_std_blue.png?height=55&eid=".$cData['eid'];
					$display_invoice_fee = NULL;
					$invoice_fee = 0;
					KlarnaHandler::getInvoiceFeeInclTax ($method, $cData['country_code_3'], $cartPricesCurrency, $cData['virtuemart_currency_id'], $display_invoice_fee, $invoice_fee);
					$text = vmText::sprintf ('VMPAYMENT_KLARNA_INVOICE_TITLE_NO_PRICE', $display_invoice_fee);
					break;
				case 'partpayment':
				case 'part':
					$sType='account';
					//$image = '/klarna_part_' . $country2 . '.png';
					$image ="https://cdn.klarna.com/public/images/".strtoupper($country2)."/badges/v1/". $sType ."/".$country2."_". $sType ."_badge_std_blue.png?height=55&eid=".$cData['eid'];

					$address['virtuemart_country_id'] = $virtuemart_country_id;
					//$pclasses                         = KlarnaHandler::getPClasses(NULL,   KlarnaHandler::getKlarnaMode($method), $cData);
					if (!class_exists ('Klarna_payments')) {
						require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_payments.php');
					}

					$payments = new klarna_payments($cData, $shipTo);
					//vmdebug('displaylogos',$cart_prices);
					$totalInPaymentCurrency = KlarnaHandler::convertPrice ($total, $cData['vendor_currency'], $cData['virtuemart_currency_id']);
					vmdebug ('totalInPaymentCurrency', $totalInPaymentCurrency);
					if (isset($sessionKlarnaData->KLARNA_DATA)) {
						$text = $payments->displayPclass ($sessionKlarnaData->KLARNA_DATA['pclass'], $totalInPaymentCurrency); // .' '.$total;
					}
					break;
				case 'speccamp':
					$image = 'klarna_logo.png';
					$text = vmText::_ ('VMPAYMENT_KLARNA_SPEC_TITLE');
					break;
				default:
					$image = '';
					$text = '';
					break;
			}

			$plugin_name = $this->_psType . '_name';
			$plugin_desc = $this->_psType . '_desc';
			$payment_description = '';
			if (!empty($method->$plugin_desc)) {
				$payment_description = $method->$plugin_desc;
			}
			$payment_name = $method->$plugin_name;

			$html = $this->renderByLayout ('payment_cart', array(
				'logo'                => $image,
				'text'                => $text,
				'payment_description' => $payment_description,
				'payment_name'        => $payment_name
			));
			return $html;
		}
	}

	/**
	 * plgVmOnCheckAutomaticSelectedPayment
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {

		$nbMethod = 0;

		if ($this->getPluginMethods ($cart->vendorId) === 0) {
			return NULL;
		}

		foreach ($this->methods as $method) {
			$type = NULL;
			$cData = KlarnaHandler::getcData ($method, $this->getCartAddress ($cart, $type, FALSE));
			if ($cData) {
				if ($nb = (int)$this->checkCountryCondition ($method, $cData['country_code_3'], $cart)) {
					$nbMethod = $nbMethod + $nb;
				}
			}
		}

		$paymentCounter = $paymentCounter + $nbMethod;
		if ($nbMethod == 0) {
			return NULL;
		} else {
			return 0;
		}
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param $virtuemart_order_id
	 * @param $virtuemart_paymentmethod_id
	 * @param $payment_name
	 * @internal param int $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

		if ($this->onShowOrderFE ($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name)) {
			return FALSE;
		}
		return NULL;
	}

	function plgVmOnCheckoutAdvertise ($cart, &$payment_advertise) {

		$vendorId = 1;
		$loadScriptAndCss = FALSE;
		if (!class_exists ('Klarna_payments')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarna_payments.php');
		}
		$country_code = NULL;
		$countryId = 0;
		if ($this->getPluginMethods ($cart->vendorId) === 0) {
			return FALSE;
		}

		$this->_getCountryCode ($cart, $country_code, $countryId);
		foreach ($this->methods as $method) {
			if ($cart->virtuemart_paymentmethod_id == $method->virtuemart_paymentmethod_id) {
				continue;
			}
			if (!($cData = $this->checkCountryCondition ($method, $country_code, $cart))) {
				return NULL;
			}
			if (strtolower ($country_code) == 'nld') {
				//  Since 12/09/12: merchants can sell goods with Klarna Invoice up to thousands of euros. So the price check has been moved here
				if (!KlarnaHandler::checkPartNLpriceCondition ($cart)) {
					// We can't show our payment options for Dutch customers
					// if price exceeds 250 euro. Will be replaced with ILT in
					// the future.
					return NULL;
				}
			}
			if (in_array ('part', $cData['payments_activated'])) {
				$payments = new klarna_payments($cData, KlarnaHandler::getShipToAddress ($cart));

				// TODO: change to there is a function in the API
				$sFee = $payments->getCheapestMonthlyCost ($cart, $cData);
				if ($sFee) {
					$payment_advertise[] = $this->renderByLayout ('cart_advertisement',
						array("sFee"   => $sFee,
							  "eid"    => $cData['eid'],
							  "country"=> $cData['country_code']
						));
				}
			}
		}

	}

	/**
	 * This method is fired when showing when printing an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $order_number The order Number
	 * @param integer $method_id            method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 */
	function plgVmOnShowOrderPrintPayment ($order_number, $method_id) {

		return $this->onShowOrderPrint ($order_number, $method_id);
	}

	/**
	 * @return mixed
	 */
	function _getVendorCurrency () {

		if (!class_exists ('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		}
		$vendor_id = 1;
		$vendor_currency = VirtueMartModelVendor::getVendorCurrency ($vendor_id);
		return $vendor_currency->currency_code_3;
	}

	/**
	 *
	 */
	function loadScriptAndCss () {
		static $loaded = false;
		if ($loaded) {
			return;
		}
		$assetsPath = VMKLARNAPLUGINWEBROOT . '/klarna/assets';
		vmJsApi::css ('style', $assetsPath . '/css');
		vmJsApi::css ('klarna', $assetsPath . '/css');

		vmJsApi::addJScript('klarna_general', '/'.$assetsPath . '/js/klarna_general.js');
		vmJsApi::addJScript ('klarnaConsentNew', 'https://static.klarna.com/external/js/klarnaConsentNew.js',false);

		$loaded=true;
	}

}

// No closing tag
