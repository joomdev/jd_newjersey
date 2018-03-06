<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 9637 2017-09-21 16:40:35Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for the configuration maintenance
 *
 * @package		VirtueMart
 * @subpackage 	Config
 * @author 		RickG
 */
class VirtuemartViewConfig extends VmViewAdmin {

	function display($tpl = null) {

		if (!class_exists('VmImage'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');

		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		if(!class_exists('JFolder'))
			require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');

		$model = VmModel::getModel();
		$usermodel = VmModel::getModel('user');

		JToolBarHelper::title( vmText::_('COM_VIRTUEMART_CONFIG') , 'head vm_config_48');

		$this->addStandardEditViewCommands();

		$this->config = VmConfig::loadConfig();
		if(!empty($this->config->_params)){
			unset ($this->config->_params['pdf_invoice']); // parameter remove and replaced by inv_os
		}

		$this->userparams = JComponentHelper::getParams('com_users');

		$this->jTemplateList = ShopFunctions::renderTemplateList(vmText::_('COM_VIRTUEMART_ADMIN_CFG_JOOMLA_TEMPLATE_DEFAULT'));

		$this->vmLayoutList = $model->getLayoutList('virtuemart');

		$this->cartLayoutList = $model->getLayoutList('cart',array('padded.php','perror.php','orderdone.php'), false);
		$this->categoryLayoutList = $model->getLayoutList('category', 0, false);

		$this->productLayoutList = $model->getLayoutList('productdetails', 0, false);

		$this->productsFieldList  = $model->getFieldList('products');

		$this->noimagelist = $model->getNoImageList();

		$this->orderStatusModel= VmModel::getModel('orderstatus');

		$this->os_Options = $this->osWoP_Options = $this->osDel_Options = $this->orderStatusModel->getOrderStatusNames();
		$emptyOption = JHtml::_ ('select.option', -1, vmText::_ ('COM_VIRTUEMART_NONE'), 'order_status_code', 'order_status_name');

		$this->userFieldsModel= VmModel::getModel('userfields');
		$this->emailSf_Options = $this->userFieldsModel->getUserfieldsList('emailaddress');

		array_unshift ($this->os_Options, $emptyOption);

		unset($this->osWoP_Options['P']);
		array_unshift ($this->osWoP_Options, $emptyOption);

		$deldate_inv = JHtml::_ ('select.option', 'm', vmText::_ ('COM_VIRTUEMART_DELDATE_INV'), 'order_status_code', 'order_status_name');
		unset($this->osDel_Options['P']);
		array_unshift ($this->osDel_Options, $deldate_inv);
		array_unshift ($this->osDel_Options, $emptyOption);

		//vmdebug('my $this->os_Options',$this->osWoP_Options);

		$this->currConverterList = $model->getCurrencyConverterList();

		$this->activeShopLanguage = $model->getActiveLanguages( VmConfig::get('vmDefLang'), 'vmDefLang', false, 'COM_VIRTUEMART_ADMIN_CFG_POOS_GLOBAL' );
		$this->activeLanguages = $model->getActiveLanguages( VmConfig::get('active_languages') );

		$this->orderByFieldsProduct = $model->getProductFilterFields('browse_orderby_fields');

		VmModel::getModel('category');
		foreach (VirtueMartModelCategory::$_validOrderingFields as $key => $field ) {
			if($field=='c.category_shared') continue;
			$fieldWithoutPrefix = $field;
			$dotps = strrpos($fieldWithoutPrefix, '.');
			if($dotps!==false){
				$prefix = substr($field, 0,$dotps+1);
				$fieldWithoutPrefix = substr($field, $dotps+1);
			}

			$text = vmText::_('COM_VIRTUEMART_'.strtoupper(str_replace(array(',',' '),array('_',''),$fieldWithoutPrefix))) ;
			$orderByFieldsCat[] =  JHtml::_('select.option', $field, $text) ;
		}

		$this->orderByFieldsCat = $orderByFieldsCat;

		$this->searchFields = $model->getProductFilterFields( 'browse_search_fields');

		$this->aclGroups = $usermodel->getAclGroupIndentedTree();

		if(!class_exists('VmTemplate')) require(VMPATH_SITE.DS.'helpers'.DS.'vmtemplate.php');
		$this->vmtemplate = VmTemplate::loadVmTemplateStyle();
		$this->imagePath = shopFunctions::getAvailabilityIconUrl($this->vmtemplate);

		$this->listShipment = $this -> listIt('shipment');
		$this->listPayment = $this -> listIt('payment');

		$this->orderDirs[] = JHtml::_('select.option', 'ASC' , vmText::_('Ascending')) ;
		$this->orderDirs[] = JHtml::_('select.option', 'DESC' , vmText::_('Descending')) ;

		shopFunctions::checkSafePath();
		$this -> checkTCPDFinstalled();
		$this -> checkVmUserVendor();

		//$this -> checkClientIP();
		parent::display($tpl);
	}

	private function listIt($ps){
		$db = JFactory::getDBO();
		$q = 'SELECT m.virtuemart_'.$ps.'method_id, l.'.$ps.'_name
FROM #__virtuemart_'.$ps.'methods as m
INNER JOIN #__virtuemart_'.$ps.'methods_'.VmConfig::$vmlang.' as l ON l.virtuemart_'.$ps.'method_id = m.virtuemart_'.$ps.'method_id
WHERE published="1"';
		$db->setQuery($q);

		try {
			$options = $db->loadAssocList();
		} catch (Exception $e){
			return array();
		}
		if(empty($options)) $options = array();
		$emptyOption = JHtml::_('select.option', '0', vmText::_('COM_VIRTUEMART_NOPREF'),'virtuemart_'.$ps.'method_id',$ps.'_name');
		array_unshift($options,$emptyOption);
		$emptyOption = JHtml::_('select.option', '-1', vmText::_('COM_VIRTUEMART_NONE'),'virtuemart_'.$ps.'method_id',$ps.'_name');
		array_unshift($options,$emptyOption);
		return $options;
	}

	private function checkVmUserVendor(){

		$db = JFactory::getDBO();
		$multix = Vmconfig::get('multix','none');

		$q = 'select * from #__virtuemart_vmusers where user_is_vendor = 1';// and virtuemart_vendor_id '.$vendorWhere.' limit 1';
		$db->setQuery($q);
		$r = $db->loadAssocList();

		if (empty($r)){
			vmWarn('Your Virtuemart installation contains an error: No user is marked as vendor. Please fix this in your phpMyAdmin and set #__virtuemart_vmusers.user_is_vendor = 1 and #__virtuemart_vmusers.virtuemart_vendor_id = 1 to one of your administrator users.');
		} else {
			if($multix=='none' and count($r)!=1){
				vmWarn('You are using single vendor mode, but it seems more than one user is set as vendor');
			}
			foreach($r as $entry){
				if(empty($entry['virtuemart_vendor_id'])){
					vmWarn('The user with virtuemart_user_id = '.$entry['virtuemart_user_id'].' is set as vendor, but has no referencing vendorId.');
				}
			}
		}
	}

	private function checkTCPDFinstalled(){

		if(!file_exists(VMPATH_LIBS.DS.'tcpdf'.DS.'tcpdf.php')){
			vmWarn('COM_VIRTUEMART_TCPDF_NINSTALLED');
		}
	}

	private function checkClientIP(){
		$revproxvar = VmConfig::get('revproxvar','');
		if(!empty($revproxvar)) vmdebug('My server variable ',$_SERVER);
	}

	static $options = array();
	static public function rowShopFrontSet($params, $label, $name, $name2, $name3 = 0, $default = 1){

		$lang =JFactory::getLanguage();
		if($lang->hasKey($label.'_TIP')){
			$label = '<span class="hasTip" title="'.htmlentities(vmText::_($label.'_TIP')).'">'.vmText::_($label).'</span>' ;
		} //Fallback
		else if($lang->hasKey($label.'_EXPLAIN')){
			$label = '<span class="hasTip" title="'.htmlentities(vmText::_($label.'_EXPLAIN')).'">'.vmText::_($label).'</span>' ;
		} else {
			$label = vmText::_($label);
		}

		$h = '<tr>';
		$h .= '<td class="key">
				'.$label.'
			</td>';
		//$h .= '<td style="text-align: center;">'.VmHtml::checkbox($name, $params->get($name, 1)).'</td>';
		$h .= '<td style="text-align: center;">'.JHtml::_ ('Select.genericlist', self::$options, $name, '', 'value', 'text', $params->get($name, 1)).'</td>';


		$h .= '<td>'.VmHtml::input($name2, $params->get($name2, $default),'class="inputbox"','',4,4).'</td>';
		$h .= '<td style="text-align: center;">';
		if($name3 !== 0) $h .= JHtml::_ ('Select.genericlist', self::$options, $name3, '', 'value', 'text', $params->get($name3, 1));
		$h .= "</td>\n</tr>";
		return $h;
	}


	/**
	 * Writes a line  for the price configuration
	 *
	 * @author Max Milberes
	 * @param string $name
	 * @param string $langkey
	 */
	static function writePriceConfigLine ($array, $name, $langkey) {

		if (!class_exists ('VmHTML')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
		}
		if(is_object($array)) $array = get_object_vars($array);
		if(!isset($array[$name])) $array[$name] = 0;
		if(!isset($array[$name . 'Text'])) $array[$name . 'Text'] = 0;
		if(!isset($array[$name . 'Rounding'])) $array[$name . 'Rounding'] = -1;
		$html =
		'<tr>
				<td class="key">
					<span class="editlinktip hasTip" title="' . vmText::_ ($langkey . '_EXPLAIN') . '">
						<label>' . vmText::_ ($langkey) .
		'</label>
					</span>
				</td>

				<td>' .
		VmHTML::checkbox ($name, $array[$name]) . '
				</td>
				<td align="center">' .
		VmHTML::checkbox ($name . 'Text', $array[$name . 'Text']) . '
				</td>
				<td align="center">
				<input type="text" value="' . $array[$name . 'Rounding'] . '" class="inputbox" size="4" name="' . $name . 'Rounding">
				</td>
			</tr>';
		return $html;
	}
}
// pure php no closing tag
