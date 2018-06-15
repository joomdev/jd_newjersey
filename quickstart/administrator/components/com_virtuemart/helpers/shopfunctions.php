<?php
defined ('_JEXEC') or die('Direct Access to ' . basename (__FILE__) . ' is not allowed.');

/**
 * General helper class
 *
 * This class provides some shop functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @author Patrick Kohl
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 * @version $Id: shopfunctions.php 9772 2018-02-28 21:16:40Z Milbo $
 */
class ShopFunctions {

	/**
	 * Contructor
	 */
	public function __construct () {

	}

	/**
	 * Builds an enlist for information (not chooseable)
	 *
	 * @author Max Milbers
	 *
	 * @param array $idList ids
	 * @param string $table vmTable to use
	 * @param string $name fieldname for the name
	 * @param string $view view for the links
	 * @param bool $tableXref the xref table
	 * @param bool $tableSecondaryKey the fieldname of the xref table
	 * @param int $quantity
	 * @param bool $translate
	 * @return string
	 */
	static public function renderGuiList ($idList, $table, $name, $view, $tableXref = false, $tableSecondaryKey = false, $quantity = 3, $translate = true ) {

		$list = '';
		$ttip = '';
		$link = '';

		if ($view != 'user' and $view != 'shoppergroup') {
			$cid = 'cid';
		} else if ($view == 'user'){
			$cid = 'virtuemart_user_id';
		} else {
			$cid = 'virtuemart_shoppergroup_id';
		}

		$model = new VmModel();
		$table = $model->getTable($table);

		if(!is_array($idList)){
			$db = JFactory::getDBO ();
			$q = 'SELECT `' . $table->getPKey() . '` FROM `#__virtuemart_' . $db->escape ($tableXref) . '` WHERE ' . $db->escape ($tableSecondaryKey) . ' = "' . (int)$idList . '"';
			$db->setQuery ($q);
			$idList = $db->loadColumn ();
		}

		$i = 0;

		foreach($idList as $id ){

			$item = $table->load ((int)$id);
			if($translate) $item->$name = vmText::_($item->$name);
			$link = ', '.JHtml::_('link', JRoute::_('index.php?option=com_virtuemart&view='.$view.'&task=edit&'.$cid.'[]='.$id,false), $item->$name);
			if($i<$quantity and $i<=count($idList)){
				$list .= $link;
			} else if ($i==$quantity and $i<count($idList)){
				$list .= ',...';
			}
			$ttip .= ', '.$item->$name;
			if($i>($quantity + 6)) {
				$ttip .= ',...';
				break;
			}
			$i++;
		}

		if(!$list) return '';
		$list = substr ($list, 2);
		$ttip = substr ($ttip, 2);

		return '<span class="hasTip" title="'.$ttip.'" >' . $list . '</span>';
	}

	/**
	 * Creates a Drop Down list of available Vendors
	 *
	 * @author Max Milbers
	 * @access public
	 * @param int $virtuemart_shoppergroup_id the shopper group to pre-select
	 * @param bool $multiple if the select list should allow multiple selections
	 * @return string HTML select option list
	 */
	static public function renderVendorList ($vendorId=false, $name = 'virtuemart_vendor_id') {

		$view = vRequest::getCmd('view',false);
		
		if($vendorId === false){
			if(vmAccess::manager('managevendors')){
				$vendorId = strtolower (JFactory::getApplication()->getUserStateFromRequest ('com_virtuemart.'.$view.'.virtuemart_vendor_id', 'virtuemart_vendor_id', vmAccess::getVendorId(), 'int'));
			} else{
				$vendorId = vmAccess::getVendorId();
			}
		}

		if(!vmAccess::manager(array($view,'managevendors'),0,true)) {
			if (empty($vendorId)) {
				$vendor = vmText::_('COM_VIRTUEMART_USER_NOT_A_VENDOR');
			} else {
				$db = JFactory::getDBO ();
				$q = 'SELECT `vendor_name` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id` = "' . (int)$vendorId . '" ';
				$db->setQuery ($q);
				$vendor = $db->loadResult ();
			}
			return '<span type="text" size="14" class="inputbox" readonly="">' . $vendor . '</span>';
		} else {
			return self::renderVendorFullVendorList($vendorId,false,$name);
		}

	}

	static public function renderVendorFullVendorList($vendorId, $multiple = false, $name = 'virtuemart_vendor_id'){

		$db = JFactory::getDBO ();

		$q = 'SELECT `virtuemart_vendor_id`,`vendor_name` FROM #__virtuemart_vendors';
		$db->setQuery ($q);
		$vendors = $db->loadAssocList ();

		$attrs = array();
		if(!class_exists('VmHtml')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');
		$id = VmHtml::ensureUniqueId('vendor_name'.$vendorId);
		$idA = $name;
		$attrs['class'] = 'vm-chzn-select vm-drop';
		if ($multiple) {
			$attrs['multiple'] = 'multiple';
			$idA .= '[]';
		} else {
			$emptyOption = JHtml::_ ('select.option', '', vmText::_ ('COM_VIRTUEMART_LIST_EMPTY_OPTION'), 'virtuemart_vendor_id', 'vendor_name');
			array_unshift ($vendors, $emptyOption);
		}
		$listHTML = JHtml::_ ('select.genericlist', $vendors, $idA, $attrs, 'virtuemart_vendor_id', 'vendor_name', $vendorId, $id);
		return $listHTML;
	}

	/**
	 * Creates a Drop Down list of available Shopper Groups
	 *
	 * @author Max Milbers
	 * @access public
	 * @param int $shopperGroupId the shopper group to pre-select
	 * @param bool $multiple if the select list should allow multiple selections
	 * @return string HTML select option list
	 */
	static public function renderShopperGroupList ($shopperGroupId = 0, $multiple = TRUE,$name='virtuemart_shoppergroup_id', $select_attribute='COM_VIRTUEMART_DRDOWN_AVA2ALL', $attrs = array() ) {
		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);

		$shopperModel = VmModel::getModel ('shoppergroup');
		$shoppergrps = $shopperModel->getShopperGroups (FALSE, TRUE);


		if(empty($attrs['class'])) $attrs['class'] = 'vm-chzn-select vm-drop';
		//if(!isset($attrs['style'])) $attrs['style'] = 'min-width:150px'; //$attrs['style']='width: 200px;';
		if ($multiple) {
			$attrs['multiple'] = 'multiple';
			$attrs['data-placeholder'] = vmText::_($select_attribute);
			if($name=='virtuemart_shoppergroup_id'){
				$name.= '[]';
			}
		} else {
			$emptyOption = JHTML::_ ('select.option', '', vmText::_ ($select_attribute), 'virtuemart_shoppergroup_id', 'shopper_group_name');
			array_unshift ($shoppergrps, $emptyOption);
		}

		$listHTML = JHTML::_ ('select.genericlist', $shoppergrps, $name, $attrs, 'virtuemart_shoppergroup_id', 'shopper_group_name', $shopperGroupId,'[',true);
		$listHTML .= '<input name="virtuemart_shoppergroup_set" value="1" type="hidden">';
		return $listHTML;
	}

	/**
	 * Renders the list of Manufacturers
	 *
	 * @author St. Kraft
	 * Mod. <mediaDESIGN> St.Kraft 2013-02-24 Herstellerrabatt
	 */
	static public function renderManufacturerList ($manufacturerId = 0, $multiple = FALSE, $name = 'virtuemart_manufacturer_id') {

		$manufacturerModel = VmModel::getModel ('manufacturer');
		$manufacturers = $manufacturerModel->getManufacturers (FALSE, TRUE);
		//$attrs = array('style'=>"width: 210px");
		$attrs['class'] = 'vm-chzn-select vm-drop';
		//$attrs['style'] = 'min-width:150px';
		if ($multiple) {
			$attrs['multiple'] = 'multiple';
			if($name=='virtuemart_manufacturer_id')	$name.= '[]';
		} else {
			$emptyOption = JHtml::_ ('select.option', '', vmText::_ ('COM_VIRTUEMART_LIST_EMPTY_OPTION'), 'virtuemart_manufacturer_id', 'mf_name');
			array_unshift ($manufacturers, $emptyOption);
		}

		$listHTML = JHtml::_ ('select.genericlist', $manufacturers, $name, $attrs, 'virtuemart_manufacturer_id', 'mf_name', $manufacturerId);
		return $listHTML;
	}


	/**
	 * Renders the list for the tax rules
	 *
	 * @author Max Milbers
	 */
	static function renderTaxList ($selected, $name = 'product_tax_id', $class = '') {

		if (!class_exists ('VirtueMartModelCalc')) {
					require(VMPATH_ADMIN . DS . 'models' . DS . 'calc.php');
				}
		$taxes = VirtueMartModelCalc::getTaxes ();

		$taxrates = array();
		$taxrates[] = JHtml::_ ('select.option', '-1', vmText::_ ('COM_VIRTUEMART_PRODUCT_TAX_NONE'), $name);
		$taxrates[] = JHtml::_ ('select.option', '0', vmText::_ ('COM_VIRTUEMART_PRODUCT_TAX_NO_SPECIAL'), $name);
		foreach ($taxes as $tax) {
			$taxrates[] = JHtml::_ ('select.option', $tax->virtuemart_calc_id, $tax->calc_name, $name);
		}
		$listHTML = JHtml::_ ('Select.genericlist', $taxrates, $name, $class, $name, 'text', $selected,'[');
		return $listHTML;
	}

	/**
	 * Creates the chooseable template list
	 *
	 * @author Max Milbers, impleri
	 *
	 * @param string defaultText Text for the empty option
	 * @param boolean defaultOption you can supress the empty otion setting this to false
	 * return array of Template objects
	 */
	static public function renderTemplateList ($defaultText = 0, $defaultOption = TRUE) {

		if (empty($defaultText)) {
			$defaultText = vmText::_ ('COM_VIRTUEMART_TEMPLATE_DEFAULT');
		}

		$defaulttemplate = array();
		if ($defaultOption) {
			$defaulttemplate[0] = new stdClass;
			$defaulttemplate[0]->name = $defaultText;
			$defaulttemplate[0]->directory = 0;
			$defaulttemplate[0]->value = '';
		}

		$q = 'SELECT * FROM `#__template_styles` WHERE `client_id`="0"';
		$db = JFactory::getDbo();
		$db->setQuery($q);

		$jtemplates = $db->loadObjectList();

		foreach ($jtemplates as $key => $template) {
			$template->name = $template->title;
			$template->value = $template->id;
			$template->directory = $template->template;
		}

		return array_merge ($defaulttemplate, $jtemplates);
	}

	static function renderOrderingList($table,$fieldname,$selected,$where='',$orderingField = 'ordering'){

// Ordering dropdown
		$qry = 'SELECT ordering AS value, '.$fieldname.' AS text'
			. ' FROM #__virtuemart_'.$table.' '.$where
			. ' ORDER BY '.$orderingField;
		$db = JFactory::getDbo();
		$db->setQuery($qry);
		$orderStatusList = $db -> loadAssocList();
		foreach($orderStatusList as &$text){
			$text['text'] = $text['value'].' '.vmText::_($text['text']);
		}
		return JHtml::_('select.genericlist',$orderStatusList,'ordering','','value','text',$selected);
	}

	/**
	 * Returns all the weight unit
	 *
	 * @author Valérie Isaksen
	 */
	static function getWeightUnit () {

		static $weigth_unit;
		if ($weigth_unit) {
			return $weigth_unit;
		}
		return $weigth_unit = array(
			'KG' => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_KG')
		, 'G'   => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_G')
		, 'MG'   => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_MG')
		, 'LB'   => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_LB')
		, 'OZ'   => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_ONCE')
		);
	}

	/**
	 * Renders the string for the
	 *
	 * @author Valérie Isaksen
	 */
	static function renderWeightUnit ($name) {

		$weigth_unit = self::getWeightUnit ();
		if (isset($weigth_unit[$name])) {
			return $weigth_unit[$name];
		} else {
			return '';
		}
	}

	/**
	 * Renders the list for the Weight Unit
	 *
	 * @author Valérie Isaksen
	 */
	static function renderWeightUnitList ($name, $selected) {

		$weight_unit_default = self::getWeightUnit ();
		foreach ($weight_unit_default as  $key => $value) {
			$wu_list[] = JHtml::_ ('select.option', $key, $value, $name);
		}
		$listHTML = JHtml::_ ('Select.genericlist', $wu_list, $name, '', $name, 'text', $selected);
		return $listHTML;

	}

	static function renderUnitIsoList($name, $selected){

		$weight_unit_default = explode(',',VmConfig::get('norm_units', 'KG,100G,M,SM,CUBM,L,100ML,P'));

		foreach ($weight_unit_default as  $value) {
			$wu_list[] = JHtml::_ ('select.option', $value, vmText::_('COM_VIRTUEMART_UNIT_SYMBOL_'.strtoupper(trim($value))), $name);
		}
		$listHTML = JHtml::_ ('Select.genericlist', $wu_list, $name, '', $name, 'text', $selected);
		return $listHTML;
	}

	/**
	 * typo problem with the function name. We must keep the other one for compatibility purposes
	 * @deprecated
	 * @param $value
	 * @param $from
	 * @param $to
	 */
	static function convertWeigthUnit ($value, $from, $to) {
		return self::convertWeightUnit ($value, $from, $to);
	}
	/**
	 * Convert Weight Unit
	 *
	 * @author Valérie Isaksen
	 */
	static function convertWeightUnit ($value, $from, $to) {

		$from = strtoupper($from);
		$to = strtoupper($to);
		$value = str_replace (',', '.', $value);
		if ($from === $to) {
			return $value;
		}

		$g = (float)$value;

		switch ($from) {
			case 'KG':
				$g = (float)(1000 * $value);
			break;
			case 'MG':
				$g = (float)($value / 1000);
			break;
			case 'LB':
				$g = (float)(453.59237 * $value);
			break;
			case 'OZ':
				$g = (float)(28.3495 * $value);
			break;
		}
		switch ($to) {
			case 'KG' :
				$value = (float)($g / 1000);
				break;
			case 'G' :
				$value = $g;
				break;
			case 'MG' :
				$value = (float)(1000 * $g);
				break;
			case 'LB' :
				$value = (float)($g / 453.59237);
				break;
			case 'OZ' :
				$value = (float)($g / 28.3495);
				break;
		}
		return $value;
	}

	/**
	 * Convert Metric Unit
	 *
	 * @author Florian Voutzinos
	 */
	static function convertDimensionUnit ($value, $from, $to) {

		$from = strtoupper($from);
		$to = strtoupper($to);
		$value = (float)str_replace (',', '.', $value);
		if ($from === $to) {
			return $value;
		}
		$meter = (float)$value;

		// transform $value in meters
		switch ($from) {
			case 'CM':
				$meter = (float)(0.01 * $value);
				break;
			case 'MM':
				$meter = (float)(0.001 * $value);
				break;
			case 'YD' :
				$meter =(float) (0.9144 * $value);
				break;
			case 'FT' :
				$meter = (float)(0.3048 * $value);
				break;
			case 'IN' :
				$meter = (float)(0.0254 * $value);
				break;
		}
		switch ($to) {
			case 'M' :
				$value = $meter;
				break;
			case 'CM':
				$value = (float)($meter / 0.01);
				break;
			case 'MM':
				$value = (float)($meter / 0.001);
				break;
			case 'YD' :
				$value =(float) ($meter / 0.9144);
				break;
			case 'FT' :
				$value = (float)($meter / 0.3048);
				break;
			case 'IN' :
				$value = (float)($meter / 0.0254);
				break;
		}
		return $value;
	}

	/**
	 * Renders the list for the Length, Width, Height Unit
	 *
	 * @author Valérie Isaksen
	 */
	static function renderLWHUnitList ($name, $selected) {

		if (!class_exists ('VmHTML')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
		}

		$lwh_unit_default = array('M' => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_M')
		, 'CM'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_CM')
		, 'MM'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_MM')
		, 'YD'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_YARD')
		, 'FT'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_FOOT')
		, 'IN'                        => vmText::_ ('COM_VIRTUEMART_UNIT_NAME_INCH')
		);
		foreach ($lwh_unit_default as  $key => $value) {
			$lu_list[] = JHtml::_ ('select.option', $key, $value, $name);
		}
		$listHTML = JHtml::_ ('Select.genericlist', $lu_list, $name, '', $name, 'text', $selected);
		return $listHTML;

	}

	/**
	 * This generates the list when the user have different ST addresses saved
	 * @deprecated use shopFunctionsF::generateStAddressList instead
	 * @author Oscar van Eijk
	 */
	static function generateStAddressList ($view, $userModel, $task) {
		if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		return shopFunctionsF::generateStAddressList($view, $userModel, $task);
	}

	/**
	 * @deprecated use shopFunctionsF::renderVendorAddress instead
	 * @param $vendorId
	 * @param string $lineSeparator
	 * @param array $skips
	 * @return string
	 */
	static public function renderVendorAddress ($vendorId,$lineSeparator="<br />", $skips = array('name','username','email','agreed')) {
		if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		return shopFunctionsF::renderVendorAddress($vendorId, $lineSeparator, $skips);
	}

	public static $counter = 0;
	public static $categoryTree = array();

	static public function categoryListTree ($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields = array()) {

		if(!is_array($selectedCategories)){
			$selectedCategories = array($selectedCategories);
		}
		$hash = crc32(implode('.',$selectedCategories).':'.$cid.':'.$level.implode('.',$disabledFields));
		if (empty(self::$categoryTree[$hash])) {

			$app = JFactory::getApplication ();
			$cache = VmConfig::getCache ('com_virtuemart_cats');
			$cache->setCaching (1);

			$vendorId = vmAccess::isSuperVendor();
			self::$categoryTree[$hash] = $cache->call (array('ShopFunctions', 'categoryListTreeLoop'), $selectedCategories, $cid, $level, $disabledFields,$app->isSite(),$vendorId,VmConfig::$vmlang);

			//self::$categoryTree[$hash] = ShopFunctions::categoryListTreeLoop($selectedCategories, $cid, $level, $disabledFields,$app->isSite(),$vendorId,VmConfig::$vmlang);
		}

		return self::$categoryTree[$hash];
	}



	/**
	 * Creates structured option fields for all categories
	 *
	 * @todo: Connect to vendor data
	 * @author Max Milbers, jseros
	 * @param array 	$selectedCategories All category IDs that will be pre-selected
	 * @param int 		$cid 		Internally used for recursion
	 * @param int 		$level 		Internally used for recursion
	 * @return string 	$category_tree HTML: Category tree list
	 */
	static public function categoryListTreeLoop ($selectedCategories = array(), $cid = 0, $level = 0, $disabledFields = array(), $isSite, $vendorId, $vmlang,$categoryParentName='') {

		static $categoryTree = '';
		if($level==0) {
			$categoryTree = '';
			self::$counter = 0;
		}
		self::$counter++;
		$categoryModel = VmModel::getModel ('category');
		$level++;

		$categoryModel->_noLimit = TRUE;

		$records = $categoryModel->getCategories ($isSite, $cid,false,'',$vendorId);

		$selected = "";
		if (!empty($records)) {
			foreach ($records as $key => $category) {

				$childId = $category->category_child_id;

				if ($childId != $cid) {
					if (in_array ($childId, $selectedCategories)) {
						$selected = 'selected=\"selected\"';
					} else {
						$selected = '';
					}

					$disabled = '';
					if (in_array ($childId, $disabledFields)) {
						$disabled = 'disabled="disabled"';
					}

					if ($disabled != '' && stristr ($_SERVER['HTTP_USER_AGENT'], 'msie')) {
						//IE7 suffers from a bug, which makes disabled option fields selectable
					} else {
						$categoryTree .= '<option ' . $selected . ' ' . $disabled . ' value="' . $childId . '">';
						$categoryName = $category->category_name;
						if(VmConfig::get('full_catname_tree',0)) {
							if (!empty($categoryParentName)) {
								$categoryName =$categoryParentName.' | '.$category->category_name;
							}
						} else {
							$categoryTree .= str_repeat (' - ', ($level - 1));
						}
						$categoryTree .= $categoryName . '</option>';
					}
				}

				if ($categoryModel->hasChildren ($childId)) {
					self::categoryListTreeLoop ($selectedCategories, $childId, $level, $disabledFields,$isSite, $vendorId, $vmlang, $categoryName);
				}

			}
		}

		return $categoryTree;
	}


	/**
	 * Return the countryname or code of a given countryID
	 *
	 * @author Oscar van Eijk
	 * @access public
	 * @param int $id Country ID
	 * @param char $fld Field to return: country_name (default), country_2_code or country_3_code.
	 * @return string Country name or code
	 */
	static public function getCountryByID ($id, $fld = 'country_name') {

		if (empty($id)) {
			return '';
		}

		$id = (int)$id;
		$db = JFactory::getDBO ();

		$q = 'SELECT `' . $db->escape ($fld) . '` AS fld FROM `#__virtuemart_countries` WHERE virtuemart_country_id = ' . (int)$id;
		$db->setQuery ($q);
		return $db->loadResult ();
	}

	/**
	 * Return the virtuemart_country_id of a given country name
	 *
	 * @author Oscar van Eijk
	 * @author Max Milbers
	 * @access public
	 * @param string $name Country name (can be country_name or country_3_code  or country_2_code )
	 * @return int virtuemart_country_id
	 */
	static public function getCountryIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		VmModel::getModel('country');
		$c = VirtueMartModelCountry::getCountryByCode($name);
		return $c -> virtuemart_country_id;
	}

	/**
	 * Return the statename or code of a given virtuemart_state_id
	 *
	 * @author Oscar van Eijk
	 * @access public
	 * @param int $id State ID
	 * @param char $fld Field to return: state_name (default), state_2_code or state_3_code.
	 * @return string state name or code
	 */
	static public function getStateByID ($id, $fld = 'state_name') {

		if (empty($id)) {
			return '';
		}
		$db = JFactory::getDBO ();
		$q = 'SELECT ' . $db->escape ($fld) . ' AS fld FROM `#__virtuemart_states` WHERE virtuemart_state_id = "' . (int)$id . '"';
		$db->setQuery ($q);
		$r = $db->loadObject ();
		return $r->fld;
	}

	/**
	 * Return the stateID of a given state name
	 *
	 * @author Max Milbers
	 * @access public
	 * @param string $name Country name
	 * @return int virtuemart_state_id
	 */
	static public function getStateIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		$db = JFactory::getDBO ();
		if (strlen ($name) === 2) {
			$fieldname = 'state_2_code';
		} else {
			if (strlen ($name) === 3) {
				$fieldname = 'state_3_code';
			} else {
				$fieldname = 'state_name';
			}
		}
		$q = 'SELECT `virtuemart_state_id` FROM `#__virtuemart_states` WHERE `' . $fieldname . '` = "' . $db->escape ($name) . '"';
		$db->setQuery ($q);
		$r = $db->loadResult ();
		return $r;
	}

	/*
	 * Returns the associative array for a given virtuemart_calc_id
	*
	* @author Valérie Isaksen
	* @access public
	* @param int $id virtuemart_calc_id
	* @return array Result row
	*/
	static public function getTaxByID ($id) {

		if (empty($id)) {
			return '';
		}

		$id = (int)$id;
		$db = JFactory::getDBO ();
		$q = 'SELECT  *   FROM `#__virtuemart_calcs` WHERE virtuemart_calc_id = ' . (int)$id;
		$db->setQuery ($q);
		return $db->loadAssoc ();

	}

	/**
	 * Return any field  from table '#__virtuemart_currencies'
	 *
	 * @author Valérie Isaksen
	 * @access public
	 * @param int $id Currency ID
	 * @param char $fld Field from table '#__virtuemart_currencies' to return: currency_name (default), currency_code_2, currency_code_3 etc.
	 * @return string Currency name or code
	 */
	static public function getCurrencyByID ($id, $fld = 'currency_name') {

		if (empty($id)) {
			return '';
		}
		static $currencyNameById = array();
		if(!isset($currencyNameById[$id][$fld])){
			$id = (int)$id;
			$db = JFactory::getDBO ();

			$q = 'SELECT ' . $db->escape ($fld) . ' AS fld FROM `#__virtuemart_currencies` WHERE virtuemart_currency_id = ' . (int)$id;
			$db->setQuery ($q);
			$currencyNameById[$id][$fld] = $db->loadResult ();
		}

		return $currencyNameById[$id][$fld];
	}

	/**
	 * Return the currencyID of a given Currency name
	 * This function becomes dangerous if there is a currency name with 3 letters
	 * @author Valerie Isaksen, Max Milbers
	 * @access public
	 * @param string $name Currency name
	 * @return int virtuemart_currency_id
	 */
	static public function getCurrencyIDByName ($name) {

		if (empty($name)) {
			return 0;
		}
		static $currencyIdByName = array();
		if(!isset($currencyIdByName[$name])){
			$db = JFactory::getDBO ();
			if (strlen ($name) === 2) {
				$fieldname = 'currency_code_2';
			} else {
				if (strlen ($name) === 3) {
					$fieldname = 'currency_code_3';
				} else {
					$fieldname = 'currency_name';
				}
			}
			$q = 'SELECT `virtuemart_currency_id` FROM `#__virtuemart_currencies` WHERE `' . $fieldname . '` = "' . ($name) . '"';
			$db->setQuery ($q);
			$currencyIdByName[$name] = $db->loadResult ();
		}

		return $currencyIdByName[$name];
	}

	/**
	 * Print a select-list with enumerated categories
	 *
	 * @author jseros
	 *
	 * @param boolean $onlyPublished Show only published categories?
	 * @param boolean $withParentId Keep in mind $parentId param?
	 * @param integer $parentId Show only its childs
	 * @param string $attribs HTML attributes for the list
	 * @return string <Select /> HTML
	 */
	static public function getEnumeratedCategories ($onlyPublished = TRUE, $withParentId = FALSE, $parentId = 0, $name = '', $attribs = '', $key = '', $text = '', $selected = NULL) {

		$categoryModel = VmModel::getModel ('category');

		$categories = $categoryModel->getCategories ($onlyPublished, $parentId);

		foreach ($categories as $index => $cat) {
			$cat->category_name = $cat->ordering . '. ' . $cat->category_name;
			$categories[$index] = $cat;
		}
		return JHtml::_ ('Select.genericlist', $categories, $name, $attribs, $key, $text, $selected, $name);
	}

	/**
	 * proxy function going to be
	 * @deprecated use shopFunctionsF::getOrderStatusName instead
	 * @param char $_code Order status code
	 * @return string The name of the order status
	 */
	static public function getOrderStatusName ($_code) {
		if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		return shopFunctionsF::getOrderStatusName($_code);
	}

	/**
	 * @author Valerie
	 * @deprecated use shopFunctionsF::InvoiceNumberReserved instead
	 */
	static function InvoiceNumberReserved ($invoice_number) {
		if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		return shopFunctionsF::InvoiceNumberReserved($invoice_number);
	}

	/**
	 * Creates an drop-down list with numbers from 1 to 31 or of the selected range,
	 * dont use within virtuemart. It is just meant for paymentmethods
	 *
	 * @deprecated
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	static function listDays ($list_name, $selected = FALSE, $start = NULL, $end = NULL) {

		$options = array();
		if (!$selected) {
			$selected = date ('d');
		}
		$start = $start ? $start : 1;
		$end = $end ? $end : $start + 30;
		$options[] = JHtml::_ ('select.option', 0, vmText::_ ('DAY'));
		for ($i = $start; $i <= $end; $i++) {
			$options[] = JHtml::_ ('select.option', $i, $i);
		}
		return JHtml::_ ('select.genericlist', $options, $list_name, '', 'value', 'text', $selected);
	}


	/**
	 * Creates a Drop-Down List for the 12 months in a year
	 *
	 * @param string $list_name The name for the select element
	 * @param string $selected_item The pre-selected value
	 *
	 */
	static function listMonths ($list_name, $selected = FALSE, $attr = '', $format='F') {

		$options = array();
		if (!$selected) {
			$selected = date ('m');
		}
		$months=array(
			"01"=>vmText::_ ('JANUARY'),
			"02"=>vmText::_ ('FEBRUARY'),
			"03"=>vmText::_ ('MARCH'),
			"04"=>vmText::_ ('APRIL'),
			"05"=>vmText::_ ('MAY'),
			"06"=>vmText::_ ('JUNE'),
			"07"=>vmText::_ ('JULY'),
			"08"=>vmText::_ ('AUGUST'),
			"09"=>vmText::_ ('SEPTEMBER'),
			"10"=>vmText::_ ('OCTOBER'),
			"11"=>vmText::_ ('NOVEMBER'),
			"12"=>vmText::_ ('DECEMBER')
		);

		$options[] = JHTML::_ ('select.option', 0, vmText::_ ('MONTH'));
		foreach($months as  $key => $value) {
			if ($format=='F') {
				$text=$value;
			} else {
				$text=$key;
			}
			$options[] = JHTML::_ ('select.option',$key, $text);
		}

		return JHTML::_ ('select.genericlist', $options, $list_name, $attr, 'value', 'text', $selected);

	}

	/**
	 * Creates an drop-down list with years of the selected range or of the next 11 years
	 *
	 * @param string $list_name The name of the select element
	 * @param string $selected_item The pre-selected value
	 */
	static function listYears ($list_name, $selected = FALSE, $start = NULL, $end = NULL, $attr = '', $format='Y') {

		$options = array();
		if (!$selected) {
			$selected = date ($format);
		}
		$start = $start ? $start : date ($format);
		$end = $end ? $end : $start + 11;
		$options[] = JHtml::_ ('select.option', 0, vmText::_ ('YEAR'));
		for ($i = $start; $i <= $end; $i++) {
			$options[] = JHtml::_ ('select.option', $i, $i);
		}
		return JHtml::_ ('select.genericlist', $options, $list_name, $attr, 'value', 'text', $selected);
	}


	/**
	 * Return $str with all but $display_length at the end as asterisks.
	 *
	 * @author gday
	 *
	 * @access public
	 * @param string $str The string to mask
	 * @param int $display_length The length at the end of the string that is NOT masked
	 * @param boolean $reversed When true, masks the end. Masks from the beginning at default
	 * @return string The string masked by asteriks
	 */
	public function asteriskPad ($str, $display_length, $reversed = FALSE) {

		$total_length = strlen ($str);

		if ($total_length > $display_length) {
			if (!$reversed) {
				for ($i = 0; $i < $total_length - $display_length; $i++) {
					$str[$i] = "*";
				}
			} else {
				for ($i = $total_length - 1; $i >= $total_length - $display_length; $i--) {
					$str[$i] = "*";
				}
			}
		}

		return ($str);
	}

	/**
	 * @deprecated
	 * @return array
	 */
	static function getValidProductFilterArray () {

		static $filterArray;

		if(!isset( $filterArray )) {

			VmModel::getModel( 'product' );
			$filterArray = VirtueMartModelProduct::getValidProductFilterArray();
		}

		return $filterArray;
	}
	
	/**
	 * Returns developer information for a plugin
	 * Returns a 2 link with background image, should look like a button to open contact page or manual
	 *
	 * @static
	 * @param $title string Title of the plugin
	 * @param $intro string Intro text
	 * @param $logolink url Url to logo images, use here the path and then as image names contact.png and manual.png
	 * @param $developer string Name of the developer/company
	 * @param $contactlink url Url to the contact form of the developer for support
	 * @param $manlink url URL to the manual for this specific plugin
	 * @return string
	 */
	static function display3rdInfo($title,$intro,$developer,$logolink,$contactlink,$manlink,$width='96px',$height='66px',$linesHeight='33px'){

		$html = $intro;

		$html .= self::displayLinkButton(vmText::sprintf('COM_VIRTUEMART_THRD_PARTY_CONTACT',$developer),$contactlink, $logolink.'/contact.png',$width,$height,$linesHeight);
		$html .='<br />';
		$html .= self::displayLinkButton(vmText::sprintf('COM_VIRTUEMART_THRD_PARTY_MANUAL',$title),$manlink, $logolink.'/manual.png',$width,$height,$linesHeight);

		return $html;
	}


	static function displayLinkButton($title, $link, $bgrndImage,$width,$height,$linesHeight,$additionalStyles=''){

		$html = '<div style="line-height:'.$linesHeight.';background-image:url('.$bgrndImage.');width:'.$width.';height:'.$height.';'.$additionalStyles.'">'
				.'<a  title="'.$title.'" href="'.$link.'" target="_blank" >'.$title .'</a></div>';

		return $html;
	}

	/**
	 * @deprecated
	 * @param int $length
	 * @return string
	 */
	static function generateRandomString($length = 10) {
		return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	}

	static function checkSafePath($sPath=0){
		static $safePath = null;
		if(isset($safePath)) {
			return $safePath;
		}

		if($sPath==0) {
			$safePath = VmConfig::get('forSale_path',0);
		} else {
			$safePath = $sPath;
		}

		if(VmConfig::$installed==false or vRequest::getInt('nosafepathcheck',false) or vRequest::getWord('view')== 'updatesmigration') {
			return $safePath;
		}

		$warn = false;
		$uri = JFactory::getURI();


		if(empty($safePath)){
			$warn = 'EMPTY';
		} else {
			if(!class_exists('JFolder')) require_once(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
			$exists = JFolder::exists($safePath);
			if(!$exists){
				$warn = 'WRONG';
			}
		}

		if($warn){
			vmLanguage::loadJLang('com_virtuemart');
			$safePath = false;

			vmLanguage::loadJLang('com_virtuemart_config');
			$configlink = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=updatesmigration&show_spwizard=1';

			$t = vmText::sprintf('COM_VM_SAFEPATH_WARN_'.$warn, vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH'), vmText::sprintf('COM_VM_SAFEPATH_WIZARD',$configlink));
			VmError($t);
		} else {
			if(!is_writable( $safePath )){
				vmLanguage::loadJLang('com_virtuemart');
				vmLanguage::loadJLang('com_virtuemart_config');
				vmdebug('checkSafePath $safePath not writeable '.$safePath);
				VmError(vmText::sprintf('COM_VIRTUEMART_WARN_SAFE_PATH_INV_NOT_WRITEABLE',vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH'),$safePath)
				,vmText::sprintf('COM_VIRTUEMART_WARN_SAFE_PATH_INV_NOT_WRITEABLE','',''));
			} else {
				if(!is_writable(self::getInvoicePath($safePath) )){
					vmLanguage::loadJLang('com_virtuemart');
					vmLanguage::loadJLang('com_virtuemart_config');
					vmdebug('checkSafePath $safePath/invoice not writeable '.addslashes($safePath));
					VmError(vmText::sprintf('COM_VIRTUEMART_WARN_SAFE_PATH_INV_NOT_WRITEABLE',vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH'),htmlspecialchars($safePath))
					,vmText::sprintf('COM_VIRTUEMART_WARN_SAFE_PATH_INV_NOT_WRITEABLE','',''));
				}
			}

		}

		return $safePath;
	}
	/*
	 * get The invoice Folder Name
	 * @return the invoice folder name
	 */
	static function getInvoiceFolderName() {
		if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		return ShopFunctionsF::getInvoiceFolderName();
	}
	/*
	 * get The invoice path
	 * @param $safePath the safepath from the config
	 * @return the path where the invoice are stored
	 */
	static function getInvoicePath($safePath=null) {
		if(!isset($safePath)){
			$safePath = self::checkSafePath();
		}
		return  $safePath.self::getInvoiceFolderName() ;
	}

	static function getUpperJoomlaPath(){
		$sub = JUri::root(true);
		if(!empty($sub)){
			$sub = trim($sub,'/');
			$subIndex= strrpos(VMPATH_ROOT,$sub);
			$p = substr(VMPATH_ROOT,0,$subIndex-1);

		} else {
			$p = VMPATH_ROOT;
		}
		$lastIndex= strrpos($p,DS);
		return substr(VMPATH_ROOT,0,$lastIndex);
	}

	/*
	 * Returns the suggested safe Path, used to store the invoices
	 * @static
	 * @return string: suggested safe path
	 */
	static public function getSuggestedSafePath() {
		return self::getUpperJoomlaPath().DS.'vmfiles'.DS;
	}

	/*
	 * @author Valerie Isaksen
	 */
	static public function renderProductShopperList ($productShoppers) {

		$html = '';
		$i=0;
		if(empty($productShoppers)) return '';
		foreach ($productShoppers as $email => $productShopper) {
			$html .= '<tr  class="customer row'.$i.'" data-cid="' . $productShopper['email'] . '">
			<td rowspan ="'.$productShopper['nb_orders'] .'">' . $productShopper['name'] . '</td>
			<td rowspan ="'.$productShopper['nb_orders'] .'><a class="mailto" href="' . $productShopper['mail_to'] . '"><span class="mail">' . $productShopper['email'] . '</span></a></td>
			<td rowspan ="'.$productShopper['nb_orders'] .'class="shopper_phone">' . $productShopper['phone'] . '</td>';
            $first=TRUE;
			foreach ($productShopper['order_info'] as $order_info) {
				if (!$first)
				$html .= '<tr class="row'.$i.'">';
			$html .= '<td class="quantity">';
			$html .= $order_info['quantity'];
			$html .= '</td>';
			$html .= '<td class="order_status">';
			$html .= vmText::_($order_info['order_item_status_name']);
			$html .= '</td>
			<td class="order_number">';
				$uri = JFactory::getURI();
				$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $order_info['order_id'];
				$html .= JHtml::_ ('link', $link, $order_info['order_number'], array('title' => vmText::_ ('COM_VIRTUEMART_ORDER_EDIT_ORDER_NUMBER') . ' ' . $order_info['order_number']));
			$first=FALSE;
			$html .= '</td>';
			$html .= '<td class="order_date">';
			$html .= vmJsApi::date ($order_info['order_date'], 'LC4', TRUE);
			$html .= '</td>
			</tr>
				';
			}
			$i = 1 - $i;
		}
		if (empty($html)) {
			$html = '
				<tr class="customer">
					<td colspan="4">
						' . vmText::_ ('COM_VIRTUEMART_NO_SEARCH_RESULT') . '
					</td>
				</tr>
				';
		}

		return $html;
	}

	static public function renderMetaEdit($obj){

		$options = array(
			''	=>	vmText::_('JGLOBAL_INDEX_FOLLOW'),
			'noindex, follow'	=>	vmText::_('JGLOBAL_NOINDEX_FOLLOW'),
			'index, nofollow'	=>	vmText::_('JGLOBAL_INDEX_NOFOLLOW'),
			'noindex, nofollow'	=>	vmText::_('JGLOBAL_NOINDEX_NOFOLLOW'),
			'noodp, noydir'	=>	vmText::_('COM_VIRTUEMART_NOODP_NOYDIR'),
			'noodp, noydir, nofollow'	=>	vmText::_('COM_VIRTUEMART_NOODP_NOYDIR_NOFOLLOW'),
		);
		$html = '<table>
					'.VmHTML::row('input','COM_VIRTUEMART_CUSTOM_PAGE_TITLE','customtitle',$obj->customtitle).'
					'.VmHTML::row('textarea','COM_VIRTUEMART_METAKEY','metakey',$obj->metakey,'class="inputbox"',80).'
					'.VmHTML::row('textarea','COM_VIRTUEMART_METADESC','metadesc',$obj->metadesc,'class="inputbox"',80).'
					'.VmHtml::row('selectList','COM_VIRTUEMART_METAROBOTS','metarobot',$obj->metarobot,$options).'
					'.VmHTML::row('input','COM_VIRTUEMART_METAAUTHOR','metaauthor',$obj->metaauthor).'
				</table>';
		return $html;
	}

	static function getAvailabilityIconUrl ($vmtemplate) {

		if(!empty($vmtemplate) and is_array( $vmtemplate )) $vmtemplate = $vmtemplate['template'];

		if(is_Dir( VMPATH_ROOT.DS.'templates'.DS.$vmtemplate.DS.'images'.DS.'availability'.DS )) {
			return '/templates/'.$vmtemplate.'/images/availability/';
		} else if(is_Dir(VMPATH_ROOT.'/'.VmConfig::get('assets_general_path').'images/availability/')){
			return '/'.VmConfig::get('assets_general_path').'images/availability/';
		}else {
			return '';
		}
	}

	/**
	 * get the Client IP, even if a reverse proxy or similar is used
	 * @author Valerie Isaksen, Max Milbers
	 * @return bool|string
	 */
	static function getClientIP() {
		$ip_keys = array('X_FORWARDED_FOR', 'X-Forwarded-Proto','REMOTE_ADDR');
		$extra = VmConfig::get('revproxvar','');
		if(!empty($extra)){
			$extra = explode(',',$extra);
			$ip_keys = array_merge($extra, $ip_keys);
		}
		foreach ($ip_keys as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					// trim for safety measures
					$ip = trim($ip);
					vmdebug('getClientIP',$ip);
					// attempt to validate IP
					if (self::validateIp($ip)) {
						return $ip;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Ensures an ip address is both a valid IP and does not fall within
	 * a private network range.
	 */
	static function validateIp($ip) {
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
			return false;
		}
		return true;
	}
}

//pure php no tag
