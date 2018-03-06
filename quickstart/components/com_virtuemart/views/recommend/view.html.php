<?php
/**
*
* Product details view
*
* @package VirtueMart
* @subpackage
* @author RolandD
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 2796 2011-03-01 11:29:16Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');

/**
* Product details
*
* @package VirtueMart
* @author Max Milbers
*/
class virtuemartViewrecommend extends VmView {

	/**
	* Collect all data to show on the template
	*
	* @author Max Milbers
	*/
	function display($tpl = null) {

		$app = JFactory::getApplication();
		if(!VmConfig::get('show_emailfriend',false)){

			$app->redirect(JRoute::_('index.php?option=com_virtuemart'));
		}

		$this->login = '';
		if(!VmConfig::get('recommend_unauth',false)){
			$user = JFactory::getUser();
			if($user->guest){
				$this->login = shopFunctionsF::getLoginForm(false);
				//$app->redirect(JRoute::_('index.php?option=com_virtuemart','JGLOBAL_YOU_MUST_LOGIN_FIRST'));
			}
		}

		// Load the product
		$productModel = VmModel::getModel('product');
		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id',0);

		$this->product = $productModel->getProduct ($virtuemart_product_id);
		$layout = $this->getLayout();
		if($layout != 'form' and $layout != 'mail_confirmed'){
			$this->renderMailLayout('','');
			return true;
		}

		$show_prices  = VmConfig::get('show_prices',1);
		if($show_prices == '1'){
			if(!class_exists('calculationHelper')) require(VMPATH_ADMIN.DS.'helpers'.DS.'calculationh.php');
		}
		$this->assignRef('show_prices', $show_prices);
		$document = JFactory::getDocument();
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');
		/* add javascript for price and cart */
		//vmJsApi::jPrice();

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$task = vRequest::getCmd('task');

		if (!class_exists('VmImage'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');


		if(empty($virtuemart_product_id)){
			self::showLastCategory($tpl);
			return;
		}

		//$product = $productModel->getProduct($virtuemart_product_id);
		/* Set Canonic link */
		$format = vRequest::getCmd('format', 'html');
		if ($format == 'html') {
			$document->addHeadLink( JUri::getInstance()->toString(array('scheme', 'host', 'port')).JRoute::_($this->product->link) , 'canonical', 'rel', '' );
		}

		/* Set the titles */
		$document->setTitle(vmText::sprintf('COM_VIRTUEMART_PRODUCT_DETAILS_TITLE',$this->product->product_name.' - '.vmText::_('COM_VIRTUEMART_PRODUCT_RECOMMEND')));

		if(empty($this->product)){
			self::showLastCategory($tpl);
			return;
		}

		$productModel->addImages($this->product,1);


		/* Load the category */
		$category_model = VmModel::getModel('category');
		/* Get the category ID */
		$virtuemart_category_id = vRequest::getInt('virtuemart_category_id');
		if ($virtuemart_category_id == 0 && !empty($this->product)) {
			if (array_key_exists('0', $this->product->categories)) $virtuemart_category_id = $this->product->categories[0];
		}

		if(!class_exists('shopFunctionsF'))require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		shopFunctionsF::setLastVisitedCategoryId($virtuemart_category_id);

		if($category_model){
			$category = $category_model->getCategory($virtuemart_category_id);
			$this->assignRef('category', $category);
			$pathway->addItem(vmText::_($category->category_name),JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$virtuemart_category_id, FALSE));
		}

		//$pathway->addItem(vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS'), $uri->toString(array('path', 'query', 'fragment')));
		$pathway->addItem($this->product->product_name,JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id='.$virtuemart_category_id.'&virtuemart_product_id='.$this->product->virtuemart_product_id, FALSE));

		// for askquestion
		$pathway->addItem( vmText::_('COM_VIRTUEMART_PRODUCT_ASK_QUESTION'));

		/* Check for editing access */
		/** @todo build edit page */
		/* Load the user details */
		$this->user = JFactory::getUser();

		if ($this->product->metadesc) {
			$document->setDescription( $this->product->metadesc );
		}
		if ($this->product->metakey) {
			$document->setMetaData('keywords', $this->product->metakey);
		}

		if ($mainframe->getCfg('MetaTitle') == '1') {
			$document->setMetaData('title', $this->product->product_s_desc);  //Maybe better product_name
		}
		if ($mainframe->getCfg('MetaAuthor') == '1') {
			$document->setMetaData('author', $this->product->metaauthor);
		}

		$this->captcha = shopFunctionsF::renderCaptcha('ask_captcha');

		parent::display($tpl);
	}

	function renderMailLayout($doVendor, $recipient) {

		$this->comment = nl2br(vRequest::getString('comment'));
		$this->name = vRequest::getString('name');

		if (VmConfig::get ('order_mail_html')) {
			$tpl = 'mail_html';
		} else {
			$tpl = 'mail_raw';
		}
		$this->setLayout ($tpl);

		// Load the product
		$productModel = VmModel::getModel('product');
		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id',0);

		$this->product = $productModel->getProduct ($virtuemart_product_id);
		$productModel->addImages($this->product);

		$layout = $this->getLayout();
		//if($layout != 'form' and $layout != 'mail_confirmed'){

		$user = JFactory::getUser ();
		$vars['user'] = array('name' => $user->name, 'email' =>  $user->email);

		$vars['vendorEmail'] = $user->email;
		$vendorModel = VmModel::getModel ('vendor');
		$this->vendor = $vendorModel->getVendor ($this->product->virtuemart_vendor_id);

		$vendorModel->addImages ($this->vendor);
		$this->vendor->vendorFields = $vendorModel->getVendorAddressFields();
		$vars['vendorAddress']= shopFunctionsF::renderVendorAddress($this->product->virtuemart_vendor_id, ' - ');

		$this->vendor->vendor_name =$user->name;

		foreach( $vars as $key => $val ) {
			$this->$key = $val;
		}

		$this->subject = vmText::sprintf('COM_VIRTUEMART_RECOMMEND_PRODUCT',$this->name, $this->product->product_name);

		$this->isMail = true;
		parent::display();
	}

	public function showLastCategory($tpl) {
		$this->prepareContinueLink();
		parent::display ($tpl);
	}

}

// pure php no closing tag