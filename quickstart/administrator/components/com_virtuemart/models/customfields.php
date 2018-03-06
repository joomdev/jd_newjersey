<?php
/**
 *
 * Description
 *
 * @package    VirtueMart
 * @subpackage
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved by the author.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id:$
 */

// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');

if (!class_exists ('VmModel')) {
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmmodel.php');
}

/**
 * Model for VirtueMart Customs Fields
 *
 * @package        VirtueMart
 */
class VirtueMartModelCustomfields extends VmModel {

	/** @var array For roundable values */
	static $dimensions = array('product_length','product_width','product_height','product_weight');
	static $useAbsUrls = false;
	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 *
	 * @author Max Milbers
	 */
	function __construct () {

		parent::__construct ('virtuemart_customfield_id');
		$this->setMainTable ('product_customfields');
	}

	/**
	 * Gets a single custom by virtuemart_customfield_id
	 *
	 * @param string $type
	 * @param string $mime mime type of custom, use for exampel image
	 * @return customobject
	 */
	function getCustomfield ($id = 0) {

		return $this->getData($id);

	}

	public static function getProductCustomSelectFieldList(){

		$q = 'SELECT c.`virtuemart_custom_id`, c.`custom_parent_id`, c.`virtuemart_vendor_id`, c.`custom_jplugin_id`, c.`custom_element`, c.`admin_only`, c.`custom_title`, c.`show_title` , c.`custom_tip`,
		c.`custom_value`, c.`custom_desc`, c.`field_type`, c.`is_list`, c.`is_hidden`, c.`is_cart_attribute`, c.`is_input`, c.`layout_pos`, c.`custom_params`, c.`shared`, c.`published`, c.`ordering`, ';
		$q .= 'field.`virtuemart_customfield_id`, field.`virtuemart_product_id`, field.`customfield_value`, field.`customfield_price`,
		field.`customfield_params`, field.`published` as fpublished, field.`override`, field.`disabler`, field.`ordering`
		FROM `#__virtuemart_customs` AS c LEFT JOIN `#__virtuemart_product_customfields` AS field ON c.`virtuemart_custom_id` = field.`virtuemart_custom_id` ';
		return $q;
	}


	public static function getCustomEmbeddedProductCustomField($virtuemart_customfield_id){

		static $_customFieldById = array();

		if(!isset($_customFieldById[$virtuemart_customfield_id])){
			$db= JFactory::getDBO ();
			$q = VirtueMartModelCustomfields::getProductCustomSelectFieldList();
			if($virtuemart_customfield_id){
				$q .= ' WHERE `virtuemart_customfield_id` ="' . (int)$virtuemart_customfield_id . '"';
			}
			$db->setQuery ($q);
			$_customFieldById[$virtuemart_customfield_id] = $db->loadObject ();
			if($_customFieldById[$virtuemart_customfield_id]){
				VirtueMartModelCustomfields::bindCustomEmbeddedFieldParams($_customFieldById[$virtuemart_customfield_id],$_customFieldById[$virtuemart_customfield_id]->field_type);
			}
		}

		return $_customFieldById[$virtuemart_customfield_id];
	}

	function getCustomEmbeddedProductCustomFields($productIds,$virtuemart_custom_id=0,$cartattribute=-1,$forcefront=FALSE){

		$app = JFactory::getApplication();
		$db= JFactory::getDBO ();
		$q = VirtueMartModelCustomfields::getProductCustomSelectFieldList();

		static $_customFieldByProductId = array();

		$hashCwAttribute = $cartattribute;
		if($hashCwAttribute==-1) $hashCwAttribute = 2;
		$productCustomsCached = array();
		foreach($productIds as $k=>$productId){
			$hkey = (int)$productId.'_'.$hashCwAttribute;
			if (array_key_exists ($hkey, $_customFieldByProductId)) {

				//Must be cloned!
				foreach($_customFieldByProductId[$hkey] as $ccust){
					$clonedCache[] = clone($ccust);
				}
				$productCustomsCached = array_merge($productCustomsCached,$clonedCache);
				unset($productIds[$k]);
			}
		}

		if(is_array($productIds) and count($productIds)>0){
			$q .= 'WHERE field.`virtuemart_product_id` IN ('.implode(',', $productIds).')';
		} else if(!empty($productIds)){
			$q .= 'WHERE field.`virtuemart_product_id` = "'.$productIds.'" ';
		} else {
			return $productCustomsCached;
		}
		if(!empty($virtuemart_custom_id)){
			if(is_numeric($virtuemart_custom_id)){
				$q .= ' AND c.`virtuemart_custom_id`= "' . (int)$virtuemart_custom_id.'" ';
			} else {
				$virtuemart_custom_id = substr($virtuemart_custom_id,0,1); //just in case
				$q .= ' AND c.`field_type`= "' .$virtuemart_custom_id.'" ';
			}
		}
		if(!empty($cartattribute) and $cartattribute!=-1){
			$q .= ' AND ( `is_cart_attribute` = 1 OR `is_input` = 1) ';
		}
		if($forcefront or $app->isSite()){
			$q .= ' AND c.`published` = "1" ';
			$forcefront = true;
		}

		if(!empty($virtuemart_custom_id) and $virtuemart_custom_id!==0){
			$q .= ' ORDER BY field.`ordering` ASC';
		} else {
			if($forcefront or $app->isSite()){
				//$q .= ' GROUP BY c.`virtuemart_custom_id`';
			}

			$q .= ' ORDER BY field.`ordering` ASC';
		}

		$db->setQuery ($q);
		$productCustoms = $db->loadObjectList ();
		$err=$db->getErrorMsg();
		if($err){
			vmError('getCustomEmbeddedProductCustomFields error in query '.$err);
		}

		foreach($productCustoms as $customfield){
			$hkey = (int)$customfield->virtuemart_product_id.$hashCwAttribute;
			$_customFieldByProductId[$hkey][] = $customfield;
		}

		$productCustoms = array_merge($productCustomsCached,$productCustoms);
		if($productCustoms){

			$customfield_ids = array();
			$customfield_override_ids = array();
			foreach($productCustoms as $field){

				if($field->override!=0){
					$customfield_override_ids[] = $field->override;
				} else if ($field->disabler!=0) {
					$customfield_override_ids[] = $field->disabler;
				}

				$customfield_ids[] = $field->virtuemart_customfield_id;
			}
			$virtuemart_customfield_ids = array_unique( array_diff($customfield_ids,$customfield_override_ids));

			foreach ($productCustoms as $k =>$field) {
				if(in_array($field->virtuemart_customfield_id,$virtuemart_customfield_ids)){

					if($forcefront and $field->disabler){
						unset($productCustoms[$k]);
					} else {
						VirtueMartModelCustomfields::bindCustomEmbeddedFieldParams($productCustoms[$k],$field->field_type);
					}

				} else{
					unset($productCustoms[$k]);
				}
			}
			return $productCustoms;
		} else {
			return array();
		}
	}


	static function bindCustomEmbeddedFieldParams(&$obj,$fieldtype){

		if(!class_exists('VirtueMartModelCustom')) require(VMPATH_ADMIN.DS.'models'.DS.'custom.php');

		if ($obj->field_type == 'E') {
			if(!empty($obj->virtuemart_custom_id)){
				static $varsToPushPlg = array();
				if(isset($varsToPushPlg[$obj->virtuemart_custom_id])){
					$obj->_varsToPushParam = $varsToPushPlg[$obj->virtuemart_custom_id];
				} else {
					JPluginHelper::importPlugin ('vmcustom');
					$dispatcher = JDispatcher::getInstance ();
					$retValue = $dispatcher->trigger ('plgVmDeclarePluginParamsCustomVM3', array(&$obj));
					$varsToPushPlg[$obj->virtuemart_custom_id] = false;
					if(!empty($obj->_varsToPushParam)){
						$varsToPushPlg[$obj->virtuemart_custom_id] = $obj->_varsToPushParam;
					}
				}
			}
		} else {
			$obj->_varsToPushParam = VirtueMartModelCustom::getVarsToPush($fieldtype);
		}

		if(!empty($obj->_varsToPushParam)){
			VmTable::bindParameterable($obj,'custom_params',$obj->_varsToPushParam);

			$obj ->_xParams = 'customfield_params';
			VmTable::bindParameterable($obj,$obj->_xParams,$obj->_varsToPushParam);
		}

	}


	private function sortChildIds ($product_id, $childIds, $options, $sorted=array()){

		static $asorted = array();
		//vmdebug('sortChildIds',$product_id, $childIds);
		if(!empty($options)){
			foreach($options as $id => $v){
				if(empty($id)) continue;
				if($product_id!=$id){
					$sorted[] = array('parent_id'=>$product_id,'vm_product_id'=>$id);
					$asorted[$id] = 1;
				}

			}

		}

		foreach($childIds as $childIdKey => $childs){
			if(!is_array($childs)){

				if(empty($asorted[$childs])){
					$sorted[] = array('parent_id'=>$product_id,'vm_product_id'=>$childs);
				}

				if(isset($childIds[$childs]) and is_array($childIds[$childs])){
					$sorted = self::sortChildIds($childs, $childIds[$childs], $options, $sorted);
					//unset($childIds[$childs]);
				}
			} else {
				//$sorted = self::sortChildIds($childIdKey, $childs, $sorted);
			}
		}
		return $sorted;
	}


	private function renderProductChildLine($i,$line,$field,$productModel,$row,$showSku){

		$child = $productModel->getProductSingle($line['vm_product_id'],false);
		$readonly = '';
		$classBox = 'class="inputbox"';
		if($line['parent_id'] == $line['vm_product_id']){
			$readonly = 'readonly="readonly"';
			$classBox = 'class="readonly"';
		}
		$linkLabel = $line['parent_id'] .'->'. $line['vm_product_id'].' ';
		$html = '<tr class="row'.(($i+1)%2).' removable">';
		$html .= '<td>'.JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$child->virtuemart_product_id), $linkLabel.$child->slug, array('title' => vmText::_('COM_VIRTUEMART_EDIT').' '.$child->slug)).'<span class="vmicon vmicon-16-move"></span></td>';
		if($showSku) $html .= '<td><input '.$readonly.' '.$classBox.' type="text" name="childs['.$child->virtuemart_product_id.'][product_sku]" id="child'.$child->virtuemart_product_id.'product_sku" size="20" maxlength="64" value="'.$child->product_sku .'" /></td>';
		$html .= '<td><input '.$readonly.' '.$classBox.' type="text" name="childs['.$child->virtuemart_product_id.'][product_gtin]" id="child'.$child->virtuemart_product_id.'product_gtin" size="13" maxlength="13" value="'.$child->product_gtin .'" /></td>';
		/*$html .= 	'<input type="hidden" name="childs['.$child->virtuemart_product_id .'][product_name]" id="child'.$child->virtuemart_product_id .'product_name" value="'.$child->product_name .'" />
					<input type="hidden" name="childs['.$child->virtuemart_product_id .'][slug]" id="child'.$child->virtuemart_product_id .'slug" value="'.$child->slug .'" />
					<input type="hidden" name="childs['.$child->virtuemart_product_id .'][product_parent_id]" id="child'.$child->virtuemart_product_id .'parent" value="'.$child->product_parent_id .'" />';
		*/
		//$html .= 	$child->product_name .'</td>';
		//$html .=	'<td>'.$child->allPrices[$child->selectedPrice]['product_price'] .'</td>';
		$html .= '<td><input '.$readonly.' '.$classBox.' type="text" name="childs['.$child->virtuemart_product_id.'][mprices][product_price][]" size="8" value="'. $child->allPrices[$child->selectedPrice]['product_price'] .'" />
		<input type="hidden" name="childs['. $child->virtuemart_product_id .'][mprices][virtuemart_product_price_id][]" value="'. $child->allPrices[$child->selectedPrice]['virtuemart_product_price_id'] .'"  ></td>';

		//We dont want to update always the stock, this would lead to wrong stocks, if the store has activity, while the vendor is editing the product
		//$html .= '<td><input '.$readonly.' '.$class.' type="text" name="childs['.$child->virtuemart_product_id.'][product_in_stock]" id="child'.$child->virtuemart_product_id.'product_in_stock" size="3" maxlength="6" value="'.$child->product_in_stock .'" /></td>';
		//$html .= '<td><input '.$readonly.' '.$class.' type="text" name="childs['.$child->virtuemart_product_id.'][product_in_stock]" id="child'.$child->virtuemart_product_id.'product_in_stock" size="3" maxlength="6" value="'.$child->product_in_stock .'" /></td>';
		$html .= '<td>'.$child->product_in_stock .'</td>';
		$html .= '<td>'.$child->product_ordered.'</td>';

		$product_id = $line['vm_product_id'];
		if(empty($field->selectoptions)) $field->selectoptions = array();
		foreach($field->selectoptions as $k=>$selectoption){
			//vmdebug('my $field->options',$field->options);
			//if(!isset($field->options)) continue;

			$class ='';
			if($selectoption->voption=='clabels'){
				$name = 'field[' . $row . '][options]['.$product_id.']['.$k.']';
				$myoption = false;
				if(isset($field->options->$product_id)){
					$myoption = $field->options->$product_id;
				}

				if(!isset($myoption[$k])){
					$value = '';
				} else {
					$value = trim($myoption[$k]);
				}
				$idTag = 'cvarl.'.$product_id.'s'.$k;
			} else {
				$name = 'childs['.$product_id .']['.$selectoption->voption.']';
				$value = trim($child->{$selectoption->voption});
				$idTag = 'cvard.'.$product_id.'s'.$k;
				$class = array('class'=>'cvard');
			}

			if(count($selectoption->comboptions)>0){
				$html .= '<td>'.JHtml::_ ('select.genericlist', $selectoption->comboptions,$name , $class, 'value', 'text',
				$value ,$idTag);
				if($selectoption->voption!='clabels'){
					$html .= '<input type="hidden" name="field[' . $row . '][options]['.$product_id.']['.$k.']" value="'.$value .'" />';
				}
				$html .= '</td>';
			}
		}
		$html .= '</tr>';
		return $html;
	}

	/**
	 * @author Max Milbers
	 * @param $field
	 * @param $product_id
	 * @param $row
	 */
	public function displayProductCustomfieldBE ($field, $product, $row) {

		//This is a kind of fallback, setting default of custom if there is no value of the productcustom
		$field->customfield_value = empty($field->customfield_value) ? $field->custom_value : $field->customfield_value;
		$field->customfield_price = empty($field->customfield_price) ? 0 : $field->customfield_price;

		if(is_object($product)){
			$product_id = $product->virtuemart_product_id;
			$virtuemart_vendor_id = $product->virtuemart_vendor_id;
		} else {

			$product_id = $product;
			$virtuemart_vendor_id = vmAccess::isSuperVendor();
			vmdebug('displayProductCustomfieldBE product was not object, use for productId '.$product_id.' and $virtuemart_vendor_id = '.$virtuemart_vendor_id);
		}
		//vmdebug('displayProductCustomfieldBE',$product_id,$field,$virtuemart_vendor_id,$product);
		//the option "is_cart_attribute" gives the possibility to set a price, there is no sense to set a price,
		//if the custom is not stored in the order.
		if ($field->is_input) {
			$vendor_model = VmModel::getModel('vendor');
			$vendor = $vendor_model->getVendor($virtuemart_vendor_id);
			$currency_model = VmModel::getModel('currency');
			$vendor_currency = $currency_model->getCurrency($vendor->vendor_currency);

			$priceInput = '<span style="white-space: nowrap;"><input type="text" size="12" style="text-align:right;" value="' . $field->customfield_price . '" name="field[' . $row . '][customfield_price]" /> '.$vendor_currency->currency_symbol."</span>";
		}
		else {
			$priceInput = ' ';
		}

		switch ($field->field_type) {

			case 'C':
				//vmdebug('displayProductCustomfieldBE $field',$field);
				//if(!isset($field->withParent)) $field->withParent = 0;
				//if(!isset($field->parentOrderable)) $field->parentOrderable = 0;
				//vmdebug('displayProductCustomfieldBE',$field,$product);

				if(!empty($product->product_parent_id) and $product->product_parent_id==$field->virtuemart_product_id){
					return 'controlled by parent';
				}

				$html = '';
				if (!class_exists('VmHTML')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
				//$html = vmText::_('COM_VIRTUEMART_CUSTOM_WP').VmHTML::checkbox('field[' . $row . '][withParent]',$field->withParent,1,0,'');
				//$html .= vmText::_('COM_VIRTUEMART_CUSTOM_PO').VmHTML::checkbox('field[' . $row . '][parentOrderable]',$field->parentOrderable,1,0,'').'<br />';

				if(empty($field->selectoptions) or count($field->selectoptions)==0){
					$selectOption = new stdClass();	//The json conversts it anyway in an object, so suitable to use an object here
					$selectOption->voption = 'product_name';
					$selectOption->slabel = '';
					$selectOption->clabel = '';
					$selectOption->canonical = 0;
					$selectOption->values = '';
					$c = 0;
					$field->selectoptions = new stdClass();
					$field->selectoptions->$c = $selectOption;
					$field->options = new stdClass();

				} else if(is_array($field->selectoptions)){
					$field->selectoptions = (object)$field->selectoptions;
				}


				if(!empty($field->options) and is_array($field->options)){
					$field->options = (object)$field->options;
				}


				$optAttr = array();

				$optAttr[] = array('value' => '' ,'text' =>vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));
				$optAttr[] = array('value' => 'product_name' ,'text' =>vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME'));
				$optAttr[] = array('value' => 'product_sku', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_SKU'));
				$optAttr[] = array('value' => 'slug', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_ALIAS'));
				$optAttr[] = array('value' => 'product_length', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_LENGTH'));
				$optAttr[] = array('value' => 'product_width', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WIDTH'));
				$optAttr[] = array('value' => 'product_height', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_HEIGHT'));
				$optAttr[] = array('value' => 'product_weight', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WEIGHT'));
				$optAttr[] = array('value' => 'clabels', 'text' => vmText::_ ('COM_VIRTUEMART_CLABELS'));


				$productModel = VmModel::getModel('product');

				$childIds = array();
				$sorted = array();

				$productModel->getAllProductChildIds($product_id,$childIds);

				if(isset($childIds[$product_id])){
					$sorted = self::sortChildIds($product_id,$childIds[$product_id],$field->options);
				}

				array_unshift($sorted,  array('parent_id' => $product_id, 'vm_product_id' => $product_id));

				$showSku = true;

				$k = 0;
				if(empty($field->selectoptions)) $field->selectoptions = array();
				foreach($field->selectoptions as $k=>&$soption){
					$options = array();
					$options[] = array('value' => '' ,'text' =>vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));

					$added = array();

					if($soption->voption!='clabels'){

						foreach($sorted as $i=>$vmProductId){
							if(empty($vmProductId) or $vmProductId['vm_product_id']==$product_id){
								continue;
							}
							$product = $productModel->getProductSingle($vmProductId['vm_product_id'],false);

							if(empty($product->virtuemart_vendor_id) and empty($product->slug)){
								unset($sorted[$i]);
								continue;
							}

							$voption = trim($product->{$soption->voption});

							if(!empty($voption)) {
								$found = false;
								//Guys, dont tell me about in_array or array_search, it does not work here
								foreach($added as $add){
									if($add == $voption){
										$found = true;
									}
								}
								if(!$found){
									$added[] = $voption;
								}
							}
						}

						if($soption->voption=='product_sku'){
							$showSku = false;
						}
					}

					if(!empty($soption->values)){
						$values = explode("\n",$soption->values);
						foreach($values as $value){
							$found = false;
							$value = trim($value);
							foreach($added as $add){
								if($add == $value){
									$found = true;
								}
							}
							if(!$found){
								$added[] = $value;
							}
						}
					}

					$soption->values = implode("\n",$added);

					foreach($added as $value){
						$options[] = array('value' => $value ,'text' =>$value);
					}

					$soption->comboptions = $options;
					if(!isset($soption->clabel)) $soption->clabel = '';
					$soption->slabel = empty($soption->clabel)? vmText::_('COM_VIRTUEMART_'.strtoupper($soption->voption)): vmText::_($soption->clabel);

					if($k==0){
						$html .='<div style="float:left">';
					} else {
						$html .='<div class="removable">';
					}

					$idTag = 'selectoptions'.$k;
					$html .= JHtml::_ ('select.genericlist', $optAttr, 'field[' . $row . '][selectoptions]['.$k.'][voption]', '', 'value', 'text', $soption->voption,$idTag) ;
					$html .= '<input type="text" value="' . $soption->clabel . '" name="field[' . $row . '][selectoptions]['.$k.'][clabel]" style="line-height:2em;margin:5px 5px 0;" />';
					$html .= '<textarea name="field[' . $row . '][selectoptions]['.$k.'][values]" rows="5" cols="35" style="float:none;margin:5px 5px 0;" >'.$soption->values.'</textarea>';

					if($k>0){
						$html .='<span class="vmicon vmicon-16-remove 4remove"></span>';
					} else {

					}
					$html .='</div>';
					if($k==0){
						$html .= '<div style="float:right;max-width:60%;width:45%;min-width:30%" >'.vmText::_('COM_VIRTUEMART_CUSTOM_CV_DESC').'</div>';
						$html .= '<div class="clear"></div>';
					}
				}

				$idTag = 'selectoptions'.++$k;
				$html .= '<fieldset style="background-color:#F9F9F9;">
					<legend>'. vmText::_('COM_VIRTUEMART_CUSTOM_RAMB_NEW').'</legend>
					<div id="new_ramification">';
				//$html .= JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][selectoptions]['.$k.'][voption]', '', 'value', 'text', 'product_name',$idTag) ;
				//$html .= '<input type="text" value="" name="field[' . $row . '][selectoptions]['.$k.'][slabel]" />';

				$html .= JHtml::_ ('select.genericlist', $optAttr, 'voption', '', 'value', 'text', 'product_name','voption') ;
				$html .= '<input type="text" value="" id="vlabel" name="vlabel" />';

				$html .= '<span id="new_ramification_bt"><span class="icon-nofloat vmicon vmicon-16-new"></span>'. vmText::_('COM_VIRTUEMART_ADD').'</span>
					</div>
				</fieldset>';

				vmJsApi::addJScript('new_ramification',"
	jQuery(document).ready(function($) {
		$('#new_ramification_bt').click(function() {
			var voption = $('#voption').val();
			var label = $('#vlabel').val();
			form = document.getElementById('adminForm');
			var newdiv = document.createElement('div');
			newdiv.innerHTML = '<input type=\"text\" value=\"'+voption+'\" name=\"field[" . $row . "][selectoptions][".$k."][voption]\" /><input type=\"text\" value=\"'+label+'\" name=\"field[" . $row . "][selectoptions][".$k."][clabel]\" />';
			form.appendChild(newdiv);

			form.task.value = 'apply';
			form.submit();
			return false;
		});
	});
	");

				if ($product_id) {
					$link=JRoute::_('index.php?option=com_virtuemart&view=product&task=createChild&virtuemart_product_id='.$product_id.'&'.JSession::getFormToken().'=1&target=parent' );
					$add_child_button="";
				} else {
					$link="";
					$add_child_button=" not-active";
				}

				$html .= '<div class="button2-left '.$add_child_button.' btn-wrapper">
						<div class="blank">';
				if ($link) {
					$html .= '<a href="'. $link .'" class="btn btn-small">';
				} else {
					$html .= '<span class="hasTip" title="'.vmText::_ ('COM_VIRTUEMART_PRODUCT_ADD_CHILD_TIP').'">';
				}
				$html .= vmText::_('COM_VIRTUEMART_PRODUCT_ADD_CHILD');
				if ($link) {
					$html .= '</a>';
				} else{
					$html .= '</span>';
				}
				$html .= '</div>
					</div><div class="clear"></div>';
				//vmdebug('my $field->selectoptions',$field->selectoptions,$field->options);
				$html .= '<table id="mvo">';
				$html .= '<thead>';
				$html .= '<tr>
<th style="text-align: left !important;width:130px;">#</th>';
				if($showSku){
					$html .= '<th style="text-align: left !important;width:90px;">'.vmText::_('COM_VIRTUEMART_PRODUCT_SKU').'</th>';
				}
				$html .= '<th style="text-align: left !important;width:80px;">'. vmText::_('COM_VIRTUEMART_PRODUCT_GTIN').'</th>
<th style="text-align: left !important;" width="5%">'.vmText::_('COM_VIRTUEMART_PRODUCT_FORM_PRICE_COST').'</th>
<th style="text-align: left !important;width:30px;">'.vmText::_('COM_VIRTUEMART_PRODUCT_FORM_IN_STOCK').'</th>
<th style="text-align: left !important;width:30px;">'.vmText::_('COM_VIRTUEMART_PRODUCT_FORM_ORDERED_STOCK').'</th>';
				foreach($field->selectoptions as $k=>$option){
					$html .= '<th>'.vmText::_('COM_VIRTUEMART_'.strtoupper($option->voption)).'</th>';
				}
				$html .= '</tr>';
				$html .= '</thead>';
				$html .= '<tbody id="syncro">';

				$i=0;
				if($sorted and is_array($sorted) ){
					foreach($sorted as $i=>$line){
						$html .= self::renderProductChildLine($i,$line,$field,$productModel,$row,$showSku);
					}
				}

				$html .= '</tbody>';
				$html .= '</table>';

				$jsCsort = "

	jQuery(document).ready(function($){
		$('#syncro').sortable({cursorAt: { top: 0, left: 0 },handle: '.vmicon-16-move'});
});
";
				vmJsApi::addJScript('cvSort',$jsCsort);
				return $html;
				// 					return 'Automatic Childvariant creation (later you can choose here attributes to show, now product name) </td><td>';
				break;
			case 'A':
				//vmdebug('displayProductCustomfieldBE $field',$field);
				if(!isset($field->withParent)) $field->withParent = 0;
				if(!isset($field->parentOrderable)) $field->parentOrderable = 0;
				//vmdebug('displayProductCustomfieldBE',$field);
				if (!class_exists('VmHTML')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
				$html = '</td><td>' . vmText::_('COM_VIRTUEMART_CUSTOM_WP').VmHTML::checkbox('field[' . $row . '][withParent]',$field->withParent,1,0,'').'<br />';
				$html .= vmText::_('COM_VIRTUEMART_CUSTOM_PO').VmHTML::checkbox('field[' . $row . '][parentOrderable]',$field->parentOrderable,1,0,'');

				$options = array();
				$options[] = array('value' => 'product_name' ,'text' =>vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME'));
				$options[] = array('value' => 'product_sku', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_SKU'));
				$options[] = array('value' => 'slug', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_ALIAS'));
				$options[] = array('value' => 'product_s_desc', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_S_DESC'));
				$options[] = array('value' => 'product_length', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_LENGTH'));
				$options[] = array('value' => 'product_width', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WIDTH'));
				$options[] = array('value' => 'product_height', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_HEIGHT'));
				$options[] = array('value' => 'product_weight', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WEIGHT'));

				$html .= JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', '', 'value', 'text', $field->customfield_value) ;
				return $html;
				// 					return 'Automatic Childvariant creation (later you can choose here attributes to show, now product name) </td><td>';
				break;
			/* string or integer */
			case 'B':
			case 'S':

				if($field->is_list){
					$options = array();
					$values = explode (';', $field->custom_value);

					foreach ($values as $key => $val) {
						$options[] = array('value' => $val, 'text' => $val);
					}

					$currentValue = $field->customfield_value;
					return $priceInput . '</td><td>'.JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', NULL, 'value', 'text', $currentValue) ;
				} else{
					return $priceInput . '</td><td><input type="text" value="' . vmText::_($field->customfield_value) . '" name="field[' . $row . '][customfield_value]" />';
					break;
				}

				break;
			// Property
			case 'P':
				$options = array();
				$options[] = array('value' => 'product_name' ,'text' =>vmText::_('COM_VIRTUEMART_PRODUCT_FORM_NAME'));
				$options[] = array('value' => 'product_sku', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_SKU'));
				$options[] = array('value' => 'slug', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_ALIAS'));
				$options[] = array('value' => 'product_length', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_LENGTH'));
				$options[] = array('value' => 'product_width', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WIDTH'));
				$options[] = array('value' => 'product_height', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_HEIGHT'));
				$options[] = array('value' => 'product_weight', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_WEIGHT'));
				$options[] = array('value' => 'product_unit', 'text' => vmText::_ ('COM_VIRTUEMART_PRODUCT_UNIT'));

				$html = '</td><td>'.JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', '', 'value', 'text', $field->customfield_value) ;
				if($field->round){
					$html .= '<input type="text" value="' . $field->digits . '" name="field[' . $row . '][round]" />';
				}

				return $html;
			/* parent hint, this is a GROUP and should be G not P*/
			case 'G':
				return $field->customfield_value . '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" /></td><td>';
				break;
			/* image */
			case 'M':

				if($field->is_list and $field->is_input){

					$html = $priceInput . '</td><td>is list ';

					$values = explode (';', $field->custom_value);
					foreach($values as $val){
						$html .= $this->displayCustomMedia ($val,'product');
					}
					return $html;
				} else {
					if(empty($field->custom_value)){
						$q = 'SELECT `virtuemart_media_id` as value,`file_title` as text FROM `#__virtuemart_medias` WHERE `published`=1
					AND (`virtuemart_vendor_id`= "' . $virtuemart_vendor_id . '" OR `shared` = "1")';
						$db = JFactory::getDBO();
						$db->setQuery ($q);
						$options = $db->loadObjectList ();
					} else {
						$values = explode (';', $field->custom_value);
						$mM = VmModel::getModel('media');

						foreach ($values as $key => $val) {
							if(empty($val)) continue;
							$mM->setId($val);
							$file = $mM->getFile();
							if(empty($file->file_type)){
								vmAdminInfo('The media customfield "'.$field->custom_title.'" with custom_id = '.$field->virtuemart_custom_id.' tries to load a non existing media with id = '.$val);
								continue;
							}
							$tmp = array('value' => $val, 'text' => $file->file_name);
							$options[] = (object)$tmp;
						}
					}

					return $priceInput . '</td><td>' . JHtml::_ ('select.genericlist', $options, 'field[' . $row . '][customfield_value]', '', 'value', 'text', $field->customfield_value);
				}

				break;

			case 'D':
				return $priceInput . '</td><td>' . vmJsApi::jDate ($field->customfield_value, 'field[' . $row . '][customfield_value]', 'field_' . $row . '_customvalue') ;
				break;

			//'X'=>'COM_VIRTUEMART_CUSTOM_EDITOR',
			case 'X':
        // Not sure why this block is needed to get it to work when editing the customfield (the subsequent block works fine when creating it, ie. in JS)
				$document = JFactory::getDocument();
				if (strcasecmp(get_class($document),'JDocumentHTML') === 0) {
					$editor = JFactory::getEditor();
					return '</td><td>'.$editor->display('field['.$row.'][customfield_value]',$field->customfield_value, '550', '400', '60', '20', false);
				}
				return $priceInput . '</td><td><textarea class="mceInsertContentNew" name="field[' . $row . '][customfield_value]" id="field-' . $row . '-customfield_value">' . $field->customfield_value . '</textarea>
						<script type="text/javascript">// Creates a new editor instance
							tinymce.execCommand("mceAddControl",true,"field-' . $row . '-customfield_value")
						</script>';
				//return '<input type="text" value="'.$field->customfield_value.'" name="field['.$row.'][customfield_value]" /></td><td>'.$priceInput;
				break;
			//'Y'=>'COM_VIRTUEMART_CUSTOM_TEXTAREA'
			case 'Y':
				return $priceInput . '</td><td><textarea id="field[' . $row . '][customfield_value]" name="field[' . $row . '][customfield_value]" class="inputbox" cols=80 rows=6 >' . $field->customfield_value . '</textarea>';
				//return '<input type="text" value="'.$field->customfield_value.'" name="field['.$row.'][customfield_value]" /></td><td>'.$priceInput;
				break;
			/*Extended by plugin*/
			case 'E':

				$html = '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
				if (!class_exists ('vmCustomPlugin')) {
					require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
				}
				//vmdebug('displayProductCustomfieldBE $field',$field);
				JPluginHelper::importPlugin ('vmcustom', $field->custom_element);
				$dispatcher = JDispatcher::getInstance ();
				$retValue = '';
				$dispatcher->trigger ('plgVmOnProductEdit', array($field, $product_id, &$row, &$retValue));

				return $html . $priceInput   . '</td><td>'. $retValue;
				break;

			/* related category*/
			case 'Z':
				if (empty($field->customfield_value)) {
					return '';
				} // special case it's category ID !

				$q = 'SELECT * FROM `#__virtuemart_categories_' . VmConfig::$vmlang . '` as l INNER JOIN `#__virtuemart_categories` AS c ON l.`virtuemart_category_id` = c.`virtuemart_category_id` WHERE l.`virtuemart_category_id`= "' . (int)$field->customfield_value . '" ';
				$db = JFactory::getDBO();
				$db->setQuery ($q);

				if ($category = $db->loadObject ()) {
					$q = 'SELECT `virtuemart_media_id` FROM `#__virtuemart_category_medias` WHERE `virtuemart_category_id`= "' . (int)$field->customfield_value . '" ';
					$db->setQuery ($q);
					$thumb = '';
					if ($media_id = $db->loadResult ()) {
						$thumb = $this->displayCustomMedia ($media_id,'category');
					}

					$display = '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
					$display .= '<span class="custom_related_image">'.$thumb.'</span><span class="custom_related_title">';
					$display .= JHtml::link ('index.php?option=com_virtuemart&view=category&task=edit&cid=' . (int)$field->customfield_value, $category->category_name, array('title' => $category->category_name,'target'=>'blank')).'</span>';
					return $display;
				}
				else {
					return 'no result $product_id = '.$product_id.' and '.$field->customfield_value;
				}
			/* related product*/
			case 'R':
				if (!$product_id) {
					return '';
				}

				$pModel = VmModel::getModel('product');
				$related = $pModel->getProduct((int)$field->customfield_value,TRUE,FALSE,FALSE,1);
				if (!empty($related->virtuemart_media_id[0])) {
					$thumb = $this->displayCustomMedia ($related->virtuemart_media_id[0]).' ';
				} else {
					$thumb = $this->displayCustomMedia (0).' ';
				}
				$display = '<input type="hidden" value="' . $field->customfield_value . '" name="field[' . $row . '][customfield_value]" />';
				$display .= '<span class="custom_related_image">'.$thumb.'</span><span class="custom_related_title">';
				$display .= JHtml::link ('index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id=' . $related->virtuemart_product_id , $related->product_name, array('title' => $related->product_name,'target'=>'blank')).'</span>';
				return $display;

		}
	}


	static $customfieldRenderer = true;
	/**
	 * @author Max Milbers
	 * @param $product
	 * @param $customfield
	 */
	public static function displayProductCustomfieldFE (&$product, &$customfields) {

		$session = JFactory::getSession ();
		$virtuemart_category_id = $session->get ('vmlastvisitedcategoryid', 0, 'vm');



		if(self::$customfieldRenderer){
			self::$customfieldRenderer = false;

			if (!class_exists ('VmView'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'vmview.php');
			$lPath = VmView::getVmSubLayoutPath ('customfield');

			if($lPath){
				require ($lPath);
			} else {
				vmdebug('displayProductCustomfieldFE layout not found customfield');
			}
		}

		VirtueMartCustomFieldRenderer::renderCustomfieldsFE($product, $customfields, $virtuemart_category_id);

	}
	/**
	 * There are too many functions doing almost the same for my taste
	 * the results are sometimes slighty different and makes it hard to work with it, therefore here the function for future proxy use
	 *
	 */
	static public function displayProductCustomfieldSelected ($product, $html, $trigger) {

		if(self::$customfieldRenderer){
			self::$customfieldRenderer = false;

			if (!class_exists ('VmView'))
				require(VMPATH_SITE . DS . 'helpers' . DS . 'vmview.php');
			$lPath = VmView::getVmSubLayoutPath ('customfield');

			if($lPath){
				require ($lPath);
			} else {
				vmdebug('displayProductCustomfieldFE layout not found customfield');
			}
		}

		return VirtueMartCustomFieldRenderer::renderCustomfieldsCart($product, $html, $trigger);
	}


	/**
	 * TODO This is html and view stuff and MUST NOT be in the model, notice by Max
	 * render custom fields display cart module FE
	 */
	static public function CustomsFieldCartModDisplay ($product) {
		return self::displayProductCustomfieldSelected ($product, '<div class="vm-customfield-mod">', 'plgVmOnViewCartModule');
	}

	/**
	 * render custom fields display cart FE
	 */
	static public function CustomsFieldCartDisplay ($product) {
		return self::displayProductCustomfieldSelected ($product, '<div class="vm-customfield-cart">', 'plgVmOnViewCart');
	}

	/**
	 * render custom fields display order BE/FE
	 */
	static public function CustomsFieldOrderDisplay ($item, $view = 'FE', $absUrl = FALSE) {
		if(empty($item->virtuemart_product_id)) return false;
		if (!empty($item->product_attribute)) {
			$item->customProductData = json_decode ($item->product_attribute, TRUE);
		}
		return self::displayProductCustomfieldSelected ($item, '<div class="vm-customfield-cart">', 'plgVmDisplayInOrder' . $view);
	}

	static function displayCustomMedia ($media_id, $table = 'product', $width = false, $height = false, $absUrl = false) {

		if (!class_exists ('TableMedias'))
			require(VMPATH_ADMIN . DS . 'tables' . DS . 'medias.php');

		$db = JFactory::getDBO ();
		$data = new TableMedias($db);
		$data->load ((int)$media_id);
		if(!empty($data->file_type)){
			$table = $data->file_type;
		}

		if (!class_exists ('VmMediaHandler'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'mediahandler.php');
		$media = VmMediaHandler::createMedia ($data, $table);

		return $media->displayMediaThumb ('', FALSE, '', TRUE, TRUE, $absUrl, $width, $height);
	}

	/**
	 * @deprecated
	 * @param $customPrice
	 * @param $currency
	 * @param $calculator
	 * @return string
	 */
	static function _getCustomPrice($customPrice, $currency, $calculator) {
		if ((float)$customPrice) {
			$price = strip_tags ($currency->priceDisplay ($calculator->calculateCustomPriceWithTax ($customPrice)));
			if ($customPrice >0) {
				$price ="+".$price;
			}
		}
		else {
			$price = ($customPrice === '') ? '' :  vmText::sprintf('COM_VIRTUEMART_CART_PRICE_FREE',$currency->getSymbol());
		}
		return $price;
	}

	static function renderCustomfieldPrice($productCustom,$product,$calculator){

		$customPrice = self::getCustomFieldPriceModificator($productCustom,$product);
		if ((float)$customPrice) {

			if ($customPrice > 0) {
				$sign = vmText::_('COM_VM_PLUS');
			} else {
				$sign = vmText::_('COM_VM_MINUS');
			}

			if(empty($productCustom->multiplyPrice)){
				$v = strip_tags ($calculator->_currencyDisplay->priceDisplay ($calculator->calculateCustomPriceWithTax ($customPrice)));
				if ($customPrice < 0) {
					$v = trim($v,'-');
				}
				$price = vmText::sprintf('COM_VM_CUSTOMFIELD_VARIANT_PRICE',$sign,$v);
			} else {
				$v = trim($productCustom->customfield_price,0);
				$v = trim($v,'.');
				$price = vmText::sprintf('COM_VM_CUSTOMFIELD_VARIANT_PERCENTAGE',$sign,$v);

			}

		}
		else {
			$price = ($customPrice === '') ? '' :  vmText::sprintf('COM_VIRTUEMART_CART_PRICE_FREE',$calculator->_currencyDisplay->getSymbol());
		}
		return $price;
	}

	static function getCustomFieldPriceModificator($productCustom,$product){

		if(empty($productCustom->multiplyPrice)){
			$p = $productCustom->customfield_price;
		} else {
			if($productCustom->multiplyPrice == 'base_productprice' or $productCustom->multiplyPrice == 'base_variantprice'){
				$pVirt = $product->allPrices[$product->selectedPrice]['product_price'];
				if($productCustom->multiplyPrice == 'base_variantprice'){
					$pVirt += $product->modificatorSum ;
				}
				$p = $pVirt * $productCustom->customfield_price * 0.01;
			} else {	//base_modificatorprice
				$p = $product->modificatorSum * $productCustom->customfield_price * 0.01;
			}
		}
		return $p;
	}

	/**
	 * @param $product
	 * @param $variants ids of the selected variants
	 * @return float
	 */
	public function calculateModificators(&$product) {

		if (!isset($product->modificatorSum)){
			$product->modificatorSum = 0.0;
			if(!empty($product->customfields)) {
				foreach( $product->customfields as $k => $productCustom ) {
					$selected = -1;

					if(isset($product->cart_item_id)) {
						if(!class_exists( 'VirtueMartCart' ))
							require(VMPATH_SITE.DS.'helpers'.DS.'cart.php');
						$cart = VirtueMartCart::getCart();

						//vmdebug('my $productCustom->customfield_price '.$productCustom->virtuemart_customfield_id,$cart->cartProductsData,$cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id]);
						if(isset($cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])) {
							$selected = $cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id];

						} else if(isset($cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id])) {
							if($cart->cartProductsData[$product->cart_item_id]['customProductData'][$productCustom->virtuemart_custom_id] == $productCustom->virtuemart_customfield_id) {
								$selected = $productCustom->virtuemart_customfield_id;    //= 1;

							}
						}
						//vmdebug('my $productCustom->customfield_price',$selected,$productCustom->virtuemart_custom_id,$productCustom->virtuemart_customfield_id,$cart->cartProductsData[$product->cart_item_id]['customProductData']);
					} else {

						$pluginFields = vRequest::getVar( 'customProductData', NULL );

						if($pluginFields == NULL and isset($product->customPlugin)) {
							$pluginFields = json_decode( $product->customPlugin, TRUE );
						}

						if(isset($pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])) {
							$selected = $pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id];
						} else if(isset($pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id])) {
							if($pluginFields[$product->virtuemart_product_id][$productCustom->virtuemart_custom_id] == $productCustom->virtuemart_customfield_id) {
								$selected = 1;
							}
						}
					}

					if($selected === -1) {
						continue;
					}

					if(!empty($productCustom) and $productCustom->field_type == 'E') {

						if(!class_exists( 'vmCustomPlugin' )) require(VMPATH_PLUGINLIBS.DS.'vmcustomplugin.php');
						JPluginHelper::importPlugin( 'vmcustom' );
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger( 'plgVmPrepareCartProduct', array(&$product, &$product->customfields[$k], $selected, &$product->modificatorSum) );
					} else {
						if($productCustom->customfield_price) {

							$product->modificatorSum += self::getCustomFieldPriceModificator($productCustom,$product);
						}
					}
				}
			}
		}

		return $product->modificatorSum;
	}


	/** Save and delete from database
	* all product custom_fields and xref
	@ var   $table	: the xref table(eg. product,category ...)
	@array $data	: array of customfields
	@int     $id		: The concerned id (eg. product_id)
	*/
	public function storeProductCustomfields($table,$datas, $id) {

		vRequest::vmCheckToken('Invalid token in storeProductCustomfields');
		//Sanitize id
		$id = (int)$id;

		//Table whitelist
		$tableWhiteList = array('product','category','manufacturer');
		if(!in_array($table,$tableWhiteList)) return false;

		// Get old IDS
		$db = JFactory::getDBO();
		$db->setQuery( 'SELECT `virtuemart_customfield_id` FROM `#__virtuemart_'.$table.'_customfields` as `PC` WHERE `PC`.virtuemart_'.$table.'_id ='.$id );
		$old_customfield_ids = $db->loadColumn();
		if (array_key_exists('field', $datas)) {

			foreach($datas['field'] as $key => $fields){

				if(!empty($datas['field'][$key]['virtuemart_product_id']) and (int)$datas['field'][$key]['virtuemart_product_id']!=$id){
					//aha the field is from the parent, what we do with it?
					$fields['override'] = (int)$fields['override'];
					$fields['disabler'] = (int)$fields['disabler'];
					if($fields['override']!=0 or $fields['disabler']!=0){
						//If it is set now as override, store it as clone, therefore set the virtuemart_customfield_id = 0
						if($fields['override']!=0){
							$fields['override'] = $fields['virtuemart_customfield_id'];
						}
						if($fields['disabler']!=0){
							$fields['disabler'] = $fields['virtuemart_customfield_id'];
						}
						$fields['virtuemart_customfield_id'] = 0;
					}
					else {
						//we do not store customfields inherited by the parent, therefore
						$key = array_search($fields['virtuemart_customfield_id'], $old_customfield_ids );
						if ($key !== false ){
							unset( $old_customfield_ids[ $key ] );
						}
						continue;
					}
				}

				if($fields['field_type']=='C'){
					$cM = VmModel::getModel('custom');
					$c = $cM->getCustom($fields['virtuemart_custom_id'],'');

					if(!empty($c->sCustomId)){

						$sCustId = $c->sCustomId;
						$labels = array();
						foreach($fields['selectoptions'] as $k => $option){
							if($option['voption'] == 'clabels' and !empty($option['clabel'])){
								$labels[$k] = $option['clabel'];
							}
						}

						//for testing
						foreach($fields['options'] as $prodId => $lvalue){
							if($prodId == $id) continue;
							$db->setQuery( 'SELECT `virtuemart_customfield_id` FROM `#__virtuemart_'.$table.'_customfields` as `PC` WHERE `PC`.virtuemart_'.$table.'_id ="'.$prodId.'" AND `virtuemart_custom_id`="'.(int)$sCustId.'" '  );
							$strIds = $db->loadColumn();
							$i=0;
							foreach($lvalue as $k=>$value) {

								if(!empty($labels[$k])) {
									$ts = array();
									$ts['field_type'] = 'S';
									$ts['virtuemart_product_id'] = (int)$prodId;
									$ts['virtuemart_custom_id'] = (int)$sCustId;
									if(isset($strIds[$i])){
										$ts['virtuemart_customfield_id'] = (int)$strIds[$i];
										unset( $strIds[$i++] );
									}
									$ts['customfield_value'] = $value;

									$tableCustomfields = $this->getTable($table.'_customfields');
									$tableCustomfields->bindChecknStore($ts);
								}
							}

							if(count($strIds)>0){
								// delete old unused Customfields
								$db->setQuery( 'DELETE FROM `#__virtuemart_'.$table.'_customfields` WHERE `virtuemart_customfield_id` in ("'.implode('","', $strIds ).'") ');
								$db->execute();
							}
						}
					}
				}

				$fields['virtuemart_'.$table.'_id'] = $id;
				$tableCustomfields = $this->getTable($table.'_customfields');
				$tableCustomfields->setPrimaryKey('virtuemart_product_id');
				if (!empty($datas['customfield_params'][$key]) and !isset($datas['clone']) ) {
					if (array_key_exists( $key,$datas['customfield_params'])) {
						$fields = array_merge ((array)$fields, (array)$datas['customfield_params'][$key]);
					}
				}

				$tableCustomfields->_xParams = 'customfield_params';
				if(!class_exists('VirtueMartModelCustom')) require(VMPATH_ADMIN.DS.'models'.DS.'custom.php');
				VirtueMartModelCustom::setParameterableByFieldType($tableCustomfields,$fields['field_type'],$fields['custom_element'],$fields['custom_jplugin_id']);

				//We do not store default values
				$paramsTemp = array();
				foreach($tableCustomfields->_varsToPushParam as $name=>$attrib){
					if(isset($fields[$name])){
						$paramsTemp[$name] = $attrib;
					} else {
						unset($tableCustomfields->$name);
					}
				}
				$tableCustomfields->_varsToPushParam = $paramsTemp;

				$tableCustomfields->bindChecknStore($fields);

				$key = array_search($fields['virtuemart_customfield_id'], $old_customfield_ids );
				if ($key !== false ) unset( $old_customfield_ids[ $key ] );

			}
		} else {
			vmdebug('storeProductCustomfields nothing to store');
		}
		vmdebug('Delete $old_customfield_ids',$old_customfield_ids);
		if ( count($old_customfield_ids) ) {
			// delete old unused Customfields
			$db->setQuery( 'DELETE FROM `#__virtuemart_'.$table.'_customfields` WHERE `virtuemart_customfield_id` in ("'.implode('","', $old_customfield_ids ).'") ');
			$db->execute();
			vmdebug('Deleted $old_customfield_ids',$old_customfield_ids);
		}


		JPluginHelper::importPlugin('vmcustom');
		$dispatcher = JDispatcher::getInstance();
		if (isset($datas['customfield_params']) and is_array($datas['customfield_params'])) {
			foreach ($datas['customfield_params'] as $key => $plugin_param ) {
				$dispatcher->trigger('plgVmOnStoreProduct', array($datas, $plugin_param ));
			}
		}

	}

	static public function setEditCustomHidden ($customfield, $i) {

		if (!isset($customfield->virtuemart_customfield_id))
			$customfield->virtuemart_customfield_id = '0';
		if (!isset($customfield->virtuemart_product_id))
			$customfield->virtuemart_product_id = '';
		$html = '<input type="hidden" value="' . $customfield->field_type . '" name="field[' . $i . '][field_type]" />
			<input type="hidden" value="' . $customfield->custom_element . '" name="field[' . $i . '][custom_element]" />
			<input type="hidden" value="' . $customfield->custom_jplugin_id . '" name="field[' . $i . '][custom_jplugin_id]" />
			<input type="hidden" value="' . $customfield->virtuemart_custom_id . '" name="field[' . $i . '][virtuemart_custom_id]" />
			<input type="hidden" value="' . $customfield->virtuemart_product_id . '" name="field[' . $i . '][virtuemart_product_id]" />
			<input type="hidden" value="' . $customfield->virtuemart_customfield_id . '" name="field[' . $i . '][virtuemart_customfield_id]" />';
			$html .= '<input class="ordering" type="hidden" value="'.$customfield->ordering.'" name="field['.$i .'][ordering]" />';
		return $html;

	}

	private $_hidden = array();

	public function addHidden ($name, $value = '') {
		$this->_hidden[$name] = $value;
	}

}
// pure php no closing tag
