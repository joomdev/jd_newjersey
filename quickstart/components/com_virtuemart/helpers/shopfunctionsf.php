<?php
/**
 *
 * Contains shop functions for the front-end
 *
 * @package    VirtueMart
 * @subpackage Helpers
 *
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: shopfunctionsf.php 9682 2017-11-30 12:16:43Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die('Restricted access');


class shopFunctionsF {

	static public function getLoginForm ($cart = FALSE, $order = FALSE, $url = '', $layout = 'login') {

		$body = '';
		$show = TRUE;

		if($cart) {
			$show = VmConfig::get( 'oncheckout_show_register', 1 );
		}
		if($show == 1) {
			//This is deprecated and will be replaced by the commented lines below (vmView instead of VirtuemartViewUser)
			if(!class_exists( 'VirtuemartViewUser' )) require(VMPATH_SITE.DS.'views'.DS.'user'.DS.'view.html.php');
			$view = new VirtuemartViewUser();
			//if(!class_exists( 'vmView' )) require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');
			//$view = new vmView();
			$body = $view->renderVmSubLayout($layout,array('show' => $show, 'order' => $order, 'from_cart' => $cart, 'url' => $url));
		}

		return $body;
	}

	static public function getLastVisitedCategoryId ($default = 0) {
		$session = JFactory::getSession();
		return $session->get( 'vmlastvisitedcategoryid', $default, 'vm' );
	}

	static public function setLastVisitedCategoryId ($categoryId) {
		$session = JFactory::getSession();
		return $session->set( 'vmlastvisitedcategoryid', (int)$categoryId, 'vm' );
	}

	static public function getLastVisitedItemId ($default = 0) {
		$session = JFactory::getSession();
		return $session->get( 'vmlastvisitedItemid', $default, 'vm' );
	}

	static public function setLastVisitedItemId ($id) {
		$session = JFactory::getSession();
		return $session->set( 'vmlastvisitedItemid', (int)$id, 'vm' );
	}

	static public function getLastVisitedManuId () {
		$session = JFactory::getSession();
		return $session->get( 'vmlastvisitedmanuid', 0, 'vm' );
	}

	static public function setLastVisitedManuId ($manuId) {
		$session = JFactory::getSession();
		return $session->set( 'vmlastvisitedmanuid', (int)$manuId, 'vm' );
	}

	/**
	 * @param $orderable
	 * @return string
	 * @deprecated
	 */
	static public function getAddToCartButton ($orderable) {
		return self::renderVmSubLayout('addtocartbtn',array('orderable'=>$orderable));
	}

	static public function isFEmanager ($task = 0) {
		if(JFactory::getUser()->guest) return false;
		return vmAccess::manager($task);
	}

	/**
	 * Just an idea, still WIP
	 * @param $type
	 * @return mixed
	 */
	static function renderFormField($type){
		//Get custom field
		JFormHelper::addFieldPath(VMPATH_ADMIN . DS . 'fields');
		$types = JFormHelper::loadFieldType($type, false);
		return $types->getOptions();
	}

	/**
	 * Return the order status name for a given code
	 *
	 * @author Oscar van Eijk
	 * @access public
	 *
	 * @param char $_code Order status code
	 * @return string The name of the order status
	 */
	static public function getOrderStatusName ($_code) {

		static $orderNames = array();
		$db = JFactory::getDBO ();
		$_code = $db->escape ($_code);
		if(!isset($orderNames[$_code])){
			$_q = 'SELECT `order_status_name` FROM `#__virtuemart_orderstates` WHERE `order_status_code` = "' . $_code . '"';
			$db->setQuery ($_q);
			$orderNames[$_code] = $db->loadObject ();
			if (empty($orderNames[$_code]->order_status_name)) {
				vmError ('getOrderStatusName: couldnt find order_status_name for ' . $_code);
				return 'current order status broken';
			} else {
				$orderNames[$_code] = vmText::_($orderNames[$_code]->order_status_name);
			}
		}

		return $orderNames[$_code];
	}

	/**
	 * Render a simple country list
	 *
	 * @author jseros, Max Milbers, Valérie Isaksen
	 *
	 * @param int $countryId Selected country id
	 * @param boolean $multiple True if multiple selections are allowed (default: false)
	 * @param mixed $_attrib string or array with additional attributes,
	 * e.g. 'onchange=somefunction()' or array('onchange'=>'somefunction()')
	 * @param string $_prefix Optional prefix for the formtag name attribute
	 * @return string HTML containing the <select />
	 */
	static public function renderCountryList ($countryId = 0, $multiple = FALSE, $_attrib = array(), $_prefix = '', $required = 0, $idTag = 'virtuemart_country_id') {

		$countryModel = VmModel::getModel ('country');
		$countries = $countryModel->getCountries (TRUE, TRUE, FALSE);
		$attrs = array();
		$optText = 'country_name';
		$optKey = 'virtuemart_country_id';
		$name = $_prefix.'virtuemart_country_id';
		$idTag = $_prefix.$idTag;
		$attrs['class'] = 'virtuemart_country_id';
		$attrs['class'] = 'vm-chzn-select';
		// Load helpers and  languages files
		if (!class_exists( 'VmConfig' )) require(JPATH_COMPONENT_ADMINISTRATOR .'/helpers/config.php');
		VmConfig::loadConfig();
		vmLanguage::loadJLang('com_virtuemart_countries');
		vmJsApi::jQuery();
		vmJsApi::chosenDropDowns();

		$sorted_countries = array();
		$lang = JFactory::getLanguage();
		$prefix="COM_VIRTUEMART_COUNTRY_";
		foreach ($countries as  $country) {
			$country_string = $lang->hasKey($prefix.$country->country_3_code) ?   vmText::_($prefix.$country->country_3_code)  : $country->country_name;
			$sorted_countries[$country->virtuemart_country_id] = $country_string;
		}

		asort($sorted_countries);

		$countries_list=array();
		$i=0;
		foreach ($sorted_countries as  $key=>$value) {
			$countries_list[$i] = new stdClass();
			$countries_list[$i]->$optKey = $key;
			$countries_list[$i]->$optText = $value;
			$i++;
		}

		if ($required != 0) {
			$attrs['class'] .= ' required';
		}

		if ($multiple) {
			$attrs['multiple'] = 'multiple';
			$name .= '[]';
		} else {
			$emptyOption = JHtml::_ ('select.option', '', vmText::_ ('COM_VIRTUEMART_LIST_EMPTY_OPTION'), $optKey, $optText);
			array_unshift ($countries_list, $emptyOption);
		}

		if (is_array ($_attrib)) {
			$attrs = array_merge ($attrs, $_attrib);
		} else {
			$_a = explode ('=', $_attrib, 2);
			$attrs[$_a[0]] = $_a[1];
		}

		return JHtml::_ ('select.genericlist', $countries_list, $name, $attrs, $optKey, $optText, $countryId, $idTag);
	}

	/**
	 * Render a simple state list
	 *
	 * @author Max Milbers, Valerie Isaksen
	 *
	 * @param int $stateID Selected state id
	 * @param int $countryID Selected country id
	 * @param string $dependentField Parent <select /> ID attribute
	 * @param string $_prefix Optional prefix for the formtag name attribute
	 * @return string HTML containing the <select />
	 */
	static public function renderStateList ($stateId = '0', $_prefix = '', $multiple = FALSE, $required = 0,$attribs=array(),$idTag = 'virtuemart_state_id', $suffix='_field') {

		if (is_array ($stateId)) {
			$stateId = implode (",", $stateId);
		}

		vmJsApi::JcountryStateList ($stateId,$_prefix, $suffix);

		if(!isset($attrs['class'])){
			$attrs['class'] = '';
		}
		if(!empty($required)){
			$attrs['class'] .= ' required';
		}
		$attrs['class'] .= ' vm-chzn-select';
		if ($multiple) {
			$attrs['name'] = $_prefix . 'virtuemart_state_id[]';
			$attrs['multiple'] = 'multiple';
		} else {
			$attrs['name'] = $_prefix . 'virtuemart_state_id';
		}

		if (is_array ($attribs)) {
			$attrs = array_merge ($attrs, $attribs);
		}

		$attrString= JArrayHelper::toString($attrs);
		$listHTML = '<select  id="'.$_prefix.$idTag.'" ' . $attrString . '>
						<option value="">' . vmText::_ ('COM_VIRTUEMART_LIST_EMPTY_OPTION') . '</option>
						</select>';

		return $listHTML;
	}

	/**
	 * This generates the list when the user have different ST addresses saved
	 *
	 * @author Max Milbers
	 */
	static function generateStAddressList ($view, $userModel, $task) {

		// Shipment address(es)
		$_addressList = $userModel->getUserAddressList ($userModel->getId (), 'ST');
		if (count ($_addressList) == 1 && empty($_addressList[0]->address_type_name)) {
			return vmText::_ ('COM_VIRTUEMART_USER_NOSHIPPINGADDR');
		} else {
			$_shipTo = array();
			$useXHTTML = empty($view->useXHTML) ? false : $view->useXHTML;
			$useSSL = empty($view->useSSL) ? FALSE : $view->useSSL;

			for ($_i = 0; $_i < count ($_addressList); $_i++) {
				if (empty($_addressList[$_i]->virtuemart_user_id)) {
					$_addressList[$_i]->virtuemart_user_id = JFactory::getUser ()->id;
				}
				if (empty($_addressList[$_i]->virtuemart_userinfo_id)) {
					$_addressList[$_i]->virtuemart_userinfo_id = 0;
				}
				if (empty($_addressList[$_i]->address_type_name)) {
					$_addressList[$_i]->address_type_name = 0;
				}

				$_shipTo[] = '<li>' . '<a href="index.php'
					. '?option=com_virtuemart'
					. '&view=user'
					. '&task=' . $task
					. '&addrtype=ST'
					. '&virtuemart_user_id[]=' . $_addressList[$_i]->virtuemart_user_id
					. '&virtuemart_userinfo_id=' . $_addressList[$_i]->virtuemart_userinfo_id
					. '">' . $_addressList[$_i]->address_type_name . '</a> ' ;
				$_shipTo[] = '&nbsp;&nbsp;<a href="'.JRoute::_ ('index.php?option=com_virtuemart&view=user&task=removeAddressST&virtuemart_user_id[]=' . $_addressList[$_i]->virtuemart_user_id . '&virtuemart_userinfo_id=' . $_addressList[$_i]->virtuemart_userinfo_id.'&'.JSession::getFormToken().'=1', $useXHTTML, $useSSL ). '" >'.'<i class="icon-delete"></i>'.vmText::_('COM_VIRTUEMART_USER_DELETE_ST').'</a></li>';
			}

			$addLink = '<a href="' . JRoute::_ ('index.php?option=com_virtuemart&view=user&task=' . $task . '&new=1&addrtype=ST&virtuemart_user_id[]=' . $userModel->getId ().'&'.JSession::getFormToken().'=1', $useXHTTML, $useSSL) . '"><span class="vmicon vmicon-16-editadd"></span> ';
			$addLink .= vmText::_ ('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL') . ' </a>';

			return $addLink . '<ul>' . join ('', $_shipTo) . '</ul>';
		}
	}


	/**
	 * used mostly in the email, to display the vendor address
	 * Attention, this function will be removed from any view.html.php
	 *
	 * @static
	 * @param        $vendorId
	 * @param string $lineSeparator
	 * @param array  $skips
	 * @return string
	 */
	static public function renderVendorAddress ($vendorId,$lineSeparator="<br />", $skips = array('name','username','email','agreed')) {

		$vendorModel = VmModel::getModel('vendor');
		$vendorFields = $vendorModel->getVendorAddressFields($vendorId);

		$vendorAddress = '';
		foreach ($vendorFields['fields'] as $field) {
			if(in_array($field['name'],$skips)) continue;
			if (!empty($field['value'])) {
				$vendorAddress .= $field['value'];
				if ($field['name'] != 'title' and $field['name'] != 'first_name' and $field['name'] != 'middle_name' and $field['name'] != 'zip') {
					$vendorAddress .= $lineSeparator;
				} else {
					$vendorAddress .= ' ';
				}
			}
		}
		return $vendorAddress;
	}


	/**
	 *
	 * @author Max Milbers
	 */
	static public function addProductToRecent ($productId) {

		$session = JFactory::getSession();
		$products_ids = $session->get( 'vmlastvisitedproductids', array(), 'vm' );
		$key = array_search( $productId, $products_ids );
		if($key !== FALSE) {
			unset($products_ids[$key]);
		}
		array_unshift( $products_ids, $productId );
		$products_ids = array_unique( $products_ids );

		$maxSize = (int)VmConfig::get('max_recent_products', 10);
		if(count( $products_ids )>$maxSize) {
			array_splice( $products_ids, $maxSize );
		}

		return $session->set( 'vmlastvisitedproductids', $products_ids, 'vm' );
	}

	/**
	 * Gives ids the recently by the shopper visited products
	 *
	 * @author Max Milbers
	 */
	static public function getRecentProductIds ($nbr = 3) {

		$session = JFactory::getSession();
		$ids = $session->get( 'vmlastvisitedproductids', array(), 'vm' );
		if(count( $ids )>$nbr) {
			array_splice( $ids, $nbr );
		}
		return $ids;
	}

	static public function sortLoadProductCustomsStockInd(&$products,$pModel){

		if(!$products) return;
		$customfieldsModel = VmModel::getModel ('Customfields');
		if (!class_exists ('vmCustomPlugin')) {
			require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
		}
		foreach($products as $i => $productItem){

			if (!empty($productItem->customfields)) {
				$product = clone($productItem);
				$customfields = array();
				foreach($productItem->customfields as $cu){
					$customfields[] = clone ($cu);
				}

				$customfieldsSorted = array();
				$customfieldsModel -> displayProductCustomfieldFE ($product, $customfields);
				$product->stock = $pModel->getStockIndicator($product);
				foreach ($customfields as $k => $custom) {
					if (!empty($custom->layout_pos)  ) {
						$customfieldsSorted[$custom->layout_pos][] = $custom;
					} else {
						$customfieldsSorted['normal'][] = $custom;
					}
					unset($customfields[$k]);
				}

				$product->customfieldsSorted = $customfieldsSorted;
				unset($product->customfields);
				$products[$i] = $product;
			} else {

				$productItem->stock = $pModel->getStockIndicator($productItem);
				$products[$i] = $productItem;
			}
		}
	}

	static public function calculateProductRowsHeights($products,$currency,$products_per_row){

		$rowsHeight = array();
		if(!$products) return $rowsHeight;

		$col = 1;
		$nb = 1;
		$row = 1;
		$BrowseTotalProducts = count($products);
		$rowHeights = array();


		foreach($products as $product){

			$priceRows = 0;
			//Lets calculate the height of the prices
			foreach($currency->_priceConfig as $name=>$values){
				if(!empty($currency->_priceConfig[$name][0])){
					if(!empty($product->prices[$name]) or $name == 'billTotal' or $name == 'billTaxAmount'){
						$priceRows++;
					}
				}
			}
			$rowHeights[$row]['price'][] = $priceRows;
			$position = 'addtocart';
			if(!empty($product->customfieldsSorted[$position])){

				//Hack for Multi variants
				$mvRows = 0;$i=0;
				foreach($product->customfieldsSorted[$position] as $custom){
					if($custom->field_type=='C'){
						//vmdebug('my custom',$custom);
						$mvRows += count($custom->selectoptions);
						$i++;
					}
				}
				$customs = count($product->customfieldsSorted[$position]);
				if(!empty($mvRows)){
					$customs = $customs - $i +$mvRows;
				}
			} else {
				$customs = 0;
			}
			$position = 'ontop';
			if(!empty($product->customfieldsSorted[$position])){
				foreach($product->customfieldsSorted[$position] as $custom){
					if($custom->field_type=='A'){
						$customs++;
					}
				}
			}

			$rowHeights[$row]['customfields'][] = $customs;
			$rowHeights[$row]['product_s_desc'][] = empty($product->product_s_desc)? 0:1;
			$rowHeights[$row]['avail'][] = empty($product->product_availability)? 0:1;

			$nb ++;

			if ($col == $products_per_row || $nb>$BrowseTotalProducts) {

				foreach($rowHeights[$row] as $group => $cols){

					$rowsHeight[$row][$group] = 0;
					foreach($cols as $c){
						$rowsHeight[$row][$group] =  max($rowsHeight[$row][$group],$c);
					}

				}
				$col = 1;
				$rowHeights = array();
				$row++;
			} else {
				$col ++;
			}

		}

		return $rowsHeight;
	}

	/**
	 * Renders sublayouts
	 *
	 * @param $name
	 * @param int $viewData viewdata for the rendered sublayout, do not remove
	 * @return string
	 */
	static public function renderVmSubLayout($name,$viewData=0){

		if (!class_exists ('VmView'))
			require(VMPATH_SITE . DS . 'helpers' . DS . 'vmview.php');
		$lPath = VmView::getVmSubLayoutPath ($name);

		if($lPath){
			ob_start ();
			include ($lPath);
			return ob_get_clean();
		} else {
			vmdebug('renderVmSubLayout layout not found '.$name);
		}

	}



	/**
	 * Prepares a view for rendering email, then renders and sends
	 *
	 * @param object $controller
	 * @param string $viewName View which will render the email
	 * @param string $recipient shopper@whatever.com
	 * @param array $vars variables to assign to the view
	 */
	//TODO this is quirk, why it is using here $noVendorMail, but everywhere else it is using $doVendor => this make logic trouble
	static public function renderMail ($viewName, $recipient, $vars = array(), $controllerName = NULL, $noVendorMail = FALSE,$useDefault=true) {

		self::loadOrderLanguages();

		$view = self::prepareViewForMail($viewName, $vars, $controllerName);
		$user = self::sendVmMail( $view, $recipient, $noVendorMail );

		if(isset($view->doVendor) && !$noVendorMail) {
			//We need to ensure the language for the vendor here
			$vendorUserId = VmModel::getModel('vendor')->getUserIdByVendorId(1);
			$vu = JFactory::getUser($vendorUserId);
			$vLang = $vu->getParam('admin_language',VmConfig::$jDefLangTag);

			self::loadOrderLanguages($vLang);
			self::sendVmMail( $view, $view->vendorEmail, TRUE );
		}

		return $user;

	}

	public static function prepareViewForMail($viewName, $vars, $controllerName = false) {
		if(!class_exists( 'VirtueMartControllerVirtuemart' )) require(VMPATH_SITE.DS.'controllers'.DS.'virtuemart.php');

		$controller = new VirtueMartControllerVirtuemart();
		// refering to http://forum.virtuemart.net/index.php?topic=96318.msg317277#msg317277
		$controller->addViewPath( VMPATH_SITE.DS.'views' );

		$view = $controller->getView( $viewName, 'html' );
		if(!$controllerName) $controllerName = $viewName;
		$controllerClassName = 'VirtueMartController'.ucfirst( $controllerName );
		if(!class_exists( $controllerClassName )) require(VMPATH_SITE.DS.'controllers'.DS.$controllerName.'.php');

		//refering to http://forum.virtuemart.net/index.php?topic=96318.msg317277#msg317277
		$view->addTemplatePath( VMPATH_SITE.'/views/'.$viewName.'/tmpl' );

		if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
		$template = VmTemplate::loadVmTemplateStyle();
		VmTemplate::setTemplate($template);
		if($template){
			if(is_array($template) and isset($template['template'])){
				$view->addTemplatePath( VMPATH_ROOT.DS.'templates'.DS.$template['template'].DS.'html'.DS.'com_virtuemart'.DS.$viewName );
			} else {
				$view->addTemplatePath( VMPATH_ROOT.DS.'templates'.DS.$template.DS.'html'.DS.'com_virtuemart'.DS.$viewName );
			}
		}

		foreach( $vars as $key => $val ) {
			$view->$key = $val;
		}

		return $view;
	}

	/**
	 * @deprecated use the class vmTemplate instead
	 * @return string
	 */
	public static function loadVmTemplateStyle(){

		static $res = null;
		if($res!==null) return $res;
		if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
		$res = VmTemplate::loadVmTemplateStyle();

	}


	/**
	 * This function sets the right template on the view
	 * @author Max Milbers
	 * @deprecated use class VmTemplates instead
	 */
	static function setVmTemplate ($view, $catTpl = 0, $prodTpl = 0, $catLayout = 0, $prodLayout = 0) {

		if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
		return VmTemplate::setVmTemplate($view, $catTpl, $prodTpl, $catLayout, $prodLayout);
	}

	static public function loadOrderLanguages($language = 0){

		$s = TRUE;
		$cache = true;
		vmLanguage::setLanguageByTag($language);

		//Shouldnt be necessary anylonger.
		vmLanguage::loadJLang('com_virtuemart', 0, $language, $cache);
		vmLanguage::loadJLang('com_virtuemart', $s, $language, $cache);
		vmLanguage::loadJLang('com_virtuemart_shoppers', $s, $language, $cache);
		vmLanguage::loadJLang('com_virtuemart_orders', $s, $language, $cache);

	}


	/**
	 * With this function you can use a view to sent it by email.
	 * Just use a task in a controller
	 *
	 * @param string $view for example user, cart
	 * @param string $recipient shopper@whatever.com
	 * @param bool $vendor true for notifying vendor of user action (e.g. registration)
	 */

	public static function sendVmMail (&$view, $recipient, $noVendorMail = FALSE) {

		VmConfig::ensureMemoryLimit(96);

		ob_start();

		$view->renderMailLayout( $noVendorMail, $recipient );
		$body = ob_get_contents();
		ob_end_clean();

		$subject = (isset($view->subject)) ? $view->subject : vmText::_( 'COM_VIRTUEMART_DEFAULT_MESSAGE_SUBJECT' );
		$mailer = JFactory::getMailer();
		$mailer->addRecipient( $recipient );

		$subjectMailer= '=?utf-8?B?'.base64_encode($subject).'?=';
		$mailer->setSubject(  html_entity_decode( $subjectMailer , ENT_QUOTES, 'UTF-8') );
		$mailer->isHTML( VmConfig::get( 'order_mail_html', TRUE ) );
		$mailer->setBody( $body );
		$replyTo = array();
		$replyToName = array();
 
		if(!$noVendorMail) {
			$replyTo[0] = $view->vendorEmail;
			$replyToName[0] = $view->vendor->vendor_name;
		} else {
			if(isset($view->orderDetails['details']) && isset($view->orderDetails['details']['BT'])) {
				$replyTo[0] = $view->orderDetails['details']['BT']->email;
				$replyToName[0] = $view->orderDetails['details']['BT']->first_name . ' ' . $view->orderDetails['details']['BT']->last_name;
			} else {
				if(isset($view->user->email) && $view->user->name) {
					$replyTo[0] = $view->user->email;
					$replyToName[0] = $view->user->name;
				} else {
					$replyTo[0] = $view->user['email'];
					$replyToName[0] = $view->user['name'];
				}
			}
		}
 
		if(count($replyTo)) {
			if(version_compare(JVERSION, '3.5', 'ge')) {
				$mailer->addReplyTo($replyTo, $replyToName);
			} else {
				$replyTo[1] = $replyToName[0];
				$mailer->addReplyTo($replyTo);
			}
		}
		if(isset($view->mediaToSend)) {
			foreach( (array)$view->mediaToSend as $media ) {
				$mailer->addAttachment( $media );
			}
		}

		// set proper sender
		$sender = array();
		if(!empty($view->vendorEmail) and VmConfig::get( 'useVendorEmail', 0 )) {
			$sender[0] = $view->vendorEmail;
			$sender[1] = $view->vendor->vendor_name;
		} else {
			// use default joomla's mail sender
			$app = JFactory::getApplication();
			$sender[0] = $app->getCfg( 'mailfrom' );
			$sender[1] = $app->getCfg( 'fromname' );
			if(empty($sender[0])){
				$config = JFactory::getConfig();
				$sender = array( $config->get( 'mailfrom' ), $config->get( 'fromname' ) );
			}
		}
		$mailer->setSender( $sender );

		$mailer->setSender($sender);
		$debug_email = VmConfig::get('debug_mail', false);
		if (VmConfig::get('debug_mail', false) == '1') {
			$debug_email = 'debug_email';

		}
		if ($debug_email) {
			if (!is_array($recipient)) {
				$recipient = array($recipient);
			}
			if (VmConfig::showDebug()) {
				vmdebug('Debug mail active, no mail sent. The mail to send subject ' . $subject . ' to "' . implode(' ', $recipient) . '" from ' . $sender[0] . ' ' . $sender[1] . ' ' . vmText::$language->getTag() . '<br>' . $body);
			} else {
				vmInfo('Debug mail active, no mail sent. The mail to send subject ' . $subject . ' to "' . implode(' ', $recipient) . '" from ' . $sender[0] . ' ' . $sender[1] . '<br>' . $body);
			}
			if ($debug_email == 'debug_email') {
				return true;
			}
		}
		try {
			$return = $mailer->Send();
		}
		catch (Exception $e)
		{
			VmConfig::$logDebug = true;
			vmdebug('Error sending mail ',$e);
			vmError('Error sending mail ');
			// this will take care of the error message
			return false;
		}


		return $return; 
	}




	function sendRatingEmailToVendor ($data) {

		$vars = array();
		$productModel = VmModel::getModel ('product');
		$product = $productModel->getProduct ($data['virtuemart_product_id']);
		$vars['subject'] = vmText::sprintf('COM_VIRTUEMART_RATING_EMAIL_SUBJECT', $product->product_name);
		$vars['mailbody'] = vmText::sprintf('COM_VIRTUEMART_RATING_EMAIL_BODY', $product->product_name);

		$vendorModel = VmModel::getModel ('vendor');
		$vendor = $vendorModel->getVendor ($product->virtuemart_vendor_id);
		$vendorModel->addImages ($vendor);
		$vars['vendor'] = $vendor;
		$vars['vendorEmail'] = $vendorModel->getVendorEmail ($product->virtuemart_vendor_id);
		$vars['vendorAddress'] = shopFunctionsF::renderVendorAddress ($product->virtuemart_vendor_id);

	    shopFunctionsF::renderMail ('productdetails', $vars['vendorEmail'], $vars, 'productdetails', TRUE);

	}

	static public function getTaxNameWithValue($name, $value){

		$value = rtrim(trim($value,'0'),'.');
		if(empty($value)) return $name;
		if(strpos($name,(string)$value)!==false){
			$tax = $name;
		} else {
			$tax = $name.' '.$value.'%';
		}
		return $tax;
	}

	/**
	 *
	 * Enter description here ...
	 * @author Max Milbers
	 * @author Iysov
	 * @param string $string
	 * @param int $maxlength
	 * @param string $suffix
	 */
	static public function limitStringByWord ($string, $maxlength, $suffix = '') {

		if(function_exists( 'mb_strlen' )) {
			// use multibyte functions by Iysov
			if(mb_strlen( $string )<=$maxlength) return $string;
			$string = mb_substr( $string, 0, $maxlength );
			$index = mb_strrpos( $string, ' ' );
			if($index === FALSE) {
				return $string;
			} else {
				return mb_substr( $string, 0, $index ).$suffix;
			}
		} else { // original code here
			if(strlen( $string )<=$maxlength) return $string;
			$string = substr( $string, 0, $maxlength );
			$index = strrpos( $string, ' ' );
			if($index === FALSE) {
				return $string;
			} else {
				return substr( $string, 0, $index ).$suffix;
			}
		}
	}

	static public function vmSubstr($str,$s,$e = null){
		if(function_exists( 'mb_strlen' )) {
			return mb_substr( $str, $s, $e );
		} else {
			return substr( $str, $s, $e );
		}
	}

	/**
	 * Admin UI Tabs
	 * Gives A Tab Based Navigation Back And Loads The Templates With A Nice Design
	 * @param $load_template = a key => value array. key = template name, value = Language File contraction
	 * @example 'shop' => 'COM_VIRTUEMART_ADMIN_CFG_SHOPTAB'
	 */
	static function buildTabs ($view, $load_template = array()) {

		vmJsApi::addJScript( 'vmtabs' );
		$html = '<div id="ui-tabs">';
		$i = 1;
		foreach( $load_template as $tab_content => $tab_title ) {
			$html .= '<div id="tab-'.$i.'" class="tabs" title="'.vmText::_( $tab_title ).'">';
			$html .= $view->loadTemplate( $tab_content );
			$html .= '<div class="clear"></div>
			    </div>';
			$i++;
		}
		$html .= '</div>';
		echo $html;
	}


	/**
	 * Checks if Joomla language keys exist and combines it according to existing keys.
	 * @string $pkey : primary string to search for Language key (must have %s in the string to work)
	 * @string $skey : secondary string to search for Language key
	 * @return string
	 * @author Max Milbers
	 * @author Patrick Kohl
	 */
	static function translateTwoLangKeys ($pkey, $skey) {

		$upper = strtoupper( $pkey ).'_2STRINGS';
		if(vmText::_( $upper ) !== $upper) {
			return vmText::sprintf( $upper, vmText::_( $skey ) );
		} else {
			return vmText::_( $pkey ).' '.vmText::_( $skey );
		}
	}

	
	/**
	 * Get Virtuemart itemID from joomla menu
	 * @author Maik K�nnemann
	 */
	static function getMenuItemId( $lang = '*' ) {

		$itemID = '';

		if(empty($lang)) $lang = '*';

		$component	= JComponentHelper::getComponent('com_virtuemart');

		$db = JFactory::getDbo();
		$q = 'SELECT * FROM `#__menu` WHERE `component_id` = "'. $component->id .'" and `language` = "'. $lang .'"';
		$db->setQuery( $q );
		$items = $db->loadObjectList();
		if(empty($items)) {
			$q = 'SELECT * FROM `#__menu` WHERE `component_id` = "'. $component->id .'" and `language` = "*"';
			$db->setQuery( $q );
			$items = $db->loadObjectList();
		}

		foreach ($items as $item) {
			if(strstr($item->link, 'view=virtuemart')) {
				$itemID = $item->id;
				break;
			}
		}

		if(empty($itemID) && !empty($items[0]->id)) {
			$itemID = $items[0]->id;
		}

		return $itemID;
	}

	static function triggerContentPlugin(  &$article, $context, $field) {
	// add content plugin //
		$dispatcher = JDispatcher::getInstance ();
		JPluginHelper::importPlugin ('content');
		$article->text = $article->$field;

		jimport ('joomla.registry.registry');
		$params = new JRegistry('');
		if (!isset($article->event)) {
			$article->event = new stdClass();
		}
		$results = $dispatcher->trigger ('onContentPrepare', array('com_virtuemart.'.$context, &$article, &$params, 0));
		// More events for 3rd party content plugins
		// This do not disturb actual plugins, because we don't modify $vendor->text
		$res = $dispatcher->trigger ('onContentAfterTitle', array('com_virtuemart.'.$context, &$article, &$params, 0));
		$article->event->afterDisplayTitle = trim (implode ("\n", $res));

		$res = $dispatcher->trigger ('onContentBeforeDisplay', array('com_virtuemart.'.$context, &$article, &$params, 0));
		$article->event->beforeDisplayContent = trim (implode ("\n", $res));

		$res = $dispatcher->trigger ('onContentAfterDisplay', array('com_virtuemart.'.$context, &$article, &$params, 0));
		$article->event->afterDisplayContent = trim (implode ("\n", $res));

		$article->$field = $article->text;
	}

	static public function mask_string($cc, $mask_char='X'){
		return str_pad(substr($cc, -4), strlen($cc), $mask_char, STR_PAD_LEFT);
	}

	/*
	 * get The invoice Folder Name
	 * @return the invoice folder name
	 */
	static function getInvoiceFolderName() {
		return   'invoices' ;
	}

	/**
	 * Get the file name for the invoice or deliverynote.
	 * The layout argument currently is either 'invoice' or 'deliverynote'
	 * @return The full filename of the invoice/deliverynote without file extension, sanitized not to contain problematic characters like /
	 */
	static function getInvoiceName($invoice_number, $layout='invoice'){

		$tmpT = false;
		vmLanguage::loadJLang('com_virtuemart_orders', true);
		if(VmConfig::get('invoiceNameInShopLang',true)){
			$tmpT = VmConfig::$vmlangTag;
			vmLanguage::setLanguageByTag(VmConfig::$jDefLangTag);
		}
		$prefix = vmText::_('COM_VIRTUEMART_FILEPREFIX_'.strtoupper($layout));
		if($tmpT!=false){
			vmLanguage::setLanguageByTag($tmpT);
		}
		if($prefix == 'COM_VIRTUEMART_FILEPREFIX_'.strtoupper($layout)){
			$prefix = 'vm'.$layout.'_';
		}
		return $prefix.preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $invoice_number);
	}

	static public function getInvoiceDownloadButton($orderInfo, $descr = 'COM_VIRTUEMART_PRINT', $icon = 'system/pdf_button.png'){
		$html = '';
		if(!empty($orderInfo->invoiceNumber)){
			if(!$sPath = shopFunctions::checkSafePath()){
				return $html;
			}
			$path = $sPath.self::getInvoiceFolderName().DS.self::getInvoiceName($orderInfo->invoiceNumber).'.pdf';
			//$path .= preg_replace('/[^A-Za-z0-9_\-\.]/', '_', 'vm'.$layout.'_'.$orderInfo->invoiceNumber.'.pdf');
			if(file_exists($path)){
				$link = JURI::root(true).'/index.php?option=com_virtuemart&view=invoice&layout=invoice&format=pdf&tmpl=component&order_number='.$orderInfo->order_number.'&order_pass='.$orderInfo->order_pass;
				$pdf_link = "<a href=\"javascript:void window.open('".$link."', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\"  >";
				$pdf_link .= JHtml::_('image',$icon, vmText::_($descr), NULL, true);
				$pdf_link .= '</a>';
				$html = $pdf_link;
			}
		}
		return $html;
	}

	/*
	 * @author Valerie
	 */
	static function InvoiceNumberReserved ($invoice_number) {

		if (($pos = strpos ($invoice_number, 'reservedByPayment_')) === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	static public function renderCaptcha($config = 'reg_captcha',$id = 'dynamic_recaptcha_1'){

		if(VmConfig::get ($config) and JFactory::getUser()->guest==1 ){

			JPluginHelper::importPlugin('captcha');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onInit',$id);
			if(version_compare(JVERSION, '3.5', 'ge')){
				$plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
				if(!empty($plugin->params)){
					$params = new JRegistry($plugin->params);
					if ($params->get('version') != '1.0') {
						return '<div id="jform_captcha" class="g-recaptcha  required" data-sitekey="'.$params->get('public_key').'" data-theme="'.$params->get('theme2').'" data-size="normal"></div>';
					}
				}

			}
			JHTML::_('behavior.framework');
			return '<div id="'.$id.'"></div>';
		}
		return '';
	}
}
