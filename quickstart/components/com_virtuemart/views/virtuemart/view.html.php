<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9560 2017-05-30 14:13:21Z Milbo $
 */

# Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

# Load the view framework
if(!class_exists('VmView'))require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');

/**
 * Default HTML View class for the VirtueMart Component
 * @todo Find out how to use the front-end models instead of the backend models
 */
class VirtueMartViewVirtueMart extends VmView {

	public function display($tpl = null) {

		//For BC, we convert first the new config param names to the old ones
		VmConfig::set('show_featured', VmConfig::get('featured'));
		VmConfig::set('show_discontinued', VmConfig::get('discontinued'));
		VmConfig::set('show_topTen', VmConfig::get('topten'));
		VmConfig::set('show_recent', VmConfig::get('recent'));
		VmConfig::set('show_latest', VmConfig::get('latest'));

		VmConfig::set('featured_products_rows', VmConfig::get('featured_rows'));
		VmConfig::set('discontinued_products_rows', VmConfig::get('discontinued_rows'));
		VmConfig::set('topTen_products_rows', VmConfig::get('topten_rows'));
		VmConfig::set('recent_products_rows', VmConfig::get('recent_rows'));
		VmConfig::set('latest_products_rows', VmConfig::get('latest_rows'));
		VmConfig::set('omitLoaded_topTen', VmConfig::get('omitLoaded_topten'));
		VmConfig::set('showCategory', VmConfig::get('showcategory'));

		$vendorId = vRequest::getInt('vendorid', 1);

		$vendorModel = VmModel::getModel('vendor');

		$vendorModel->setId($vendorId);
		$this->vendor = $vendorModel->getVendor();

		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();

		if(!class_exists('shopFunctionsF'))require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		if(!empty($menu->id)){
			ShopFunctionsF::setLastVisitedItemId($menu->id);
		} else if($itemId = vRequest::getInt('Itemid',false)){
			ShopFunctionsF::setLastVisitedItemId($itemId);
		}

		$document = JFactory::getDocument();

		if(!VmConfig::get('shop_is_offline',0)){


			if (VmConfig::get ('enable_content_plugin', 0)) {
				shopFunctionsF::triggerContentPlugin($this->vendor, 'vendor','vendor_store_desc');
				shopFunctionsF::triggerContentPlugin($this->vendor, 'vendor','vendor_terms_of_service');
			}

			if( ShopFunctionsF::isFEmanager('product.edit') ){
				$add_product_link = JURI::root() . 'index.php?option=com_virtuemart&tmpl=component&view=product&task=edit&virtuemart_product_id=0&manage=1' ;
				$add_product_link = $this->linkIcon($add_product_link, 'COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT', 'edit', false, false);
			} else {
				$add_product_link = "";
			}
			$this->assignRef('add_product_link', $add_product_link);

			$categoryModel = VmModel::getModel('category');
			$productModel = VmModel::getModel('product');
			$ratingModel = VmModel::getModel('ratings');
			$productModel->withRating = $this->showRating = $ratingModel->showRating();

			$this->products = array();
			$categoryId = vRequest::getInt('catid', 0);

			$categoryChildren = $categoryModel->getChildCategoryList($vendorId, $categoryId);

			$categoryModel->addImages($categoryChildren,1);

			$this->assignRef('categories',	$categoryChildren);

			if(!class_exists('CurrencyDisplay'))require(VMPATH_ADMIN.DS.'helpers'.DS.'currencydisplay.php');
			$this->currency = CurrencyDisplay::getInstance( );
			
			$products_per_row = VmConfig::get('homepage_products_per_row',3);
			
			$featured_products_rows = VmConfig::get('featured_rows',1);
			$featured_products_count = $products_per_row * $featured_products_rows;

			if (!empty($featured_products_count) and VmConfig::get('featured', 1)) {
				$this->products['featured'] = $productModel->getProductListing('featured', $featured_products_count);
				$productModel->addImages($this->products['featured'],1);
			}
			
			$latest_products_rows = VmConfig::get('latest_rows');
			$latest_products_count = $products_per_row * $latest_products_rows;

			if (!empty($latest_products_count) and VmConfig::get('latest', 1)) {
				$this->products['latest']= $productModel->getProductListing('latest', $latest_products_count);
				$productModel->addImages($this->products['latest'],1);
			}

			$topTen_products_rows = VmConfig::get('topten_rows');
			$topTen_products_count = $products_per_row * $topTen_products_rows;
			
			if (!empty($topTen_products_count) and VmConfig::get('topten', 1)) {
				$this->products['topten']= $productModel->getProductListing('topten', $topTen_products_count);
				$productModel->addImages($this->products['topten'],1);
			}
			
			$recent_products_rows = VmConfig::get('recent_rows');
			$recent_products_count = $products_per_row * $recent_products_rows;

			
			if (!empty($recent_products_count) and VmConfig::get('recent', 1) ) {
				$recent_products = $productModel->getProductListing('recent');
				if(!empty($recent_products)){
					$this->products['recent']= $productModel->getProductListing('recent', $recent_products_count);
					$productModel->addImages($this->products['recent'],1);
				}
			}

			if ($this->products) {

				$display_stock = VmConfig::get('display_stock',1);
				$showCustoms = VmConfig::get('show_pcustoms',1);
				if($display_stock or $showCustoms){

					if(!$showCustoms){
						foreach($this->products as $pType => $productSeries){
							foreach($productSeries as $i => $productItem){
								$this->products[$pType][$i]->stock = $productModel->getStockIndicator($productItem);
							}
						}
					} else {
						if (!class_exists ('vmCustomPlugin')) {
							require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
						}
						foreach($this->products as $pType => $productSeries) {
							shopFunctionsF::sortLoadProductCustomsStockInd($this->products[$pType],$productModel);
						}
					}
				}
			}

			$this->showBasePrice = (vmAccess::manager() or vmAccess::isSuperVendor());

			$layout = VmConfig::get('vmlayout','default');
			$this->setLayout($layout);

			$productsLayout = VmConfig::get('productsublayout','products');
			if(empty($productsLayout)) $productsLayout = 'products';
			$this->productsLayout = empty($menu->query['productsublayout'])? $productsLayout:$menu->query['productsublayout'];

			// Add feed links
			if ($this->products  && (VmConfig::get('feed_featured_published', 0)==1 or VmConfig::get('feed_topten_published', 0)==1 or VmConfig::get('feed_latest_published', 0)==1)) {
				$link = '&format=feed&limitstart=';
				$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
				$document->addHeadLink(JRoute::_($link . '&type=rss', FALSE), 'alternate', 'rel', $attribs);
				$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
				$document->addHeadLink(JRoute::_($link . '&type=atom', FALSE), 'alternate', 'rel', $attribs);
			}
			vmJsApi::jPrice();
		} else {
			$this->setLayout('off_line');
		}

		$error = vRequest::getInt('error',0);

		//Todo this may not work everytime as expected, because the error must be set in the redirect links.
		if(!empty($error)){
			$document->setTitle(vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND').vmText::sprintf('COM_VIRTUEMART_HOME',$this->vendor->vendor_store_name));
		} else {

			if(empty($this->vendor->customtitle)){

				if ($menu){
					$menuTitle = $menu->params->get('page_title');
					if(empty($menuTitle)) {
						$menuTitle = vmText::sprintf('COM_VIRTUEMART_HOME',$this->vendor->vendor_store_name);
					}
					$document->setTitle($menuTitle);
				} else {
					$title = vmText::sprintf('COM_VIRTUEMART_HOME',$this->vendor->vendor_store_name);
					$document->setTitle($title);
				}
			} else {
				$document->setTitle($this->vendor->customtitle);
			}


			if(!empty($this->vendor->metadesc)) $document->setMetaData('description',$this->vendor->metadesc);
			if(!empty($this->vendor->metakey)) $document->setMetaData('keywords',$this->vendor->metakey);
			if(!empty($this->vendor->metarobot)) $document->setMetaData('robots',$this->vendor->metarobot);
			if(!empty($this->vendor->metaauthor)) $document->setMetaData('author',$this->vendor->metaauthor);

		}

		if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
		vmTemplate::setTemplate();

		parent::display($tpl);

	}
}
# pure php no closing tag