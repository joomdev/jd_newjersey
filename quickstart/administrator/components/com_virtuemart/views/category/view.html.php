<?php

/**
 *
 * Category View
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9802 2018-03-20 15:22:11Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for maintaining the list of categories
 *
 * @package	VirtueMart
 * @subpackage Category
 * @author RickG, jseros
 */
class VirtuemartViewCategory extends VmViewAdmin {

	function display($tpl = null) {

		if(!class_exists('VirtueMartModelConfig'))require(VMPATH_ADMIN .'models/config.php');

		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		$model = VmModel::getModel();
		$layoutName = $this->getLayout();

		$task = vRequest::getCmd('task',$layoutName);
		$this->assignRef('task', $task);

		$this->user = $user = JFactory::getUser();
		if ($layoutName == 'edit') {

			vmLanguage::loadJLang('com_virtuemart_config');

			$category = $model->getCategory('', false, false);
			if(!empty($category->_loadedWithLangFallback)){
				vmInfo('COM_VM_LOADED_WITH_LANGFALLBACK',$category->_loadedWithLangFallback);
			}
			$this->setOrigLang($category);
			if(!class_exists('VirtuemartViewConfig')) require (VMPATH_ADMIN .'/views/config/view.html.php');
			// Toolbar
			$text='';
			if (isset($category->category_name)) $name = $category->category_name; else $name ='';
			if(!empty($category->virtuemart_category_id)){
				$text = '<a href="'.juri::root().'index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id.'" target="_blank" >'. $name.'<span class="vm2-modallink"></span></a>';
			}

			$this->SetViewTitle('CATEGORY',$text);

			$model->addImages($category);

			/*if ( $category->virtuemart_category_id > 1 ) {
				$relationInfo = $model->getRelationInfo( $category->virtuemart_category_id );
				$this->assignRef('relationInfo', $relationInfo);
			} else {
				$category->virtuemart_vendor_id = vmAccess::getVendorId();
			}*/

			$parent = $model->getParentCategory( $category->virtuemart_category_id );
			$this->assignRef('parent', $parent);

			if(!class_exists('ShopFunctions'))require(VMPATH_ADMIN.DS.'helpers'.DS.'shopfunctions.php');

			$this->jTemplateList = ShopFunctions::renderTemplateList(vmText::_('COM_VIRTUEMART_ADMIN_CFG_JOOMLA_TEMPLATE_DEFAULT'));

			$cmodel = VmModel::getModel('config');

			$this->categoryLayoutList = $cmodel->getLayoutList('category');

			$this->productLayoutList = $cmodel->getLayoutList('productdetails');

			$this->productsFieldList  = $cmodel->getFieldList('products');

			//Nice fix by Joe, the 4. param prevents setting an category itself as child
			$categorylist = '';//ShopFunctions::categoryListTree(array($parent->virtuemart_category_id), 0, 0, (array) $category->virtuemart_category_id);

			$param = '';
			if(!empty($parent->virtuemart_category_id) and !empty($category->virtuemart_category_id)){
				$param = '&virtuemart_category_id='.$parent->virtuemart_category_id.'&own_category_id='.$category->virtuemart_category_id;
			}
			vmJsApi::ajaxCategoryDropDown('category_parent_id', $param, vmText::_('COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL'));

			if($this->showVendors()){
				$vendorList= ShopFunctions::renderVendorList($category->virtuemart_vendor_id);
				$this->assignRef('vendorList', $vendorList);
			}

			$this->assignRef('category', $category);
			$this->assignRef('categorylist', $categorylist);

			$this->addStandardEditViewCommands($category->virtuemart_category_id,$category);
		}
		else {
			$this->SetViewTitle('CATEGORY_S');

			$keyWord ='';

			$this->assignRef('catmodel',	$model);
			$this->addStandardDefaultViewCommands();
			$this->addStandardDefaultViewLists($model,'category_name');

			$app = JFactory::getApplication ();

			//$topCategory=vRequest::getInt('top_category_id',0);
			$topCategory = $app->getUserStateFromRequest ( 'com_virtuemart.category.top_category_id', 'top_category_id', '', 'int');
			$app->setUserState( 'com_virtuemart.category.top_category_id',$topCategory);
			$param = '';
			if(!empty($topCategory)){
				$param = '&top_category_id='.$topCategory;
			}
			vmJsApi::ajaxCategoryDropDown('top_category_id', $param, vmText::_('COM_VIRTUEMART_CATEGORY_FORM_TOP_LEVEL'));

			$this->categories = $model->getCategoryTree($topCategory,0,false,$this->lists['search']);
			foreach($this->categories as $i=>$c){
				$this->categories[$i]->productcount = $model->countProducts($this->categories[$i]->virtuemart_category_id);
			}

			$this->catpagination = $model->getPagination();

			//we need a function of the FE shopfunctions helper to cut the category descriptions
			if (!class_exists ('shopFunctionsF')) require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}

		parent::display($tpl);
	}

}

// pure php no closing tag
