<?php
defined('_JEXEC') or 	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;
/**

 * @author Max Milbers
 * @version $Id:$
 * @package VirtueMart
 * @subpackage payment
 * @author Max Milbers
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @copyirght Copyright (C) 2011 - 2014 The VirtueMart Team and authors
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.org
 */

if (!class_exists('vmCustomPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');

class plgVmCustomTextinput extends vmCustomPlugin {

	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$varsToPush = array(	'custom_size'=>array(0.0,'int'),
			'custom_price_by_letter'=>array(0.0,'bool')
		);

		$this->setConfigParameterable('customfield_params',$varsToPush);

	}

	// get product param for this plugin on edit
	function plgVmOnProductEdit($field, $product_id, &$row,&$retValue) {

		if ($field->custom_element != $this->_name) return '';

		//VmConfig::$echoDebug = true;
		//vmdebug('plgVmOnProductEdit',$field);
		$html ='
			<fieldset>
				<legend>'. vmText::_('VMCUSTOM_TEXTINPUT') .'</legend>
				<table class="admintable">
					'.VmHTML::row('input','VMCUSTOM_TEXTINPUT_SIZE','customfield_params['.$row.'][custom_size]',$field->custom_size);
		$options = array(0=>'VMCUSTOM_TEXTINPUT_PRICE_BY_INPUT',1=>'VMCUSTOM_TEXTINPUT_PRICE_BY_LETTER');
		$html .= VmHTML::row('select','VMCUSTOM_TEXTINPUT_PRICE_BY_LETTER_OR_INPUT','customfield_params['.$row.'][custom_price_by_letter]',$options,$field->custom_price_by_letter,'','value','text',false);

		//$html .= ($field->custom_price_by_letter==1)?vmText::_('VMCUSTOM_TEXTINPUT_PRICE_BY_LETTER'):vmText::_('VMCUSTOM_TEXTINPUT_PRICE_BY_INPUT');
		$html .='</td>
		</tr>
				</table>
			</fieldset>';
		$retValue .= $html;
		$row++;
		return true ;
	}

	function plgVmOnDisplayProductFEVM3(&$product,&$group) {

		if ($group->custom_element != $this->_name) return '';
		$group->display .= $this->renderByLayout('default',array(&$product,&$group) );

		return true;
	}


	/**
	 * Function for vm3
	 * @see components/com_virtuemart/helpers/vmCustomPlugin::plgVmOnViewCart()
	 * @author Patrick Kohl
	 */
	function plgVmOnViewCart($product,$row,&$html) {
		if (empty($product->productCustom->custom_element) or $product->productCustom->custom_element != $this->_name) return '';
		if (!$plgParam = $this->GetPluginInCart($product)) return '' ;

		foreach($plgParam as $k => $item){

			if(!empty($item['comment']) ){
				if($product->productCustom->virtuemart_customfield_id==$k){
					$html .='<span>'.vmText::_($product->productCustom->custom_title).' '.$item['comment'].'</span>';
				}
			}
		}
		return true;
	}

	/**
	 * Trigger for VM3
	 * @author Max Milbers
	 * @param $product
	 * @param $productCustom
	 * @param $html
	 * @return bool|string
	 */
	function plgVmOnViewCartVM3(&$product, &$productCustom, &$html) {
		if (empty($productCustom->custom_element) or $productCustom->custom_element != $this->_name) return false;

		if(empty($product->customProductData[$productCustom->virtuemart_custom_id][$productCustom->virtuemart_customfield_id])) return false;
		foreach( $product->customProductData[$productCustom->virtuemart_custom_id] as $k =>$item ) {
			if($productCustom->virtuemart_customfield_id == $k) {
				if(isset($item['comment'])){
					$html .='<span>'.vmText::_($productCustom->custom_title).' '.$item['comment'].'</span>';
				}
			}
		}
		return true;
	}

	function plgVmOnViewCartModuleVM3( &$product, &$productCustom, &$html) {
		return $this->plgVmOnViewCartVM3($product,$productCustom,$html);
	}

	function plgVmDisplayInOrderBEVM3( &$product, &$productCustom, &$html) {
		$this->plgVmOnViewCartVM3($product,$productCustom,$html);
	}

	function plgVmDisplayInOrderFEVM3( &$product, &$productCustom, &$html) {
		$this->plgVmOnViewCartVM3($product,$productCustom,$html);
	}


	/**
	 *
	 * vendor order display BE
	 */
	function plgVmDisplayInOrderBE(&$item, $productCustom, &$html) {
		if(!empty($productCustom)){
			$item->productCustom = $productCustom;
		}
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$productCustom,$html); //same render as cart
    }


	/**
	 *
	 * shopper order display FE
	 */
	function plgVmDisplayInOrderFE(&$item, $productCustom, &$html) {
		if(!empty($productCustom)){
			$item->productCustom = $productCustom;
		}
		if (empty($item->productCustom->custom_element) or $item->productCustom->custom_element != $this->_name) return '';
		$this->plgVmOnViewCart($item,$productCustom,$html); //same render as cart
    }



	/**
	 * Trigger while storing an object using a plugin to create the plugin internal tables in case
	 *
	 * @author Max Milbers
	 */
	public function plgVmOnStoreInstallPluginTable($psType,$data,$table) {

		if($psType!=$this->_psType) return false;
		if(empty($table->custom_element) or $table->custom_element!=$this->_name ){
			return false;
		}
		if(empty($table->is_input)){
			vmInfo('COM_VIRTUEMART_CUSTOM_IS_CART_INPUT_SET');
			$table->is_input = 1;
			$table->store();
		}
		//Should the textinput use an own internal variable or store it in the params?
		//Here is no getVmPluginCreateTableSQL defined
 		//return $this->onStoreInstallPluginTable($psType);
	}

	/**
	 * Declares the Parameters of a plugin
	 * @param $data
	 * @return bool
	 */
	function plgVmDeclarePluginParamsCustomVM3(&$data){

		return $this->declarePluginParams('custom', $data);
	}

	function plgVmGetTablePluginParams($psType, $name, $id, &$xParams, &$varsToPush){
		return $this->getTablePluginParams($psType, $name, $id, $xParams, $varsToPush);
	}

	function plgVmSetOnTablePluginParamsCustom($name, $id, &$table,$xParams){
		return $this->setOnTablePluginParams($name, $id, $table,$xParams);
	}

	/**
	 * Custom triggers note by Max Milbers
	 */
	function plgVmOnDisplayEdit($virtuemart_custom_id,&$customPlugin){
		return $this->onDisplayEditBECustom($virtuemart_custom_id,$customPlugin);
	}

	public function plgVmPrepareCartProduct(&$product, &$customfield,$selected,&$modificatorSum){

		if ($customfield->custom_element !==$this->_name) return ;

		//$product->product_name .= 'Ice Saw';
		//vmdebug('plgVmPrepareCartProduct we can modify the product here');

		if (!empty($selected['comment'])) {
			if ($customfield->custom_price_by_letter ==1) {
				$charcount = strlen (html_entity_decode ($selected['comment']));
			} else {
				$charcount = 1.0;
			}
			$modificatorSum += $charcount * $customfield->customfield_price ;
		} else {
			$modificatorSum += 0.0;
		}

		return true;
	}


	public function plgVmDisplayInOrderCustom(&$html,$item, $param,$productCustom, $row ,$view='FE'){
		$this->plgVmDisplayInOrderCustom($html,$item, $param,$productCustom, $row ,$view);
	}

	public function plgVmCreateOrderLinesCustom(&$html,$item,$productCustom, $row ){
// 		$this->createOrderLinesCustom($html,$item,$productCustom, $row );
	}
	function plgVmOnSelfCallFE($type,$name,&$render) {
		$render->html = '';
	}

}

// No closing tag