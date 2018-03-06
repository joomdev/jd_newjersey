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

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');
		// Load some common models
if(!class_exists('VirtueMartModelCustomfields')) require(VMPATH_ADMIN.DS.'models'.DS.'customfields.php');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewProduct extends VmViewAdmin {

	var $json = array();

	function __construct( ){

		$this->type = vRequest::getCmd('type', false);
		$this->row = vRequest::getInt('row', false);
		$this->db = JFactory::getDBO();
		$this->model = VmModel::getModel('Customfields') ;
		

	}
	function display($tpl = null) {

		$filter = trim(vRequest::getVar('q', vRequest::getVar('term', '') ));

		$id = vRequest::getInt('id', false);
		$virtuemart_product_id = vRequest::getInt('virtuemart_product_id',array());
		if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
			$product_id = (int)$virtuemart_product_id[0];
		} else {
			$product_id = (int)$virtuemart_product_id;
		}
		$useFb = vmLanguage::getUseLangFallback();
		$useFb2 = vmLanguage::getUseLangFallbackSecondary();
		/* Get the task */
		if ($this->type=='relatedproducts') {

			$query = "SELECT p.virtuemart_product_id AS id, ";

			$langField = 'product_name';
			if($useFb){
				$f2 = 'ld.'.$langField;
				if($useFb2){
					$f2 = 'IFNULL(ld.'.$langField.', ljd.'.$langField.')';
				}
				$field = 'IFNULL(l.'.$langField.','.$f2.')';
			} else {
				$field = 'l.'.$langField;
			}

			$query .= ' CONCAT('.$field.', "::", product_sku) AS value';
			$query .= ' FROM `#__virtuemart_products` AS p ';

			$joinedTables = VmModel::joinLangTables('#__virtuemart_products','p','virtuemart_product_id');
			$query .= " \n".implode(" \n",$joinedTables);
			if (!empty($filter)){
				$filter = '"%'.$this->db->escape( $filter, true ).'%"';
				$fields = VmModel::joinLangLikeFields(array('product_name'),$filter);
				$query .=  ' WHERE '.implode (' OR ', $fields) ;
				$query .= ' OR product_sku LIKE '.$filter;
			}

			self::setRelatedHtml($product_id,$query,'R');
		}
		else if ($this->type=='relatedcategories') {


			$query = "SELECT c.virtuemart_category_id AS id, ";

			$langField = 'category_name';
			if($useFb){
				$f2 = 'ld.'.$langField;
				if($useFb2){
					$f2 = 'IFNULL(ld.'.$langField.', ljd.'.$langField.')';
				}
				$field = 'IFNULL(l.'.$langField.','.$f2.')';
			} else {
				$field = 'l.'.$langField;
			}

			$query .= ' CONCAT('.$field.', "::", c.virtuemart_category_id) AS value';
			$query .= ' FROM `#__virtuemart_categories` AS c ';

			$joinedTables = VmModel::joinLangTables('#__virtuemart_categories','c','virtuemart_category_id');
			$query .= " \n".implode(" \n",$joinedTables);
			if (!empty($filter)){
				$filter = '"%'.$this->db->escape( $filter, true ).'%"';
				$fields = VmModel::joinLangLikeFields(array('category_name'),$filter);
				$query .=  ' WHERE '.implode (' OR ', $fields) ;
			}

			self::setRelatedHtml($product_id,$query,'Z');
		}
		else if ($this->type=='custom')
		{
			$query = "SELECT CONCAT(virtuemart_custom_id, '|', custom_value, '|', field_type) AS id, CONCAT(custom_title, '::', custom_tip) AS value
				FROM #__virtuemart_customs";
			if (!empty($filter)) $query .= " WHERE custom_title LIKE '%".$filter."%' ";
			$query .= " limit 0,50";
			$this->db->setQuery($query);
			$this->json['value'] = $this->db->loadObjectList();
			$this->json['ok'] = 1 ;
		}
		else if ($this->type=='fields')
		{
			if (!class_exists ('VirtueMartModelCustom')) {
				require(VMPATH_ADMIN . DS . 'models' . DS . 'custom.php');
			}
			$fieldTypes = VirtueMartModelCustom::getCustomTypes();
			$model = VmModel::getModel('custom');
			$q = 'SELECT `virtuemart_custom_id` FROM `#__virtuemart_customs`
			WHERE (`custom_parent_id`='.$id.') ';
			$q .= 'order by `ordering` asc';
			$this->db->setQuery($q);
			$ids = $this->db->loadColumn();
			if($ids){
				array_unshift($ids,$id);
			} else {
				$ids = array($id);
			}

			foreach($ids as $k => $i){
				$p = $model->getCustom($i);
				if($p){
					$p->value = $p->custom_value;
					$rows[] = $p;
				}
			}

			$html = array ();
			foreach ($rows as $field) {
				if ($field->field_type =='deprecatedwasC' ){
					$this->json['table'] = 'childs';
					$q='SELECT `virtuemart_product_id` FROM `#__virtuemart_products` WHERE `published`=1
					AND `product_parent_id`= '.vRequest::getInt('virtuemart_product_id');
					//$this->db->setQuery(' SELECT virtuemart_product_id, product_name FROM `#__virtuemart_products` WHERE `product_parent_id` ='.(int)$product_id);
					$this->db->setQuery($q);
					if ($childIds = $this->db->loadColumn()) {
					// Get childs
						foreach ($childIds as $childId) {
							$field->custom_value = $childId;
							$display = $this->model->displayProductCustomfieldBE($field,$childId,$this->row);
							 if ($field->is_cart_attribute) $cartIcone=  'default';
							 else  $cartIcone= 'default-off';
							 $html[] = '<div class="removable">
								<td>'.$field->custom_title.'</td>
								 <td>'.$display.$field->custom_tip.'</td>
								 <td>'.vmText::_($fieldTypes[$field->field_type]).'
								'.$this->model->setEditCustomHidden($field, $this->row).'
								 </td>
								 <td><span class="vmicon vmicon-16-'.$cartIcone.'"></span></td>
								 <td></td>
								</div>';
							$this->row++;
						}
					}
				} else { //if ($field->field_type =='E') {
					$this->json['table'] = 'customPlugins';
					$colspan ='';
					if ($field->field_type =='E') {
						$this->model->bindCustomEmbeddedFieldParams($field,'E');
					} else if($field->field_type == 'C'){
						$colspan = 'colspan="2" ';
					}

					$display = $this->model->displayProductCustomfieldBE($field,$product_id,$this->row);
					 if ($field->is_cart_attribute) {
					     $cartIcone=  'default';
					 } else {
					     $cartIcone= 'default-off';
					 }
					$field->virtuemart_product_id=$product_id;
					$html[] = '
					<tr class="removable">
						<td>
							<b>'.vmText::_($fieldTypes[$field->field_type]).'</b> '.vmText::_($field->custom_title).'</span><br/>

								<span class="vmicon vmicon-16-'.$cartIcone.'"></span>
								<span class="vmicon vmicon-16-move"></span>
								<span class="vmicon vmicon-16-remove 4remove"></span>

						'.$this->model->setEditCustomHidden($field, $this->row).'
					 	</td>
							<td '.$colspan.'>'.$display.'</td>
						 </tr>
					</tr>';
					$this->row++;

				}
			}

			$this->json['value'] = $html;
			$this->json['ok'] = 1 ;
		} else if ($this->type=='userlist')
		{
			$status = vRequest::getvar('order_status',array('S'));
			$productShoppers=0;

			if ($status) {
				$option = vRequest::getCmd('option');
				$lists['filter_order'] = JFactory::getApplication()->getUserStateFromRequest($option.'filter_order_orders', 'filter_order', 'email', 'cmd');
				$lists['filter_order_Dir'] = JFactory::getApplication()->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');

				$productModel = VmModel::getModel('product');
				$productShoppers = $productModel->getProductShoppersByStatus($product_id ,$status,$lists['filter_order'],$lists['filter_order_Dir']);
			}

			if(!class_exists('ShopFunctions'))require(VMPATH_ADMIN.DS.'helpers'.DS.'shopfunctions.php');
			$html = ShopFunctions::renderProductShopperList($productShoppers);
			$this->json['value'] = $html;

		} else if ($this->type=='getCategoriesTree') {
			if(!empty($product_id)){
				$this->ProductModel = VmModel::getModel();
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

	
	
	function setRelatedHtml($product_id,$query,$fieldType) {


		$this->db->setQuery($query.' limit 0,50');
		$this->json = $this->db->loadObjectList();
		if(!($this->json)){
			echo('setRelatedHtml '.$query);
			return;
		}
		$query = 'SELECT * FROM `#__virtuemart_customs` WHERE field_type ="'.$fieldType.'" ';
		$this->db->setQuery($query);
		$custom = $this->db->loadObject();
		if(!$custom) {
			vmdebug('setRelatedHtml could not find $custom for field type '.$fieldType);
			return false;
		}
		$custom->virtuemart_product_id = $product_id;
		/*$m = count($this->json);
		vmdebug('setRelatedHtml '.str_replace('#__',$this->db->getPrefix(),$this->db->getQuery()),$m);*/
		foreach ($this->json as $k=>$related) {

			$custom->customfield_value = $related->id;

			$display = $this->model->displayProductCustomfieldBE($custom,$related->id,$this->row);

			$html = '<div class="vm_thumb_image">
				<span class="vmicon vmicon-16-move"></span>
				<div class="vmicon vmicon-16-remove 4remove"></div>
				<span>'.$display.'</span>
				'.$this->model->setEditCustomHidden($custom, $this->row).'
				</div>';

			$this->json[$k]->label = $html;

		}
	}

}
// pure php no closing tag
