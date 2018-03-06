<?php


defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
if (!class_exists('ShopFunctions'))
require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');

if (!class_exists('TableCategories'))
require(VMPATH_ADMIN . DS . 'tables' . DS . 'categories.php');


/**
 * Supports a modal product picker.
 *
 *
 */
class JFormFieldProduct extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @author      Valerie Cartan Isaksen
	 * @var		string
	 *
	 */
	var $type = 'product';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */


	function getInput() {

		$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
		$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
		VmConfig::loadConfig();
		return JHtml::_('select.genericlist',  $this->_getProducts(), $this->name, 'class="inputbox"   ', 'value', 'text', $this->value, $this->id);
	}
	private function _getProducts() {
		if (!class_exists('VmModel'))
		require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmmodel.php');
		$productModel = VmModel::getModel('Product');
		$productModel->_noLimit = true;
		if(vmAccess::manager('managevendors')){
			$productModel->virtuemart_vendor_id = 0;
		}
		$products = $productModel->getProductListing(false, false, false, false, true,false);
		$productModel->_noLimit = false;
		$i = 0;
		$list = array();
		foreach ($products as $product) {
			$list[$i]['value'] = $product->virtuemart_product_id;
			$list[$i]['text'] = $product->product_name. " (". $product->product_sku.")";
			$i++;
		}
		return $list;
	}

}