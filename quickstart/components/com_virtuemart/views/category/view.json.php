<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.json.php 9572 2017-06-07 15:03:30Z kkmediaproduction $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmView'))require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');
/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewCategory extends VmView {

	var $json = array();

	function __construct( ){

		$this->type = vRequest::getCmd('type', false);
		$this->row = vRequest::getInt('row', false);
	}
	
	function display($tpl = null) {

		$id = vRequest::getInt('id', false);
		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id',array());
		if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
			$product_id = (int)$virtuemart_product_id[0];
		} else {
			$product_id = (int)$virtuemart_product_id;
		}

		
    	if ($this->type=='getCategoriesTree') {
			if(!empty($product_id)){
				$this->ProductModel = VmModel::getModel('product');
				$product = $this->ProductModel->getProductSingle($virtuemart_product_id,false);
				$categories = $product->categories;
			} else {
				if(!$categories = vRequest::getInt('top_category_id')){
					$categories = vRequest::getInt('virtuemart_category_id',array());
					if(!is_array($categories)){
						$categories = (array) $categories;
					}
				}
			}
			$own_category_id = vRequest::getInt('own_category_id',false);

			//TODO Why do we not use the states of the model directly?
			//$productModel = VmModel::getModel('product');
			//$own_category_id = $productModel->filter_order;
			if(!class_exists('ShopFunctions'))require(VMPATH_ADMIN.DS.'helpers'.DS.'shopfunctions.php');
			if($own_category_id){
				$html = ShopFunctions::categoryListTree($categories, 0, 0, (array) $own_category_id);
			} else {
				$html = ShopFunctions::categoryListTree($categories);
			}

			$this->json['value'] = $html;
			
			
		} else $this->json['ok'] = 0 ;

		if ( empty($this->json)) {
			$this->json['value'] = null;
			$this->json['ok'] = 1 ;
		}

		header ('Content-Type: application/json');
		echo vmJsApi::safe_json_encode($this->json);
		jExit();
	}

}
// pure php no closing tag
