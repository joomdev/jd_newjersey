<?php
defined('_JEXEC') or  die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/*
* Best selling Products module for VirtueMart
* @version $Id: mod_virtuemart_category.php 1160 2014-05-06 20:35:19Z milbo $
* @package VirtueMart
* @subpackage modules
*
* @copyright (C) 2011-2015 The Virtuemart Team
*
*
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* @link https://virtuemart.net
*----------------------------------------------------------------------
* This code creates a list of the bestselling products
* and displays it wherever you want
*----------------------------------------------------------------------
*/

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

VmConfig::loadConfig();
vmLanguage::loadJLang('mod_virtuemart_category', true);
vmJsApi::jQuery();
vmJsApi::cssSite();

/* Setting */
$categoryModel = VmModel::getModel('Category');
$category_id = $params->get('Parent_Category_id', 0);
$class_sfx = $params->get('class_sfx', '');
$moduleclass_sfx = $params->get('moduleclass_sfx','');
$layout = $params->get('layout','default');
$active_category_id = vRequest::getInt('virtuemart_category_id', '0');
$vendorId = '1';

$categories = $categoryModel->getChildCategoryList($vendorId, $category_id);

// We dont use image here
//$categoryModel->addImages($categories);

if(empty($categories)) return false;

$level = $params->get('level','2');

if($level>1){
	foreach ($categories as $i => $category) {
		$categories[$i]->childs = $categoryModel->getChildCategoryList($vendorId, $category->virtuemart_category_id) ;
		// No image used here
		//$categoryModel->addImages($category->childs);
		//Yehyeh, very cheap done.
		if($level>2){
			foreach ($categories[$i]->childs as $j => $cat) {
				$categories[$i]->childs[$j]->childs = $categoryModel->getChildCategoryList( $vendorId, $cat->virtuemart_category_id );
			}
		}
	}
}
//vmdebug('my categories',$categories);


$parentCategories = $categoryModel->getCategoryRecurse($active_category_id,0);

/* Load tmpl default */
require(JModuleHelper::getLayoutPath('mod_virtuemart_category',$layout));
?>