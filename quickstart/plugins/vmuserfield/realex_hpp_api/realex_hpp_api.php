<?php
defined('_JEXEC') or die();


/**
 *
 * Realex User Field plugin
 *
 * @author Valerie Isaksen
 * @version $Id: realex_hpp_api.php 9560 2017-05-30 14:13:21Z Milbo $
 * @package VirtueMart
 * @subpackage userfield
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

if (!class_exists('vmUserfieldPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmuserfieldtypeplugin.php');
}
define('USERFIELD_REALEX', 1);

class plgVmUserfieldRealex_hpp_api extends vmUserfieldPlugin {

	var $varsToPush = array();

	const REALEX_FOLDERNAME = "realex_hpp_api";

	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->setConfigParameterable('params', $this->varsToPush);
		$this->_userFieldName = 'realex_hpp_api';
	}

	/**
	 * @return string
	 */
	public function getVmPluginCreateTableSQL() {

		$db = JFactory::getDBO();
		$query = 'SHOW TABLES LIKE "%' . str_replace('#__', '', $this->_tablename) . '"';
		$db->setQuery($query);
		$result = $db->loadResult();
		$app = JFactory::getApplication();
		$tablesFields = 0;
		if ($result) {
			$SQLfields = $this->getTableSQLFields();
			$loggablefields = $this->getTableSQLLoggablefields();
			$tablesFields = array_merge($SQLfields, $loggablefields);
			$update[$this->_tablename] = array($tablesFields, array(), array());
			vmdebug(get_class($this) . ':: VirtueMart2 update ' . $this->_tablename);
			if (!class_exists('GenericTableUpdater')) {
				require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
			}
			$updater = new GenericTableUpdater();
			$updater->updateMyVmTables($update);
			//	return FALSE;   //TODO enable this, when using vm version higher than 2.0.8F
		} else {
			return $this->createTableSQL('Userfield Realex Realvault Table', $tablesFields);
		}

	}

	/**
	 * @return array
	 */
	function getTableSQLFields() {

		$SQLfields = array(
			'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_user_id' => 'int(1) UNSIGNED',
			'merchant_id' => 'varchar(128)',
			// Realvault CCs are associated with a Merchant ID and not payment method
			'realex_hpp_api_saved_pmt_type' => 'varchar(20)',
			'realex_hpp_api_saved_pmt_ref' => 'char(50)',
			'realex_hpp_api_saved_pmt_digits' => 'varchar(128)',
			'realex_hpp_api_saved_pmt_expdate' => 'varchar(16)',
			'realex_hpp_api_saved_pmt_name' => 'char(255)',
		);
		return $SQLfields;
	}

	function plgVmDeclarePluginParamsUserfield($type, $name, $id, &$data) {

		return $this->declarePluginParams($type, $name, $id, $data);
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallPluginTable($type, $data) {

		return $this->onStoreInstallPluginTable($type, $data->name);
	}


	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the shipment-specific data.
	 *
	 * @param integer $order_number The order Number
	 * @return mixed Null for shipments that aren't active, text (HTML) otherwise
	 * @author Valérie Isaksen
	 */

	public function plgVmOnUserfieldDisplay($_prefix, $field, $userId, &$return) {

		if ('plugin' . $this->_name != $field->type) {
			return;
		}
		$html = $this->onShowUserDisplayUserfield($userId, $field->name);
		if ($html) {
			$return['fields'][$field->name]['formcode'] .= $html;
		}
		return '';

	}

	public function plgVmOnPaymentDisplay($fieldtype, $userId, &$storedCreditCards) {

		if ('plugin' . $this->_name != $fieldtype) {
			return;
		}

		$storedCreditCards = $this->getStoredCreditCards($userId);


	}

	public function plgVmPrepareUserfieldDataSave($fieldType, $fieldName, $post, &$value, $params) {

		if ('plugin' . $this->_name != $fieldType) {
			return;
		}

		$card_delete_ids = JRequest::getVar('realex_card_delete_ids', array(), 'post', 'array');
		$card_update_ids = JRequest::getVar('realex_card_update_ids', array(), 'post', 'array');
		if (!empty($card_delete_ids)) {
			return $this->deleteStoredCards($card_delete_ids);
		}
		if (!empty($card_update_ids)) {
			return $this->updateStoredCards($card_update_ids);
		}
		if (!class_exists('ShopFunctions')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
		}
// we come from payment
		if (isset($params['fromPayment'])) {
			$this->storePluginInternalData($params);
		}

	}

	/**
	 * Delete a stored card
	 * To remove a card from the RealVault system
	 */
	function deleteStoredCards($card_ids) {

		$db = JFactory::getDBO();
		foreach ($card_ids as $card_id) {

			JLoader::import('joomla.plugin.helper');
			JPluginHelper::importPlugin('vmpayment');
			$app = JFactory::getApplication();

			$storedCC = $this->getStoredCCInfo($card_id);
			$expDate = $this->explodeExpDate($storedCC['realex_hpp_api_saved_pmt_expdate']);
			$success = false;
			// the trigger will send the card-cancel-card to Releax
			$app->triggerEvent('plgVmOnRealexDeletedStoredCard', array('realex_hpp_api', $storedCC, &$success));
			if ($success) {
				$query = 'DELETE FROM `' . $this->_tablename . '` WHERE `id`=' . $card_id . ' AND `virtuemart_user_id`=' . $storedCC['virtuemart_user_id'];
				$db->setQuery($query);
				$db->query();
				vmInfo(vmText::sprintf('VMUSERFIELD_REALEX_HPP_API_CARD_DELETED', $storedCC['realex_hpp_api_saved_pmt_name'], $storedCC['realex_hpp_api_saved_pmt_digits'], $expDate['mm'], $expDate['yy']));

			} else {
				$vendorId = 1;
				$vendor_link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=contact&virtuemart_vendor_id=' . $vendorId);
				vmInfo(vmText::sprintf('VMUSERFIELD_REALEX_HPP_API_CARD_NOT_DELETED', $storedCC['realex_hpp_api_saved_pmt_name'], $storedCC['realex_hpp_api_saved_pmt_digits'], $expDate['mm'], $expDate['yy'], $vendor_link));

			}

		}
	}

	/**
	 * Delete a stored card
	 * To remove a card from the RealVault system
	 */
	function updateStoredCards($card_ids) {
		$user = JFactory::getUser();
		$db = JFactory::getDBO();
		foreach ($card_ids as $card_id) {

			JLoader::import('joomla.plugin.helper');
			JPluginHelper::importPlugin('vmpayment');
			$app = JFactory::getApplication();

			$storedCC = $this->getStoredCCInfo($card_id);
			$updatedCCname = vRequest::getString('cc_name_' . $card_id);
			$updatedYear = vRequest::getInt('cc_expire_year_' . $card_id);
			$updatedMonth = vRequest::getInt('cc_expire_month_' . $card_id);
			$expDate = $this->explodeExpDate($storedCC['realex_hpp_api_saved_pmt_expdate']);
			if (($storedCC['realex_hpp_api_saved_pmt_name'] == $updatedCCname) AND ($expDate['yy'] == $updatedYear) AND ($expDate['mm'] == $updatedMonth)) {
				continue;
			} else {
				$storedCC['realex_hpp_api_saved_pmt_name'] = $updatedCCname;
				$storedCC['realex_hpp_api_saved_pmt_expdate'] = $updatedMonth . $updatedYear;
			}
			$success = false;
			// the trigger will send the card-cancel-card to Releax
			$app->triggerEvent('plgVmOnRealexUpdateStoredCard', array('realex_hpp_api', $storedCC, &$success));
			if ($success) {

				$query = 'UPDATE  `' . $this->_tablename . '` SET `realex_hpp_api_saved_pmt_name`="' . $storedCC['realex_hpp_api_saved_pmt_name'] . '" , `realex_hpp_api_saved_pmt_expdate`="' . $storedCC['realex_hpp_api_saved_pmt_expdate'] . '" WHERE `id`=' . $card_id . ' AND `virtuemart_user_id`=' . $storedCC['virtuemart_user_id'];
				$db->setQuery($query);
				$db->query();
				vmInfo(vmText::sprintf('VMUSERFIELD_REALEX_HPP_API_CARD_UPDATED', $storedCC['realex_hpp_api_saved_pmt_name'], $storedCC['realex_hpp_api_saved_pmt_digits'], $updatedMonth, $updatedYear));

			} else {
				$vendorId = 1;
				$vendor_link = JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=contact&virtuemart_vendor_id=' . $vendorId);
				vmInfo(vmText::sprintf('VMUSERFIELD_REALEX_HPP_API_CARD_NOT_UPDATED', $storedCC['realex_hpp_api_saved_pmt_name'], $storedCC['realex_hpp_api_saved_pmt_digits'], $updatedMonth, $updatedYear, $vendor_link));

			}

		}
	}


	/**
	 * stored format of the expired date is mmyy
	 * @param $expdate
	 * @return mixed
	 */
	function explodeExpDate($expdate) {
		$date['mm'] = substr($expdate, 0, 2);
		$date['yy'] = substr($expdate, -2);
		return $date;
	}


	function onShowUserDisplayUserfield($userId, $fieldName) {
		if ($userId == 0) {
			return;
		}
		$html = '';
		if (!class_exists('VmHTML')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'html.php');
		}
		$view = vRequest::getString('view', '');
		$this->loadJLangThis('plg_vmpayment_realex_hpp_api', 'vmpayment');
		if (($view == 'user')) {
			$storedCreditCards = $this->getStoredCreditCards($userId);
			if (empty ($storedCreditCards)) {
				return vmText::_('VMUSERFIELD_REALEX_HPP_API_NO_CARD_SAVED');
			}
			//$storedCreditCards = $this->isValidExpiredDate($storedCreditCards);

			$deleteUpdateAuthorized = true;
			$html = $this->renderByLayout("creditcardlist", array(
				"storedCreditCards" => $storedCreditCards,
				'deleteUpdateAuthorized' => $deleteUpdateAuthorized
			));
		} elseif ($view == 'order') {
			$userlink = JROUTE::_('index.php?option=com_virtuemart&view=user&task=edit&virtuemart_user_id[]=' . $userId, FALSE);
			$html = JHTML::_('link', JRoute::_($userlink, FALSE), JText::_('VMUSERFIELD_REALEX_HPP_API_MANAGE_CARDS'), array('title' => JText::_('VMUSERFIELD_REALEX_HPP_API_MANAGE_CARDS')));

		}

		return $html;
	}

	private function getStoredCreditCards($userId) {
		if (!($storedCreditCards = $this->_getInternalData($userId))) {
			return '';
		}

		return $storedCreditCards;

	}

	/**
	 * check if the expired Date is ok
	 * @param $storedCreditCards
	 */
	function isValidExpiredDate($creditCards) {
		if (!class_exists('Creditcard')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'creditcard.php');
		}
		foreach ($creditCards as $creditCard) {
			$exp_date = $this->explodeExpDate($creditCard->realex_hpp_api_saved_pmt_expdate);
			$creditCard->validExpiredDate = Creditcard::validate_credit_card_date('', $exp_date['mm'], $exp_date['yy']);
		}
		return $creditCards;
	}


	/**
	 * @param        $virtuemart_order_id
	 * @param string $order_number
	 * @return mixed|string
	 */
	function _getInternalData($userId) {

		$db = JFactory::getDBO();

		$prefix = $db->getPrefix();
		$query = 'SHOW TABLES LIKE "' . str_replace('#__', $prefix, $this->_tablename) . '"';
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result) {
			return NULL;
		}

		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		$q .= ' `virtuemart_user_id` = "' . $userId . '"';
		$q .= ' ORDER BY `modified_on` DESC ';

		$db->setQuery($q);
		return $db->loadObjectList();

	}

	/**
	 * @param        $virtuemart_order_id
	 * @param string $order_number
	 * @return mixed|string
	 */
	function getStoredCCInfo($card_id) {

		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` ';
		$q .= ' WHERE `id`=' . $card_id;

		$db->setQuery($q);
		return $db->loadAssoc();

	}


}

// No closing tag
