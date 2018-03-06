<?php
/**
*
* List/add/edit/remove Userfields
*
* @package	VirtueMart
* @subpackage Userfields
* @author Oscar van Eijk
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 9463 2017-03-06 10:45:33Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for maintaining the list of order types
 *
 * @package	VirtueMart
 * @subpackage Userfields
 * @author Oscar van Eijk
 */
class VirtuemartViewUserfields extends VmViewAdmin {

	function display($tpl = null) {

		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);
		$option = vRequest::getCmd( 'option');
		$mainframe = JFactory::getApplication() ;

		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		$layoutName = vRequest::getCmd('layout', 'default');
		$model = VmModel::getModel();

		// The list of fields which can't be toggled
		//$lists['coreFields']= array( 'name','username', 'email', 'password', 'password2' );
		$lists['coreFields'] = $model->getCoreFields();

		if ($layoutName == 'edit') {
			$this->editor = JFactory::getEditor();

			$this->userField = $model->getUserfield();
			//vmdebug('user plugin $this->userField',$this->userField);
            $this->SetViewTitle('USERFIELD',$this->userField->name );
            $this->assignRef('viewName',$viewName);
			$userFieldPlugin = '';

			if (!class_exists('ShopFunctions'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');

			$this->ordering = ShopFunctions::renderOrderingList('userfields','name',$this->userField->ordering);

			if ($this->userField->virtuemart_userfield_id < 1) { // Insert new userfield

				$userFieldValues = array();
				$attribs = '';
				$lists['type'] = JHtml::_('select.genericlist', $this->_getTypes(), 'type', $attribs, 'type', 'text', $this->userField->type);
			} else { // Update existing userfield
				// Ordering dropdown

				$userFieldValues = $model->getUserfieldValues();

				$lists['type'] = $this->_getTypes($this->userField->type)
					. '<input id="type" type="hidden" name="type" value="'.$this->userField->type.'" />';
				if (strpos($this->userField->type, 'plugin') !==false) {

					$userFieldPlugin = self::renderUserfieldPlugin(substr($this->userField->type, 6),$this->userField);
				}

			}
			$this->assignRef('userFieldPlugin',	$userFieldPlugin);
			JToolBarHelper::divider();
			JToolBarHelper::save();
			JToolBarHelper::apply();
			JToolBarHelper::cancel();

			$notoggle = ''; // (in_array($this->userField->name, $lists['coreFields']) ? 'class="readonly"' : '');

			// Vendor selection
			$this->lists['vendors'] = '';
			if($this->showVendors()){
				$lists['vendors']= ShopFunctions::renderVendorList($this->userField->virtuemart_vendor_id);
			}

			// Shopper groups for EU VAT Id
			$shoppergroup_model = VmModel::getModel('shoppergroup');
			$shoppergroup_list = $shoppergroup_model->getShopperGroups(true);
			array_unshift($shoppergroup_list,'0');
			$lists['shoppergroups'] = JHtml::_('select.genericlist', $shoppergroup_list, 'virtuemart_shoppergroup_id', '', 'virtuemart_shoppergroup_id', 'shopper_group_name', $this->userField->get('virtuemart_shoppergroup_id'));

			// Minimum age select
			$ages = array();
			for ($i = 13; $i <= 25; $i++) {
				$ages[] = array('key' => $i, 'value' => $i.' '.vmText::_('COM_VIRTUEMART_YEAR_S'));
			}
			$lists['minimum_age'] = JHtml::_('select.genericlist', $ages, 'minimum_age', '', 'key', 'value', $this->userField->get('minimum_age', 18));

			// Web address types
			$webaddress_types = array(
				 array('key' => 0, 'value' => vmText::_('COM_VIRTUEMART_USERFIELDS_URL_ONLY'))
				,array('key' => 2, 'value' => vmText::_('COM_VIRTUEMART_USERFIELDS_HYPERTEXT_URL'))
			);
			$lists['webaddresstypes'] = JHtml::_('select.genericlist', $webaddress_types, 'webaddresstype', '', 'key', 'value', $this->userField->get('webaddresstype'));

			// Userfield values
			if (($n = count($userFieldValues)) < 1) {
				$lists['userfield_values'] =
					 '<tr>'
					.'<td><input type="text" value="" name="vValues[0]" /></td>'
					.'<td><input type="text" size="50" value="" name="vNames[0]" /></td>'
					.'</tr>';
				$i = 1;
			} else {
				$lists['userfield_values'] = '';
				$lang =JFactory::getLanguage();
				for ($i = 0; $i < $n; $i++) {
					$translate= $lang->hasKey($userFieldValues[$i]->fieldtitle) ? " (".vmText::_($userFieldValues[$i]->fieldtitle).")" : "";
					$lists['userfield_values'] .=
						 '<tr>'
						 .'<td><input type="text" value="'.$userFieldValues[$i]->fieldvalue.'" name="vValues['.$i.']" /></td>'
						.'<td><input type="text" size="50" value="'.$userFieldValues[$i]->fieldtitle.'" name="vNames['.$i.']"   />'.$translate.'<input type="button" class="button deleteRow" value=" - " /></td>'
						.'</tr>';
				}
			}
			$this->valueCount = --$i;

			$userFieldTable = $model->getTable();
			$this->existingFields =  '"'.implode('","',$userFieldTable->showFullColumns(0,'Field')).'"';

			// Toggles
			$lists['required']     =  VmHTML::row('booleanlist','COM_VIRTUEMART_FIELDMANAGER_REQUIRED','required',$this->userField->required,$notoggle);
			$lists['published']    =  VmHTML::row('booleanlist','COM_VIRTUEMART_PUBLISHED','published',$this->userField->published,$notoggle);
			$lists['cart'] 		=  VmHTML::row('booleanlist','COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_CART','cart',$this->userField->cart,$notoggle);
			$lists['shipment']     =  VmHTML::row('booleanlist','COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_SHIPPING','shipment',$this->userField->shipment,$notoggle);
			$lists['account']      =  VmHTML::row('booleanlist','COM_VIRTUEMART_FIELDMANAGER_SHOW_ON_ACCOUNT','account',$this->userField->account,$notoggle);
			$lists['readonly']     =  VmHTML::row('booleanlist','COM_VIRTUEMART_USERFIELDS_READONLY','readonly',$this->userField->readonly,$notoggle);

			$this->assignRef('lists', $lists);
			$this->assignRef('userFieldValues', $userFieldValues);

		} else {
			JToolBarHelper::title( vmText::_('COM_VIRTUEMART_MANAGE_USER_FIELDS'),'vm_user_48 head');
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
			JToolBarHelper::divider();
			JToolBarHelper::custom('toggle.required.1', 'publish','','COM_VIRTUEMART_FIELDMANAGER_REQUIRE');
			JToolBarHelper::custom('toggle.required.0', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_UNREQUIRE');
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::divider();
			$barText = vmText::_('COM_VIRTUEMART_FIELDMANAGER_SHOW_HIDE');

			$bar= JToolBar::getInstance( 'toolbar' );
			$bar->appendButton( 'Separator', '"><span class="bartext">'.$barText.'</span><hr style="display: none;' );
//$bar->appendButton( 'publish', 'upload', $alt, '', 550, 400 );
			JToolBarHelper::custom('toggle.registration.1', 'publish','','COM_VIRTUEMART_FIELDMANAGER_SHOW_REGISTRATION');
			JToolBarHelper::custom('toggle.registration.0', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_HIDE_REGISTRATION');
			JToolBarHelper::custom('toggle.shipment.1', 'publish','','COM_VIRTUEMART_FIELDMANAGER_SHOW_SHIPPING');
			JToolBarHelper::custom('toggle.shipment.0', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_HIDE_SHIPPING');
			JToolBarHelper::custom('toggle.account.1', 'publish','','COM_VIRTUEMART_FIELDMANAGER_SHOW_ACCOUNT');
			JToolBarHelper::custom('toggle.account.0', 'unpublish','','COM_VIRTUEMART_FIELDMANAGER_HIDE_ACCOUNT');
			JToolBarHelper::divider();
			JToolBarHelper::deleteList();

			$this->addStandardDefaultViewLists($model,'ordering','ASC');

			$this->userfieldsList = $model->getUserfieldsList();
			$this->pagination = $model->getPagination();

			// search filter
			$search = $mainframe->getUserStateFromRequest( $option.'search', 'search', '', 'string');
			$search = JString::strtolower( $search );
			$this->lists['search']= $search;
		}
		$this->lists['coreFields'] = $lists['coreFields'];
		parent::display($tpl);
	}

	/**
	 * Create an array with userfield types and the visible text in the format expected by the Joomla select class
	 *
	 * @param string $value If not null, the type of which the text should be returned
	 * @return mixed array or string
	 */
	function _getTypes ($value = null){
		$types = array(
			 array('type' => 'text'             , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_TEXTFIELD'))
			,array('type' => 'checkbox'         , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_CHECKBOX_SINGLE'))
			,array('type' => 'multicheckbox'    , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_CHECKBOX_MULTIPLE'))
			,array('type' => 'date'             , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_DATE'))
			,array('type' => 'age_verification' , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_AGEVERIFICATION'))
			,array('type' => 'select'           , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_DROPDOWN_SINGLE'))
			,array('type' => 'multiselect'      , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_DROPDOWN_MULTIPLE'))
			,array('type' => 'emailaddress'     , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_EMAIL'))
 			,array('type' => 'custom'          , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_CUSTOM'))
			,array('type' => 'editorta'         , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_EDITORAREA'))
			,array('type' => 'textarea'         , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_TEXTAREA'))
			,array('type' => 'radio'            , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_RADIOBUTTON'))
			,array('type' => 'webaddress'       , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_WEBADDRESS'))
			,array('type' => 'delimiter'        , 'text' => vmText::_('COM_VIRTUEMART_FIELDS_DELIMITER'))

		);
		$this->renderInstalledUserfieldPlugins($types);

		if ($value === null) {
			return $types;
		} else {
			foreach ($types as $type) {
				if ($type['type'] == $value) {
					return $type['text'];
				}
				return $value;
			}
		}
	}

	function renderUserfieldPlugin(){

		if(!class_exists('vmUserfieldPlugin')) require(VMPATH_PLUGINLIBS.DS.'vmuserfieldtypeplugin.php');

		vmLanguage::loadJLang('plg_vmpsplugin', false);
		JForm::addFieldPath(VMPATH_ADMIN . DS . 'fields');
		//$selected = $this->userField->userfield_jplugin_id;
		//vmdebug('renderUserfieldPlugin $this->userField->element',$this->userField->type,$this->userField->element);
		$this->userField->element = substr($this->userField->type, 6);

		$path = VMPATH_ROOT .DS. 'plugins' .DS. 'vmuserfield' . DS . $this->userField->element . DS . $this->userField->element . '.xml';
		// Get the payment XML.
		$formFile	= vRequest::filterPath( $path );
		if (file_exists($formFile)){

			$this->userField->form = JForm::getInstance($this->userField->element, $formFile, array(),false, '//vmconfig | //config[not(//vmconfig)]');
			$this->userField->params = new stdClass();
			$varsToPush = vmPlugin::getVarsToPushFromForm($this->userField->form);
			VmTable::bindParameterableToSubField($this->userField,$varsToPush);
			$this->userField->form->bind($this->userField->getProperties());
		} else {
			$this->userField->form = false;
			vmdebug('renderUserfieldPlugin could not find xml for '.$this->userField->type.' at '.$path);
		}

		if ($this->userField->form) {
			$form = $this->userField->form;
			ob_start();
			include(VMPATH_ADMIN.DS.'fields'.DS.'formrenderer.php');
			$body = ob_get_contents();
			ob_end_clean();
			return $body;
		}
		return;
	}

	function renderInstalledUserfieldPlugins(&$plugins){

		$table = '#__extensions';
		$ext_id = 'extension_id';
		$enable = 'enabled';

		$db = JFactory::getDBO();
 		$q = 'SELECT * FROM `'.$table.'` WHERE `folder` = "vmuserfield" and state="0"';
		$db->setQuery($q);
		$userfieldplugins = $db->loadAssocList($ext_id);
		if(empty($userfieldplugins)){
			return;
		}

		foreach($userfieldplugins as $userfieldplugin){
            $plugins[] = array('type' => 'plugin'.$userfieldplugin['element'], 'text' => $userfieldplugin['name']);
		}

		return;
	}
}

//No Closing Tag
