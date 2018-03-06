<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author  Patrick Kohl
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 3006 2011-04-08 13:16:08Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * Json View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author  Patrick Kohl
 */
class VirtuemartViewCustom extends JViewLegacy {

	/* json object */
	private $json = null;

	function display($tpl = null) {

		$db = JFactory::getDBO();
		if ( $virtuemart_media_id = vRequest::getInt('virtuemart_media_id') ) {
			//$db = JFactory::getDBO();
			$query='SELECT `file_url`,`file_title` FROM `#__virtuemart_medias` where `virtuemart_media_id`='.$virtuemart_media_id;
			$db->setQuery( $query );
			$json = $db->loadObject();
			if (isset($json->file_url)) {
				$json->file_url = JURI::root().$json->file_url;
				$json->msg =  'OK';
				echo vmJsApi::safe_json_encode($json);
			} else {
				$json->msg =  '<b>'.vmText::_('COM_VIRTUEMART_NO_IMAGE_SET').'</b>';
				echo vmJsApi::safe_json_encode($json);
			}
		}
		elseif ( $custom_jplugin_id = vRequest::getInt('custom_jplugin_id') ) {

			$table = '#__extensions';
			$ext_id = 'extension_id';

			$q = 'SELECT `params`,`element` FROM `' . $table . '` WHERE `' . $ext_id . '` = "'.$custom_jplugin_id.'"';
			$db ->setQuery($q);
			$this->jCustom = $db ->loadObject();

			$customModel = VmModel::getModel('custom');
			$this->custom = $customModel -> getCustom();

			// Get the payment XML.
			$formFile	= vRequest::filterPath( VMPATH_ROOT .DS. 'plugins' .DS. 'vmcustom' .DS . $this->jCustom->element . DS . $this->jCustom->element . '.xml');
			if (file_exists($formFile)){
				vmLanguage::loadJLang('plg_vmpsplugin', false);
				if (!class_exists('vmPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmplugin.php');
				$filename = 'plg_vmcustom_' .  $this->jCustom->element;
				vmPlugin::loadJLang($filename,'vmcustom',$this->jCustom->element);

				$this->custom = VmModel::getModel('custom')->getCustom();
				$varsToPush = vmPlugin::getVarsToPushByXML($formFile,'customForm');
				$this->custom->form = JForm::getInstance($this->jCustom->element, $formFile, array(),false, '//vmconfig | //config[not(//vmconfig)]');
				$this->custom->params = new stdClass();

				foreach($varsToPush as $k => $field){
					if(strpos($k,'_')!=0){
						$this->custom->params->$k = $field[0];
					}
				}
				$this->custom->form->bind($this->custom->getProperties());
				$form = $this->custom->form;
				include(VMPATH_ADMIN.DS.'fields'.DS.'formrenderer.php');
				echo '<input type="hidden" value="'.$this->jCustom->element.'" name="custom_value">';
			} else {
				$this->custom->form = null;
				//VmConfig::$echoDebug = 1;
				vmdebug ('File does not exist '.$formFile);
			}
		}
		jExit();
	}

}
// pure php no closing tag
