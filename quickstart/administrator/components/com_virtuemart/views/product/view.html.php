<?php
/**
 *
 * View class for the product
 *
 * @package	VirtueMart
 * @subpackage
 * @author
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9802 2018-03-20 15:22:11Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author RolandD,Max Milbers
 */
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

class VirtuemartViewProduct extends VmViewAdmin {

	function display($tpl = null) {

		// Get the task
		$task = vRequest::getCmd('task',$this->getLayout());
		$this->assignRef('task', $task);

		// Load helpers
		if (!class_exists('CurrencyDisplay'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
		if (!class_exists('VmImage'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');

		$model = VmModel::getModel();
		$this->assignRef('model', $model);
		$app = JFactory::getApplication();

		// Handle any publish/unpublish
		switch ($task) {
			case 'add':
			case 'edit':

				//this was in the controller for the edit tasks, we need this for the access by FE
				//$this->addTemplatePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'product'.DS.'tmpl');
				vmLanguage::loadJLang('com_virtuemart_orders',TRUE);
				vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);

				$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');

				if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
					$virtuemart_product_id = (int)$virtuemart_product_id[0];
				} else {
					$virtuemart_product_id = (int)$virtuemart_product_id;
				}

				$product = $model->getProductSingle($virtuemart_product_id,false);

				if(!empty($product->_loadedWithLangFallback)){
					vmInfo('COM_VM_LOADED_WITH_LANGFALLBACK',$product->_loadedWithLangFallback);
				}
				$this->setOrigLang($product);

				$superVendor =  vmAccess::isSuperVendor();
				$vendorId = vmAccess::getVendorId();

				if(!empty($product->virtuemart_vendor_id) and $superVendor !=1 and $vendorId!=$product->virtuemart_vendor_id){
					$app->redirect( 'index.php?option=com_virtuemart&view=virtuemart', vmText::_('COM_VIRTUEMART_ALERTNOTAUTHOR'), 'error');
				}
				if(!empty($product->product_parent_id)){
					$product_parent= $model->getProductSingle($product->product_parent_id,false);
				}


				$customfields = VmModel::getModel ('Customfields');

				$product->allIds[] = $product->virtuemart_product_id;
				if(!empty($product->product_parent_id)) $product->allIds[] = $product->product_parent_id;

				$product->customfields = $customfields->getCustomEmbeddedProductCustomFields ($product->allIds);

				//Fallback for categories inherited by parent to correctly calculate the prices
				if(empty($product->categories) and !empty($product_parent->categories)){
					$product->categories = $product_parent->categories;
				}

				//Get the shoppergoup list - Cleanshooter Custom Shopper Visibility
				if (!isset($product->shoppergroups)) $product->shoppergroups = 0;
				$this->shoppergroupList = ShopFunctions::renderShopperGroupList($product->shoppergroups);

				// Load the product price
				if(!class_exists('calculationHelper')) require(VMPATH_ADMIN.DS.'helpers'.DS.'calculationh.php');

				//Do we need the children? If there is a C customfield, we dont want them
				$isCustomVariant = false;
				foreach($product->customfields as $custom){
					if($custom->field_type == 'C' and $custom->virtuemart_product_id == $virtuemart_product_id){
						$isCustomVariant = true;
						break;
					}
				}
				if(!$isCustomVariant){
					$product_childIds = $model->getProductChildIds($virtuemart_product_id);

					$product_childs = array();
					$childs = 0;
					$maxChilds = VmConfig::get('maxChilds',80);
					foreach($product_childIds as $id){
						if($childs++>$maxChilds) break;
						$product_childs[] = $model->getProductSingle($id,false);
					}
					$this->product_childs = $product_childs;
				}


				if(!class_exists('VirtueMartModelConfig')) require(VMPATH_ADMIN .'/models/config.php');
				$productLayouts = VirtueMartModelConfig::getLayoutList('productdetails');
				$this->productLayouts = $productLayouts;

				// Load Images
				$model->addImages($product);

				if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
				$vmtemplate = VmTemplate::loadVmTemplateStyle();
				$this->imagePath = shopFunctions::getAvailabilityIconUrl($vmtemplate);


				// Load the vendors
				$vendor_model = VmModel::getModel('vendor');

				$lists['vendors'] = '';
				if($this->showVendors()){
					$lists['vendors'] = Shopfunctions::renderVendorList($product->virtuemart_vendor_id);
				}
				// Load the currencies
				$currency_model = VmModel::getModel('currency');

				$vendor_model->setId(vmAccess::isSuperVendor());
				$this->vendor = $vendor_model->getVendor();

				$currency = $currency_model->getCurrency($this->vendor->vendor_currency);
				$this->vendor_currency_symb = $currency->currency_symbol;


				$lists['manufacturers'] = shopFunctions::renderManufacturerList($product->virtuemart_manufacturer_id,true);


				if(!empty($product->product_weight_uom)){
					$product_weight_uom = $product->product_weight_uom;
				} else if(!empty($product_parent)){
					$product_weight_uom = $product_parent->product_weight_uom;
				} else {
					$product_weight_uom = VmConfig::get('weight_unit_default');
				}

				if(!empty($product->product_lwh_uom)){
					$product_lwh_uom = $product->product_lwh_uom;
				} else if(!empty($product_parent)){
					$product_lwh_uom = $product_parent->product_lwh_uom;
				} else {
					$product_lwh_uom = VmConfig::get('lwh_unit_default');
				}

				if(!empty($product->product_unit)){
					$product_unit = $product->product_unit;
				} else if(!empty($product_parent)){
					$product_unit = $product_parent->product_unit;
				} else {
					$product_unit = VmConfig::get('product_unit_default','KG');
				}

				$lists['product_weight_uom'] = ShopFunctions::renderWeightUnitList('product_weight_uom',$product_weight_uom);
				$lists['product_iso_uom'] = ShopFunctions::renderUnitIsoList('product_unit',$product_unit);
				$lists['product_lwh_uom'] = ShopFunctions::renderLWHUnitList('product_lwh_uom', $product_lwh_uom);

				if( empty( $product->product_available_date )) {
					$product->product_available_date = date("Y-m-d") ;
				}
				$waitinglistmodel = VmModel::getModel('waitinglist');
				/* Load waiting list */
				if ($product->virtuemart_product_id) {
					//$waitinglist = $this->get('waitingusers', 'waitinglist');
					$waitinglist = $waitinglistmodel->getWaitingusers($product->virtuemart_product_id);
					$this->assignRef('waitinglist', $waitinglist);
				}

				$option = vRequest::getCmd('option');
				//$lists['filter_order'] = $app->getUserStateFromRequest($option.'filter_order_orders', 'filter_order', 'email', 'cmd');
				//$lists['filter_order_Dir'] = $app->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');

				$lists['filter_order'] = $app->getUserStateFromRequest('com_virtuemart.product.productShoppers.filter_order', 'filter_order', 'email', 'cmd');
				$lists['filter_order_Dir'] = $app->getUserStateFromRequest('com_virtuemart.product.productShoppers.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');


				$order_status = vRequest::getvar('order_status',array('S'));
				$productShoppers = $model->getProductShoppersByStatus($product->virtuemart_product_id,$order_status,$lists['filter_order'],$lists['filter_order_Dir'] );
				$this->assignRef('productShoppers', $productShoppers);
				$orderstatusModel = VmModel::getModel('orderstatus');
				$lists['OrderStatus'] = $orderstatusModel->renderOSList($order_status,'order_status',TRUE);

				// Add the virtuemart_shoppergroup_ids
				$cid = JFactory::getUser()->id;

				$this->activeShoppergroups = shopfunctions::renderGuiList($cid,'shoppergroups','shopper_group_name','shoppergroup','vmuser_shoppergroups','virtuemart_user_id');
				if(empty($this->activeShoppergroups) ){
					$shoppergroupModel = VmModel::getModel('shoppergroup');
					$this->activeShoppergroups = vmText::_($shoppergroupModel->getDefault(0)->shopper_group_name);
				}

				if (!class_exists ('calculationHelper')) {
					require(VMPATH_ADMIN .'/helpers/calculationh.php');
				}
				$this->calculator = calculationHelper::getInstance ();
				$this->deliveryCountry = ShopFunctions::getCountryByID ($this->calculator->_deliveryCountry,  'country_3_code');
				$this->deliveryState = ShopFunctions::getStateByID ($this->calculator->_deliveryState,  'state_3_code');

				// Load protocustom lists
				$customModel = VmModel::getModel ('custom');

				$this->fieldTypes = VirtueMartModelCustom::getCustomTypes();

				$customsList = $customModel->getCustomsList ();
				$attribs='style= "width: 300px;"';
				$customlist = JHtml::_('select.genericlist', $customsList,'customlist', $attribs,'value','text',null,false,true);

				$this->assignRef('customsList', $customlist);

				if ($product->product_parent_id > 0) {

					// Set up labels
					$info_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_INFO_LBL');
					$status_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_STATUS_LBL');
					$dim_weight_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_DIM_WEIGHT_LBL');
					$images_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_IMAGES_LBL');
					$delete_message = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DELETE_ITEM_MSG');
				}
				else {
					if ($task == 'add') $action = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT_LBL');
					else $action = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_UPDATE_ITEM_LBL');

					$info_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_INFO_LBL');
					$status_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_STATUS_LBL');
					$dim_weight_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL');
					$images_label = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_IMAGES_LBL');
					$delete_message = vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DELETE_PRODUCT_MSG');
				}


				$this->assignRef('product', $product);

				$this->assignRef('product_parent', $product_parent);
				/* Assign label values */
				$this->assignRef('action', $action);
				$this->assignRef('info_label', $info_label);
				$this->assignRef('status_label', $status_label);
				$this->assignRef('dim_weight_label', $dim_weight_label);
				$this->assignRef('images_label', $images_label);
				$this->assignRef('delete_message', $delete_message);
				$this->assignRef('lists', $lists);
				// Toolbar
				if ($product->product_sku) $sku=' ('.$product->product_sku.')'; else $sku="";
				//if (!empty($product->canonCatLink)) $canonLink = '&virtuemart_category_id=' . $product->canonCatLink; else $canonLink = '';
				if(!empty($product->virtuemart_product_id)){
					if (!class_exists ('shopFunctionsF')) require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
					$menuItemID = shopFunctionsF::getMenuItemId(JFactory::getLanguage()->getTag());
					$canonLink='';
					if($product->canonCatId) $canonLink = '&virtuemart_category_id='.$product->canonCatId;

					$text = '<a href="'.juri::root().'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.$canonLink.'&Itemid='. $menuItemID .'" target="_blank" >'. $product->product_name.$sku.'<span class="vm2-modallink"></span></a>';
					if($app->isSite()){
						$bar = JToolBar::getInstance('toolbar');
						$bar->appendButton('Link', 'back', 'COM_VIRTUEMART_LEAVE_TO_PRODUCT', juri::root().'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.$canonLink.'&Itemid='. $menuItemID);
					}
				} else {
					$text = $product->product_name.$sku;
				}
				$this->SetViewTitle('PRODUCT',$text);

				$this->addStandardEditViewCommands ($product->virtuemart_product_id);

				VmJsApi::chosenDropDowns();
				$this->ajaxCategoryDropDown('categories');

				break;

			case 'massxref_cats':
			case 'massxref_cats_exe':
				$this->SetViewTitle('PRODUCT_MASSXREF');

				$showVendors = $this->showVendors();
				$this->assignRef('showVendors',$showVendors);

				$keyWord ='';
				$catmodel = VmModel::getModel('category');
				$this->assignRef('catmodel',	$catmodel);
				//$this->addStandardDefaultViewCommands();
				$this->addStandardDefaultViewLists($catmodel,'category_name');

				$session = JFactory::getSession();
				$reset = $session->get('reset_pag', false, 'vm');
				$limit = '';
				if($reset){
					$limit = 0;
					$session->set('reset_pag', false,'vm');
				}
				$this->categories = $catmodel->getCategoryTree(0,0,false,$this->lists['search'],$limit);
				foreach($this->categories as $i=>$c){
					$this->categories[$i]->productcount = $catmodel->countProducts($this->categories[$i]->virtuemart_category_id);
				}
				$catpagination = $catmodel->getPagination();
				$this->assignRef('catpagination', $catpagination);

				$this->setLayout('massxref');

				JToolBarHelper::custom('massxref_cats_exe', 'new', 'new', vmText::_('COM_VIRTUEMART_PRODUCT_XREF_CAT_EXE'), false);
				$this->ajaxCategoryDropDown('top_category_id');
				break;

			case 'massxref_sgrps':
			case 'massxref_sgrps_exe':
				$sgrpmodel = VmModel::getModel('shoppergroup');
				$this->addStandardDefaultViewLists($sgrpmodel);

				$shoppergroups = $sgrpmodel->getShopperGroups(false, true);
				$this->assignRef('shoppergroups',	$shoppergroups);

				$sgrppagination = $sgrpmodel->getPagination();
				$this->assignRef('sgrppagination', $sgrppagination);

				$this->setLayout('massxref');

				JToolBarHelper::custom('massxref_sgrps_exe', 'new', 'new', vmText::_('COM_VIRTUEMART_PRODUCT_XREF_SGRPS_EXE'), false);

				break;

		default:
			$product_parent = false;
			if ($product_parent_id=vRequest::getInt('product_parent_id',false) ) {
				$product_parent= $model->getProductSingle($product_parent_id,false);

				if($product_parent){
					$title='PRODUCT_CHILDREN_LIST' ;
					$link_to_parent =  JHtml::_('link', JRoute::_('index.php?view=product&task=edit&virtuemart_product_id='.$product_parent->virtuemart_product_id.'&option=com_virtuemart'), $product_parent->product_name, array('title' => vmText::_('COM_VIRTUEMART_EDIT_PARENT').' '.$product_parent->product_name));
					$msg= vmText::_('COM_VIRTUEMART_PRODUCT_OF'). " ".$link_to_parent;
				} else {
					$title='PRODUCT_CHILDREN_LIST' ;
					$msg= 'Parent with product_parent_id '.$product_parent_id.' not found';
				}

			} else {
				$title='PRODUCT';
				$msg="";
			}

			$this->SetViewTitle($title, $msg );

			$this->addStandardDefaultViewLists($model,'created_on');

			$superVendor = vmAccess::isSuperVendor();
			if(empty($superVendor)){
				$productlist = array();
			} else {
				//Get the list of products
				$productlist = $model->getProductListing(false,false,false,false,true);
			}
			$this->filter_product = $model->filter_product;

			$now = getdate();
			$nowstring = $now["hours"].":".substr('0'.$now["minutes"], -2).' '.$now["mday"].".".$now["mon"].".".$now["year"];
			$this->search_date = vRequest::getVar('search_date', $nowstring);

			//The pagination must now always set AFTER the model load the listing
			$this->pagination = $model->getPagination();

			VmJsApi::chosenDropDowns();

			//Get the category tree
			$this->virtuemart_category_id=$this->categoryId = $model->virtuemart_category_id; //OSP switched to filter in model, was vRequest::getInt('virtuemart_category_id');

			$this->ajaxCategoryDropDown('virtuemart_category_id');

			//Load the product price
			if(!class_exists('calculationHelper')) require(VMPATH_ADMIN.DS.'helpers'.DS.'calculationh.php');

			$vendor_model = VmModel::getModel('vendor');
			$productreviews = VmModel::getModel('ratings');

			$this->mfTable = $model->getTable ('manufacturers');

			$this->catTable = $model->getTable ('categories');

			$this->lists['vendors'] = '';
			if($this->showVendors()){
				$this->lists['vendors'] = Shopfunctions::renderVendorList($model->virtuemart_vendor_id);
			}


			foreach ($productlist as $virtuemart_product_id => $product) {
				if(empty($product->virtuemart_media_id)){
					$product->mediaitems = 0;
				} else {
					$product->mediaitems = count($product->virtuemart_media_id);
				}

				$product->reviews = $productreviews->countReviewsForProduct($product->virtuemart_product_id);

				$vendor_model->setId($product->virtuemart_vendor_id);
				$vendor = $vendor_model->getVendor();

				$currencyDisplay = CurrencyDisplay::getInstance($vendor->vendor_currency,$vendor->virtuemart_vendor_id);


				$product->product_price_display = '';
				$class = '';
				if(empty($product->allPrices)) {
					$model->getRawProductPrices($product,1,array(),false,true);
					$class = 'class="pr-price-derivated"';
				}
				if(!empty($product->allPrices)) {
					$product->product_price_display = '<span '.$class.'>';
					foreach($product->allPrices as $price){
						//vmdebug('my price',$price);
						$product->product_price_display .= $currencyDisplay->priceDisplay($price['product_price'],(int)$price['product_currency'],1,true) .'<br>';
					}
					$product->product_price_display = substr($product->product_price_display,0,-4) . '</span>';
					/*$product->product_price_display = $currencyDisplay->priceDisplay($product->allPrices[$product->selectedPrice]['product_price'],(int)$product->allPrices[$product->selectedPrice]['product_currency'],1,true);*/
				} else {
					$product->product_price_display = vmText::_('COM_VIRTUEMART_NO_PRICE_SET');
				}

				/*if(!empty($product->allPrices[$product->selectedPrice]['product_price']) && !empty($product->allPrices[$product->selectedPrice]['product_currency']) ){
					$product->product_price_display = $currencyDisplay->priceDisplay($product->allPrices[$product->selectedPrice]['product_price'],(int)$product->allPrices[$product->selectedPrice]['product_currency'],1,true);
				} else if(!empty($product->allPrices) and count($product->allPrices)>1 ) {
					$product->product_price_display = vmText::_('COM_VIRTUEMART_MULTIPLE_PRICES');
				} else {
					$product->product_price_display = vmText::_('COM_VIRTUEMART_NO_PRICE_SET');
				}*/

				// Write the first 5 categories in the list
				$product->categoriesList = '';
				if (!empty($product->categories[0])) {
					$product->categoriesList = shopfunctions::renderGuiList($product->categories,'categories','category_name','category');
				}

				// Write the first 5 manufacturers in the list
				$product->manuList = '';
				if (!empty($product->virtuemart_manufacturer_id[0])) {
					$product->manuList = shopfunctions::renderGuiList($product->virtuemart_manufacturer_id,'manufacturers','mf_name','manufacturer');
				}

				$product->parent_link = '';
				if ($product->product_parent_id ) {
					$product->parent_link = $this->displayLinkToParent($product->product_parent_id);
				}

				$product->childlist_link = VirtuemartViewProduct::displayLinkToChildList($product->virtuemart_product_id , $product->product_name);

				//vmdebug('my '.$product->parent_link);
			}

			$mf_model = VmModel::getModel('manufacturer');
			$manufacturers = $mf_model->getManufacturerDropdown();
			$this->assignRef('manufacturers',	$manufacturers);

			/* add Search filter in lists*/
			/* Search type */
			$options = array( '' => vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'),
		    				'parent' => vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_PARENT_PRODUCT'),
							'product' => vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRODUCT'),
							'price' => vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRICE'),
							'withoutprice' => vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_WITHOUTPRICE'),
							'featured' => vmText::_('COM_VIRTUEMART_SHOW_FEATURED'),
							'topten' => vmText::_('COM_VIRTUEMART_SHOW_TOPTEN'),
							'latest' => vmText::_('COM_VIRTUEMART_LATEST_PRODUCT'),
							'discontinued' => vmText::_('COM_VIRTUEMART_PRODUCT_FORM_DISCONTINUED'),
			);
			$this->lists['search_type'] = VmHTML::selectList('search_type', $model->search_type,$options, 1, "", 'style="width:130px;"');

			/* Search order */
			$options = array( 	'bf' => vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_BEFORE'),
								'af' => vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_AFTER')
			);
			$this->lists['search_order'] = VmHTML::selectList('search_order', $model->search_order,$options, 1, "", 'style="width:100px;"');

			// Toolbar
			if (vmAccess::manager('product.edit')) {
				JToolBarHelper::custom('massxref_cats', 'new', 'new', vmText::_('COM_VIRTUEMART_PRODUCT_XREF_CAT'), true);
				JToolBarHelper::custom('massxref_sgrps', 'new', 'new', vmText::_('COM_VIRTUEMART_PRODUCT_XREF_SGRPS'), true);
			}
			if (vmAccess::manager('product.create')) {
				if($product_parent){
					$product_parent = true;
				}
				JToolBarHelper::custom('createchild', 'new', 'new', vmText::_('COM_VIRTUEMART_PRODUCT_CHILD'), !$product_parent);
				JToolBarHelper::custom('cloneproduct', 'copy', 'copy', vmText::_('COM_VIRTUEMART_PRODUCT_CLONE'), true);
			}
			JToolBarHelper::custom('addrating', 'default', '', vmText::_('COM_VIRTUEMART_ADD_RATING'), true);
			$this->addStandardDefaultViewCommands();


			$this->assignRef('productlist', $productlist);


			break;
		}

		parent::display($tpl);
	}

	/**
	 * This is wrong
	 *@deprecated
	 */
	function renderMail() {
		$this->setLayout('mail_html_waitlist');
		$this->subject = vmText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_SUBJECT', $this->productName);
		$notice_body = vmText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_BODY', $this->productName, $this->url);

		parent::display();
	}


	/**
	 * Renders the list for the discount rules
	 *
	 * @author Max Milbers
	 */
	function renderDiscountList($selected,$name='product_discount_id'){

		if(!class_exists('VirtueMartModelCalc')) require(VMPATH_ADMIN.DS.'models'.DS.'calc.php');
		$discounts = VirtueMartModelCalc::getDiscounts();

		$discountrates = array();
		$discountrates[] = JHtml::_('select.option', '-1', vmText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NONE'), 'product_discount_id' );
		$discountrates[] = JHtml::_('select.option', '0', vmText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NO_SPECIAL'), 'product_discount_id' );
		//		$discountrates[] = JHtml::_('select.option', 'override', vmText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE'), 'product_discount_id');
		foreach($discounts as $discount){
			$discountrates[] = JHtml::_('select.option', $discount->virtuemart_calc_id, $discount->calc_name, 'product_discount_id');
		}
		$listHTML = JHtml::_('Select.genericlist', $discountrates, $name, 'class="vm-chzn-add"', 'product_discount_id', 'text', $selected, '[' );
		return $listHTML;

	}

	static function displayLinkToChildList($product_id, $product_name) {

		static $c = array();
		if(!isset($c[$product_id])){
			$db = JFactory::getDBO();
			$db->setQuery(' SELECT COUNT( * ) FROM `#__virtuemart_products` WHERE `product_parent_id` ='.$product_id);
			if ($result = $db->loadResult()){
				$result = vmText::sprintf('COM_VIRTUEMART_X_CHILD_PRODUCT', $result);
				$c[$product_id] =  JHtml::_('link', JRoute::_('index.php?view=product&product_parent_id='.$product_id.'&option=com_virtuemart'), $result, array('title' => vmText::sprintf('COM_VIRTUEMART_PRODUCT_LIST_X_CHILDREN',htmlentities($product_name)) ));

			} else {
				$c[$product_id] = '';
			}
		}
		
		return $c[$product_id];
	}

	function displayLinkToParent($product_parent_id) {

		static $c = array(0=>'');
		if(!isset($c[$product_parent_id])){

			$parent = $this->model->getProductSingle($product_parent_id, false);

			if (!empty($parent->product_name)){
				$result = vmText::sprintf('COM_VIRTUEMART_LIST_CHILDREN_FROM_PARENT', htmlentities($parent->product_name));
				$c[$product_parent_id] = JHtml::_('link', JRoute::_('index.php?view=product&product_parent_id='.$product_parent_id.'&option=com_virtuemart'), $parent->product_name, array('title' => $result));

			} else {
				$c[$product_parent_id] = '';
				//vmdebug('my link displayLinkToParent '.$product_parent_id,$parent);
			}

		}

		return $c[$product_parent_id];
	}

	public function ajaxCategoryDropDown($id){

		$param = '';
		if(!empty($this->categoryId)){
			$param = '&virtuemart_category_id='.$this->categoryId;
		} else if(!empty($this->product->virtuemart_product_id)){
			$param = '&virtuemart_product_id='.$this->product->virtuemart_product_id;
		}
		$eOpt = vmText::sprintf( 'COM_VIRTUEMART_SELECT' ,  vmText::_('COM_VIRTUEMART_CATEGORY'));
		vmJsApi::ajaxCategoryDropDown($id, $param, $eOpt);
	}
}

//pure php no closing tag
