<?php

/**
 *
 * List/add/edit/remove Users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9663 2017-11-09 00:00:38Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if (!class_exists('VmView'))
    require(VMPATH_SITE . DS . 'helpers' . DS . 'vmview.php');

// Set to '0' to use tabs i.s.o. sliders
// Might be a config option later on, now just here for testing.
define('__VM_USER_USE_SLIDERS', 0);

/**
 * HTML View class for maintaining the list of users
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @author Max Milbers
 */
class VirtuemartViewUser extends VmView {

    private $_model;
    private $_cuid = 0;
    public $userDetails = false;
    private $_orderList = 0;
    private $_openTab = 0;

    /**
     * Displays the view, collects needed data for the different layouts
     *
     * @author Max Milbers
     */
    function display($tpl = null) {

		$this->useSSL = vmURI::useSSL();
		$this->useXHTML = false;

		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$layoutName = $this->getLayout();
		if ($layoutName == 'login') {
			parent::display($tpl);
			return;
		}

		if (empty($layoutName) or $layoutName == 'default') {
			$layoutName = vRequest::getCmd('layout', 'edit');
			if ($layoutName == 'default'){
				$layoutName = 'edit';
			}
			$this->setLayout($layoutName);
		}

		$this->_model = VmModel::getModel('user');

		//$this->_model->setCurrent(); //without this, the administrator can edit users in the FE, permission is handled in the usermodel, but maybe unsecure?
		$editor = JFactory::getEditor();

		$virtuemart_user_id = vRequest::getInt('virtuemart_user_id',false);
		if($virtuemart_user_id and is_array($virtuemart_user_id)) $virtuemart_user_id = $virtuemart_user_id[0];

		$this->_model->setId($virtuemart_user_id);

		$this->userDetails = $this->_model->getUser();

		$this->address_type = vRequest::getCmd('addrtype', 'BT');

		$new = false;
		if (vRequest::getInt('new', '0') == 1) {
			$new = true;
		}

		if ($new) {
			$virtuemart_userinfo_id = 0;
		} else {
			$virtuemart_userinfo_id = vRequest::getString('virtuemart_userinfo_id', 0);
		}


		$userFields = null;

		if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		$this->cart = VirtueMartCart::getCart();
		$task = vRequest::getCmd('task', '');



		if (($this->cart->_fromCart or $this->cart->getInCheckOut()) && empty($virtuemart_userinfo_id)) {

			//New Address is filled here with the data of the cart (we are in the cart)
			$fieldtype = $this->address_type . 'address';

			$this->cart->prepareAddressFieldsInCart();
			$userFields = $this->cart->$fieldtype;

		} else {
			if($task=='addST'){
				$this->address_type='ST';
			}
			if(!$new and empty($virtuemart_userinfo_id)){
				$virtuemart_userinfo_id = $this->_model->getBTuserinfo_id();
				vmdebug('Try to get $virtuemart_userinfo_id by type BT', $virtuemart_userinfo_id);
			}
			$userFields = $this->_model->getUserInfoInUserFields($layoutName, $this->address_type, $virtuemart_userinfo_id,false);
			if (!$new && empty($userFields[$virtuemart_userinfo_id])) {
				$virtuemart_userinfo_id = $this->_model->getBTuserinfo_id();
				vmdebug('$userFields by getBTuserinfo_id',$userFields);
			}

			$userFields = $userFields[$virtuemart_userinfo_id];
		}
		//vmdebug('my userfields ',$userFields);
		$this->virtuemart_userinfo_id = $virtuemart_userinfo_id;

		$this->assignRef('userFields', $userFields);

		if ($layoutName == 'edit') {

			if ($this->_model->getId() == 0 && $this->_cuid == 0) {
			$button_lbl = vmText::_('COM_VIRTUEMART_REGISTER');
			} else {
			$button_lbl = vmText::_('COM_VIRTUEMART_SAVE');
			}

			$this->assignRef('button_lbl', $button_lbl);
			$this->lUser();
			$this->shopper($userFields);

			$this->payment();
			$this->lOrderlist();
			$this->lVendor();
		}


		$stTask = 'addST';
		if ($task == 'editaddresscart'){
			$stTask = 'editaddresscart';
		}
		$this->_lists['shipTo'] = ShopFunctionsF::generateStAddressList($this,$this->_model, $stTask);

		$this->assignRef('lists', $this->_lists);

		$this->assignRef('editor', $editor);

		if ($layoutName == 'mailregisteruser') {
			$vendorModel = VmModel::getModel('vendor');
			//			$vendorModel->setId($this->_userDetails->virtuemart_vendor_id);
			$vendor = $vendorModel->getVendor();
			$this->assignRef('vendor', $vendor);

		}
		if ($layoutName == 'editaddress') {
			$layoutName = 'edit_address';
			$this->setLayout($layoutName);
		}

		if (!$this->userDetails->JUser->get('id')) {
			$corefield_title = vmText::_('COM_VIRTUEMART_USER_CART_INFO_CREATE_ACCOUNT');
		} else {
			$corefield_title = vmText::_('COM_VIRTUEMART_YOUR_ACCOUNT_DETAILS');
		}
		if ($this->cart->_fromCart or $this->cart->getInCheckOut()) {
			$pathway->addItem(vmText::_('COM_VIRTUEMART_CART_OVERVIEW'), JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE));
		} else {
			//$pathway->addItem(vmText::_('COM_VIRTUEMART_YOUR_ACCOUNT_DETAILS'), JRoute::_('index.php?option=com_virtuemart&view=user&&layout=edit'));
		}
		$pathway_text = vmText::_('COM_VIRTUEMART_YOUR_ACCOUNT_DETAILS');
		if (!$this->userDetails->JUser->get('id')) {
			if ($this->cart->_fromCart or $this->cart->getInCheckOut()) {
				if ($this->address_type == 'BT') {
					$vmfield_title = vmText::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL');
				} else {
					$vmfield_title = vmText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL');
				}
			} else {
			if ($this->address_type == 'BT') {
				$vmfield_title = vmText::_('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL');
				$title = vmText::_('COM_VIRTUEMART_REGISTER');
			} else {
				$vmfield_title = vmText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL');
			}
			}
		} else {

			if ($this->address_type == 'BT') {
				$vmfield_title = vmText::_('COM_VIRTUEMART_USER_FORM_BILLTO_LBL');
			} else {
				$vmfield_title = vmText::_('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL');
			}
		}
		//vmdebug('My fields',$userFields['fields']);

		$prefiks = '';
		if($this->address_type=='ST'){
			$prefiks = 'shipto_';
		}
		vmJsApi::vmValidator($this->userDetails->JUser->guest,$userFields['fields'],$prefiks);

		$this->add_product_link="";
		$this->manage_link="";
		if(ShopFunctionsF::isFEmanager('manage'/*,'category','product','inventory','ratings','custom','calc','manufacturer','orders','report','user'*/) ){
			$mlnk = JURI::root() . 'index.php?option=com_virtuemart&tmpl=component&manage=1' ;
			$this->manage_link = $this->linkIcon($mlnk, 'JACTION_MANAGE', 'new', false, false, true, true);
		}
		if(ShopFunctionsF::isFEmanager('product.edit')){
			$aplnk = JURI::root() . 'index.php?option=com_virtuemart&tmpl=component&view=product&view=product&task=edit&virtuemart_product_id=0&manage=1' ;
			$this->add_product_link = $this->linkIcon($aplnk, 'COM_VIRTUEMART_PRODUCT_ADD_PRODUCT', 'new', false, false, true, true);
		}

		$document = JFactory::getDocument();
		$document->setTitle($pathway_text);
		$pathway->additem($pathway_text);
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');
		$this->assignRef('page_title', $pathway_text);
		$this->assignRef('corefield_title', $corefield_title);
		$this->assignRef('vmfield_title', $vmfield_title);

		shopFunctionsF::setVmTemplate($this, 0, 0, $layoutName);

		$this->captcha = shopFunctionsF::renderCaptcha();

		parent::display($tpl);
    }

    function payment() {

    }

    function lOrderlist() {
	// Check for existing orders for this user
	$orders = VmModel::getModel('orders');

	if ($this->_model->getId() == 0) {
	    // getOrdersList() returns all orders when no userID is set (admin function),
	    // so explicetly define an empty array when not logged in.
	    $this->_orderList = array();
	} else {
	    $this->_orderList = $orders->getOrdersList($this->_model->getId(), true);

	    if (empty($this->currency)) {
		if (!class_exists('CurrencyDisplay'))
		    require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');

		$currency = CurrencyDisplay::getInstance();
		$this->assignRef('currency', $currency);
	    }
	}
		if($this->_orderList){
			vmLanguage::loadJLang('com_virtuemart_orders',TRUE);
		}
	$this->assignRef('orderlist', $this->_orderList);
    }

    function shopper($userFields) {

		// Shopper info
		if (!class_exists('VirtueMartModelShopperGroup'))
			require(VMPATH_ADMIN . DS . 'models' . DS . 'shoppergroup.php');

		$_shoppergroup = VirtueMartModelShopperGroup::getShoppergroupById($this->_model->getId());

		$this->_lists['shoppergroups'] = '';
		if(vmAccess::manager('user.edit')) {
			$shoppergrps = array();
			foreach($_shoppergroup as $group){
				$shoppergrps[] = $group['virtuemart_shoppergroup_id'];
			}
			if (!class_exists('ShopFunctions'))	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
			$this->_lists['shoppergroups'] = ShopFunctions::renderShopperGroupList($shoppergrps);
		} else {
			foreach($_shoppergroup as $group){
				$this->_lists['shoppergroups'] .= vmText::_($group['shopper_group_name']).', ';
			}
			$this->_lists['shoppergroups'] = substr($this->_lists['shoppergroups'],0,-2);
		}

		if (!empty($this->userDetails->virtuemart_vendor_id)) {
			if (!class_exists('ShopFunctions'))	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
			$this->_lists['vendors'] = ShopFunctions::renderVendorList($this->userDetails->virtuemart_vendor_id);
		} else {
			$this->_lists['vendors'] = vmText::_('COM_VIRTUEMART_USER_NOT_A_VENDOR');
		}

		//todo here is something broken we use $userDetailsList->perms and $this->userDetailsList->perms and perms seems not longer to exist
		//todo we should list here the joomla ACL groups

		// Load the required scripts
		if (count($userFields['scripts']) > 0) {
			foreach ($userFields['scripts'] as $_script => $_path) {
			JHtml::script($_script, $_path);
			}
		}

		// Load the required styresheets
		if (count($userFields['links']) > 0) {
			foreach ($userFields['links'] as $_link => $_path) {
				vmJsApi::css($_link, $_path);
			}
		}
    }

    function lUser() {

		$currentUser = JFactory::getUser();
		// Can't block myself TODO I broke that, please retest if it is working again
		$this->lists['canBlock'] = ($currentUser->authorise('com_users', 'block user') && ($this->_model->getId() != $this->_cuid));
		$this->lists['canSetMailopt'] = $currentUser->authorise('workflow', 'email_events');
		$this->_lists['block'] = JHtml::_('select.booleanlist', 'block', 'class="inputbox"', $this->userDetails->JUser->get('block'), 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');
		$this->_lists['sendEmail'] = JHtml::_('select.booleanlist', 'sendEmail', 'class="inputbox"', $this->userDetails->JUser->get('sendEmail'), 'COM_VIRTUEMART_YES', 'COM_VIRTUEMART_NO');

		$this->_lists['params'] = $this->userDetails->JUser->getParameters(true);

		$this->_lists['custnumber'] = $this->_model->getCustomerNumberById();

    }

    function lVendor() {

		// If the current user is a vendor, load the store data
		if ($this->userDetails->user_is_vendor) {
			vmJsApi::addJScript('/administrator/components/com_virtuemart/assets/js/vm2admin.js',false,false);
			vmJsApi::addJScript('fancybox/jquery.mousewheel-3.0.4.pack');
			vmJsApi::addJScript('fancybox/jquery.easing-1.3.pack');
			vmJsApi::addJScript('fancybox/jquery.fancybox-1.3.4.pack');
			vmJsApi::addJScript('jquery.ui.autocomplete.html');
			vmJsApi::chosenDropDowns();
			vmJsApi::jQueryUi();

			$currencymodel = VmModel::getModel('currency', 'VirtuemartModel');
			$currencies = $currencymodel->getCurrencies();
			$this->assignRef('currencies', $currencies);

			if (!$this->_orderList) {
				$this->lOrderlist();
			}

			$vendorModel = VmModel::getModel('vendor');
			$vendorModel->setId($this->userDetails->virtuemart_vendor_id);

			$this->vendor = $vendorModel->getVendor();
			$vendorModel->addImages($this->vendor);

		}
    }


	public function vmValidator (){
		$prefiks = '';
		if($this->address_type=='ST'){
			$prefiks = 'shipto_';
		}
		vmJsApi::vmValidator($this->userDetails->JUser->guest,$this->userFields['fields'],$prefiks);
	}

    /**
     * renderMailLayout
     *
     * @author Max Milbers
     * @author Valerie Isaksen
     */

    public function renderMailLayout($doVendor, $recipient) {

		$this->useSSL = vmURI::useSSL();
		$this->useXHTML = true;

		$userFieldsModel = VmModel::getModel('UserFields');
		$userFields = $userFieldsModel->getUserFields();
		$this->userFields = $userFieldsModel->getUserFieldsFilled($userFields, $this->user->userInfo);


		if (VmConfig::get('order_mail_html')) {
			$mailFormat = 'html';
			$lineSeparator="<br />";
		} else {
			$mailFormat = 'raw';
			$lineSeparator="\n";
		}

		$virtuemart_vendor_id=1;
		$vendorModel = VmModel::getModel('vendor');
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
		$vendorModel->addImages($vendor);
		$vendor->vendorFields = $vendorModel->getVendorAddressFields();
		$this->assignRef('vendor', $vendor);

		if (!$doVendor) {
			$this->subject = vmText::sprintf('COM_VIRTUEMART_NEW_SHOPPER_SUBJECT', $this->user->username, $this->vendor->vendor_store_name);
			$tpl = 'mail_' . $mailFormat . '_reguser';
		} else {
			$this->subject = vmText::sprintf('COM_VIRTUEMART_VENDOR_NEW_SHOPPER_SUBJECT', $this->user->username, $this->vendor->vendor_store_name);
			$tpl = 'mail_' . $mailFormat . '_regvendor';
		}

		$this->assignRef('recipient', $recipient);
		$this->vendorEmail = $vendorModel->getVendorEmail($this->vendor->virtuemart_vendor_id);
		$this->layoutName = $tpl;
		$this->setLayout($tpl);
		$this->isMail = true;

		parent::display();
    }


}

//No Closing Tag
