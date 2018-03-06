<?php
/**
 *
 * List/add/edit/remove Users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9617 2017-08-07 14:04:54Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for maintaining the list of users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 */
class VirtuemartViewUser extends VmViewAdmin {

	function display($tpl = null) {


		// Load the helper(s)
		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		$model = VmModel::getModel();
		$currentUser = JFactory::getUser();

		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);

		$task = vRequest::getCmd('task', 'edit');
		if($task == 'editshop'){
			$isSuperOrVendor = vmAccess::isSuperVendor();
			if(empty($isSuperOrVendor)){
				JFactory::getApplication()->redirect( 'index.php?option=com_virtuemart', vmText::_('JERROR_ALERTNOAUTHOR'), 'error');
			} else {
				if(!class_exists('VirtueMartModelVendor')) require(VMPATH_ADMIN.DS.'models'.DS.'vendor.php');
				$userId = VirtueMartModelVendor::getUserIdByVendorId($isSuperOrVendor);
			}
			$this->SetViewTitle('STORE'  );
		} else if ($task == 'add'){
			$userId  = 0;
		} else {
			$userId = vRequest::getVar('virtuemart_user_id',0);
			if(is_array($userId)){
				$userId = $userId[0];
			}
			$this->SetViewTitle('USER');
		}
		$userId = $model->setId($userId);

		//$layoutName = vRequest::getCmd('layout', 'default');
		$layoutName = $this->getLayout();

		if ($layoutName == 'edit' || $layoutName == 'edit_shipto') {

			$editor = JFactory::getEditor();

			if (!class_exists('VmImage'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');

			$userDetails = $model->getUser();

			if($task == 'editshop' && $userDetails->user_is_vendor){
// 				$model->setCurrent();
				if(!empty($userDetails->vendor->vendor_store_name)){
					$this->SetViewTitle('STORE',$userDetails->vendor->vendor_store_name, 'shop_mart' );
				} else {
					$this->SetViewTitle('STORE',vmText::_('COM_VIRTUEMART_NEW_VENDOR') , 'shop_mart');
				}
				$vendorid = $userDetails->virtuemart_vendor_id;
				if($vendorid==1)$this -> checkTCPDFinstalled();
			} else {
				$vendorid = 0 ;
				$this->SetViewTitle('USER',$userDetails->JUser->get('name'));
			}

			$_new = ($userDetails->JUser->get('id') < 1);

			$this->addStandardEditViewCommands($vendorid);

			// User details
			$_contactDetails = $model->getContactDetails();

			$this->lists['canBlock'] = ($currentUser->authorise('com_users', 'block user')
			&& ($userDetails->JUser->get('id') != $currentUser->get('id'))); // Can't block myself
			$this->lists['canSetMailopt'] = $currentUser->authorise('workflow', 'email_events');
			$this->lists['block'] = JHtml::_('select.booleanlist', 'block',      'class="inputbox"', $userDetails->JUser->get('block'),     'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$this->lists['sendEmail'] = JHtml::_('select.booleanlist', 'sendEmail',  'class="inputbox"', $userDetails->JUser->get('sendEmail'), 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
			$this->lists['params'] = $userDetails->JUser->getParameters(true);

			// Shopper info
			$this->lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($userDetails->shopper_groups,true, 'virtuemart_shoppergroup_id');
			$this->lists['vendors'] = '';
			if($this->showVendors()){
				$this->lists['vendors'] = ShopFunctions::renderVendorList($userDetails->virtuemart_vendor_id);
			}

			$model->setId($userDetails->JUser->get('id'));
			$this->lists['custnumber'] = $model->getCustomerNumberById();

			// Shipment address(es)
			if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
			$this->lists['shipTo'] = shopFunctionsF::generateStAddressList($this, $model, 'addST');

			$new = false;
			if(vRequest::getInt('new','0')==1){
				$new = true;
			}

			$virtuemart_userinfo_id_BT = $model->getBTuserinfo_id($userId);
			$userFieldsArray = $model->getUserInfoInUserFields($layoutName,'BT',$virtuemart_userinfo_id_BT,false);
			$userFieldsBT = $userFieldsArray[$virtuemart_userinfo_id_BT];

			// Load the required scripts
			if (count($userFieldsBT['scripts']) > 0) {
				foreach ($userFieldsBT['scripts'] as $_script => $_path) {
					JHtml::script($_script, $_path);
				}
			}
			// Load the required stylesheets
			if (count($userFieldsBT['links']) > 0) {
				foreach ($userFieldsBT['links'] as $_link => $_path) {
					vmJsApi::css($_link, $_path);
				}
			}

			$this->assignRef('userFieldsBT', $userFieldsBT);
			$this->assignRef('userInfoID', $virtuemart_userinfo_id_BT);


			$addrtype = vRequest::getCmd('addrtype');
			$virtuemart_userinfo_id = 0;
			if ($layoutName == 'edit_shipto' or $task=='addST' or $addrtype=='ST') {
				$virtuemart_userinfo_id = vRequest::getString('virtuemart_userinfo_id', '0','');
				$userFieldsArray = $model->getUserInfoInUserFields($layoutName,'ST',$virtuemart_userinfo_id,false);
				if($new ){
					$virtuemart_userinfo_id = 0;

				} else {

				}
				$userFieldsST = $userFieldsArray[$virtuemart_userinfo_id];
				$this->assignRef('shipToFields', $userFieldsST);
			}

			$this->assignRef('shipToId', $virtuemart_userinfo_id);
			$this->assignRef('new', $new);

			if (!$_new) {
				// Check for existing orders for this user
				$orders = VmModel::getModel('orders');
				$orderList = $orders->getOrdersList($userDetails->JUser->get('id'), true);
			} else {
				$orderList = null;
			}


			if (count($orderList) > 0 || !empty($userDetails->user_is_vendor)) {
				if (!class_exists('CurrencyDisplay')) require(VMPATH_ADMIN.DS.'helpers'.DS.'currencydisplay.php');
				$currency = CurrencyDisplay::getInstance();
				$this->assignRef('currency',$currency);
			}

			if (!empty($userDetails->user_is_vendor)) {



				$vendorM = VmModel::getModel('vendor');
				//if(empty($userDetails->vendor->vendor_currency)){
					$vendorCurrency = $vendorM->getVendorCurrency(1);
					if($vendorCurrency) {
						$userDetails->vendor->vendor_currency = $vendorCurrency->vendor_currency;
						vmdebug('No vendor currency given, fallback to main vendor',$userDetails->vendor->vendor_currency);
					}
				//}
				$vendorM->setId($userDetails->virtuemart_vendor_id);

				$vendorM->addImages($userDetails->vendor);
				$this->assignRef('vendor', $userDetails->vendor);

				$currencyModel = VmModel::getModel('currency');
				$_currencies = $currencyModel->getCurrencies();
				$this->assignRef('currencies', $_currencies);
				
				$configModel = VmModel::getModel('config');
				$TCPDFFontsList = $configModel->getTCPDFFontsList();
				$this->assignRef('pdfFonts', $TCPDFFontsList);

			}


			$this->assignRef('userDetails', $userDetails);

			$this->assignRef('orderlist', $orderList);
			$this->assignRef('contactDetails', $_contactDetails);
			$this->assignRef('editor', $editor);

		} else {

			JToolBarHelper::divider();
			JToolBarHelper::custom('toggle.user_is_vendor.1', 'publish','','COM_VIRTUEMART_USER_ISVENDOR');
			JToolBarHelper::custom('toggle.user_is_vendor.0', 'unpublish','','COM_VIRTUEMART_USER_ISNOTVENDOR');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList();
			JToolBarHelper::editList();
			self::showACLPref('user');
			//This is intentionally, creating new user via BE is buggy and can be done by joomla
			//JToolBarHelper::addNewX();
			$this->addStandardDefaultViewLists($model,'ju.id');

			$userList = $model->getUserList();
			$this->assignRef('userList', $userList);

			$this->pagination = $model->getPagination();

			$shoppergroupmodel = VmModel::getModel('shopperGroup');
			$this->defaultShopperGroup = $shoppergroupmodel->getDefault(0)->shopper_group_name;
		}


		if(!empty($this->orderlist)){
			vmLanguage::loadJLang('com_virtuemart_orders',TRUE);
		}
		parent::display($tpl);
	}

	/*
	*	What is this doing here?
	*
	*/

	function renderMailLayout ($doVendor=false) {
		$tpl = ($doVendor) ? 'mail_html_regvendor' : 'mail_html_reguser';
		$this->setLayout($tpl);

		$vendorModel = VmModel::getModel('vendor');
		$vendorId = 1;
		$vendorModel->setId($vendorId);
		$vendor = $vendorModel->getVendor();
		$vendorModel->addImages($vendor);
		$this->assignRef('subject', ($doVendor) ? vmText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE_VENDOR_SUBJECT', $this->user->get('email')) : vmText::sprintf('COM_VIRTUEMART_NEW_USER_MESSAGE_SUBJECT',$vendor->vendor_store_name));
		parent::display();
	}

	private function checkTCPDFinstalled(){

		if(!file_exists(VMPATH_LIBS.DS.'tcpdf'.DS.'tcpdf.php')){
			vmLanguage::loadJLang('com_virtuemart_config');
			vmWarn('COM_VIRTUEMART_TCPDF_NINSTALLED');
		}
	}

}

//No Closing Tag
