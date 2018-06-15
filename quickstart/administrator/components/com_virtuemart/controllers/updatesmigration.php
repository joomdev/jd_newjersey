<?php

/**
 *
 * updatesMigration controller
 *
 * @package	VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers, RickG
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: updatesmigration.php 9806 2018-03-28 15:38:57Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmController'))
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcontroller.php');

/**
 * updatesMigration Controller
 *
 * @package    VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers
 */
class VirtuemartControllerUpdatesMigration extends VmController{

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct(){
		parent::__construct();

	}

	/**
	 * Call at begin of every task to check if the permission is high enough.
	 * Atm the standard is at least vm admin
	 * @author Max Milbers
	 */
	private function checkPermissionForTools(){
		vRequest::vmCheckToken();
		//Hardcore Block, we may do that better later
		if(!vmAccess::manager('core') ){
			$msg = 'Forget IT';
			$this->setRedirect('index.php?option=com_virtuemart', $msg);
		}

		return true;
	}

	function setsafepathupper(){

		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');
		$model->setSafePathCreateFolders();
		$this->setRedirect($this->redirectPath);
	}

	function setsafepathcom(){

		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');
		$model->setSafePathCreateFolders(vRequest::getVar('safepathToken'));
		$this->setRedirect($this->redirectPath);
	}

	/**
	 * Install sample data into the database
	 *
	 * @author Max Milbers
	 */
	function installSampleData(){

		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');

		$model->installSampleData();

		$this->setRedirect($this->redirectPath);
	}

	/**
	 * Sets the storeowner to the currently logged in user
	 * He needs admin rights
	 *
	 * @author Max Milbers
	 */
	function setStoreOwner(){

		$this->checkPermissionForTools();

		$model = $this->getModel('updatesMigration');

		$storeOwnerId =vRequest::getInt('storeOwnerId');
		$msg = $model->setStoreOwner($storeOwnerId);

		$this->setRedirect($this->redirectPath, $msg);
	}

	/**
	 * Install sample data into the database
	 *
	 * @author RickG
	 * @author Max Milbers
	 */
	function restoreSystemDefaults(){

		$this->checkPermissionForTools();

		if(VmConfig::get('dangeroustools', false)){

			$model = $this->getModel('updatesMigration');
			$model->restoreSystemDefaults();

			$msg = vmText::_('COM_VIRTUEMART_SYSTEM_DEFAULTS_RESTORED');
			$msg .= ' User id of the main vendor is ' . $model->setStoreOwner();
			$this->setDangerousToolsOff();
		} else {
			$msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath, $msg);
	}

	public function fixCustomsParams(){
		$this->checkPermissionForTools();
		$q = 'SELECT `virtuemart_customfield_id` FROM `#__virtuemart_product_customfields` LEFT JOIN `#__virtuemart_customs` USING (`virtuemart_custom_id`) ';
		$q = 'SELECT `virtuemart_customfield_id`,`customfield_params` FROM `#__virtuemart_product_customfields` ';
		$q .= ' WHERE `customfield_params`!="" ';
		$db = JFactory::getDbo();
		$db->setQuery($q);

		$rows = $db->loadAssocList();

		foreach($rows as $fields){
			$store = '';
			if(empty($fields['customfield_params'])) continue;

			$json = @json_decode($fields['customfield_params']);

			if($json){

				$vars = get_object_vars($json);
				foreach($vars as $key=>$value){
					if(!empty($key)){
						$store .= $key . '=' . vmJsApi::safe_json_encode($value) . '|';
					}
				}

				if(!empty($store)){
					$q = 'UPDATE `#__virtuemart_product_customfields` SET `customfield_params` = "'.$db->escape($store).'" WHERE `virtuemart_customfield_id` = "'.$fields['virtuemart_customfield_id'].'" ';
					$db->setQuery($q);
					$db->execute();
				}

			}

		}
		$msg = 'Executed';
		$this->setredirect($this->redirectPath, $msg);
	}

	/**
	 * Quite unsophisticated, but it does it jobs if there are not too much products/customfields.
	 *
	 */
	public function deleteInheritedCustoms () {

		$msg = '';
		$this->checkPermissionForTools();
		if(VmConfig::get('dangeroustools', false)){

			$db = JFactory::getDbo();

			/*$q = 'SELECT customfield_id ';
			$q .= 'FROM `#__virtuemart_product_customfields` as pc WHERE
					LEFT JOIN `#__virtuemart_products` as c using (`virtuemart_product_id`) ';
			$q .= 'WHERE c.product_parent_id =';*/
			$q = ' SELECT `product_parent_id` FROM `#__virtuemart_products`
					INNER JOIN `#__virtuemart_product_customfields` as pc using (`virtuemart_product_id`)
					WHERE `product_parent_id` != "0" GROUP BY `product_parent_id` ';
			$db->setQuery($q);
			$childs = $db->loadColumn();

			$toDelete = array();
			foreach($childs as $child_id){

				$q = ' SELECT pc.virtuemart_customfield_id,pc.virtuemart_custom_id,pc.customfield_value,pc.customfield_price,pc.customfield_params
					FROM `#__virtuemart_product_customfields` as pc
					LEFT JOIN `#__virtuemart_products` as c using (`virtuemart_product_id`) ';
				$q .= ' WHERE c.virtuemart_product_id = "'.$child_id.'" ';
				$db->setQuery($q);
				$pcfs = $db->loadAssocList();
				vmdebug('load PCFS '.$q);
				if($pcfs){
					vmdebug('There are PCFS');
					$q = ' SELECT pc.virtuemart_customfield_id,pc.virtuemart_custom_id,pc.customfield_value,pc.customfield_price,pc.customfield_params
					FROM `#__virtuemart_product_customfields` as pc
					LEFT JOIN `#__virtuemart_products` as c using (`virtuemart_product_id`) ';
					$q .= ' WHERE c.product_parent_id = "'.$child_id.'" ';

					$db->setQuery($q);
					$cfs = $db->loadAssocList();

					foreach($cfs as $cf){
						foreach($pcfs as $pcf){
							if($cf['virtuemart_custom_id'] == $pcf['virtuemart_custom_id']){
								vmdebug('virtuemart_custom_id same');
								if($cf['customfield_value'] == $pcf['customfield_value'] and
								$cf['customfield_price'] == $pcf['customfield_price'] and
								$cf['customfield_params'] == $pcf['customfield_params']){
									$toDelete[] = $cf['virtuemart_customfield_id'];
								}
							}
						}
					}
				}

			}

			if(count($toDelete)>0){
				$toDelete = array_unique($toDelete,SORT_NUMERIC);
				$toDeleteString = implode(',',$toDelete);
				$q = 'DELETE FROM `#__virtuemart_product_customfields` WHERE virtuemart_customfield_id IN ('.$toDeleteString.') ';
				$db->setQuery($q);
				$db->execute();
			}

			/*$q = 'SELECT `virtuemart_customfield_id`
					FROM `#__virtuemart_product_customfields` as pc
					LEFT JOIN `#__virtuemart_products` as c using (`virtuemart_product_id`)';
			$q .= ' WHERE c.product_parent_id != "0" AND ';*/
		} else {
			$msg = $this->_getMsgDangerousTools();
		}
		$this->setredirect($this->redirectPath, $msg);
	}

	/**
	 * Remove all the Virtuemart tables from the database.
	 *
	 * @author Max Milbers
	 */
	function deleteVmTables(){

		$this->checkPermissionForTools();

		$msg = vmText::_('COM_VIRTUEMART_SYSTEM_VMTABLES_DELETED');
		if(VmConfig::get('dangeroustools', false)){
			$model = $this->getModel('updatesMigration');

			if(!$model->removeAllVMTables()){
				$this->setDangerousToolsOff();
				$this->setRedirect('index.php?option=com_virtuemart');
			}
		}else {
			$msg = $this->_getMsgDangerousTools();
		}
		$this->setRedirect('index.php?option=com_installer', $msg);
	}

	/**
	 * Deletes all dynamical created data and leaves a "fresh" installation without sampledata
	 * OUTDATED
	 * @author Max Milbers
	 *
	 */
	function deleteVmData(){

		$this->checkPermissionForTools();

		$msg = vmText::_('COM_VIRTUEMART_SYSTEM_VMDATA_DELETED');
		if(VmConfig::get('dangeroustools', false)){
			$model = $this->getModel('updatesMigration');

			if(!$model->removeAllVMData()){
				$this->setDangerousToolsOff();
				$this->setRedirect('index.php?option=com_virtuemart');
			}
		}else {
			$msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath, $msg);
	}

	function refreshCompleteInstallAndSample(){

		$this->refreshCompleteInstall(true);
	}


	function refreshCompleteInstall($sample=false){

		$this->checkPermissionForTools();

		if(VmConfig::get('dangeroustools', true)){

			$model = $this->getModel('updatesMigration');

			$safePath = VmConfig::get('forSale_path');

			$model->restoreSystemTablesCompletly();
			$sid = $model->setStoreOwner();

			$sampletxt = '';
			if($sample){

				$model->installSampleData($sid);

				if(!class_exists('VmConfig')) require_once(VMPATH_ADMIN .'/models/config.php');
				VirtueMartModelConfig::installVMconfigTable();

				if(!class_exists('VirtueMartModelConfig')) require(VMPATH_ADMIN .'/models/config.php');
				$res  = VirtueMartModelConfig::checkConfigTableExists();

				if($res) {
					$config = VmConfig::loadConfig(true);
					$config->set('forSale_path', $safePath);

					$data['virtuemart_config_id'] = 1;
					$data['config'] = $config->toString();

					$confTable = $model->getTable( 'configs' );
					$confTable->bindChecknStore( $data );

					VmConfig::loadConfig( true );
				}

				$sampletxt = ' and sampledata installed';
			}

			VirtueMartModelConfig::installLanguageTables();

			$cache = VmConfig::getCache();
			//$cache = JFactory::getCache();
			$cache->clean('com_virtuemart_cats');
			$cache->clean('com_virtuemart_cat_childs');
			$cache->clean('mod_virtuemart_product');
			$cache->clean('mod_virtuemart_category');
			$cache->clean('com_virtuemart_rss');
			$cache->clean('com_virtuemart_cat_manus');
			$cache->clean('com_virtuemart_revenue');
			$cache->clean('convertECB');
			$cache->clean('_virtuemart');
			$cache->clean('com_plugins');
			$cache->clean('_system');
			$cache->clean('page');

			$msg = '';
			if(empty($errors)){
				$msg = 'System succesfull restored'.$sampletxt.', user id of the mainvendor is ' . $sid;
			} else {
				foreach($errors as $error){
					$msg .= ( $error) . '<br />';
				}
			}

			$this->setDangerousToolsOff();
		}else {
			$msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect($this->redirectPath, $msg);
	}

	function installCompleteSamples(){
		$this->installComplete(true);
	}

	function installComplete($sample=false){

		$this->checkPermissionForTools();

		if(VmConfig::get('dangeroustools', true)){

			if(!class_exists('com_virtuemartInstallerScript')) require(VMPATH_ADMIN . DS . 'install' . DS . 'script.virtuemart.php');
			$updater = new com_virtuemartInstallerScript();
			$updater->install(true);

			$model = $this->getModel('updatesMigration');
			$sid = $model->setStoreOwner();

			$msg = 'System and sampledata succesfull installed, user id of the mainvendor is ' . $sid;

			if(!class_exists('com_virtuemart_allinoneInstallerScript')) require(VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart_allinone' . DS . 'script.vmallinone.php');
			$updater = new com_virtuemart_allinoneInstallerScript(false);
			$updater->vmInstall(true);

			if(!class_exists('VirtueMartModelConfig')) require_once(VMPATH_ADMIN .'/models/config.php');
			VirtueMartModelConfig::installVMconfigTable();

			$this->addAssetEntries();
			$this->addMenuEntries($sample);
			if($sample) {
				$model->installSampleData($sid);
			}

			//Now lets set some joomla variables
			//Caching should be enabled, set to files and for 15 minutes
			if(JVM_VERSION>2){
				if (!class_exists( 'ConfigModelCms' )) require(VMPATH_ROOT.DS.'components'.DS.'com_config'.DS.'model'.DS.'cms.php');
				if (!class_exists( 'ConfigModelForm' )) require(VMPATH_ROOT.DS.'components'.DS.'com_config'.DS.'model'.DS.'form.php');
				if (!class_exists( 'ConfigModelApplication' )) require(VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_config'.DS.'model'.DS.'application.php');
			} else {
				if (!class_exists( 'ConfigModelApplication' )) require(VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_config'.DS.'models'.DS.'application.php');
			}

			$jConfModel = new ConfigModelApplication();
			$jConfig = $jConfModel->getData();

			$jConfig['caching'] = 0;
			$jConfig['lifetime'] = 60;
			$jConfig['list_limit'] = 25;
			$jConfig['MetaDesc'] = 'VirtueMart works with Joomla! - the dynamic portal engine and content management system';
			$jConfig['MetaKeys'] = 'virtuemart, vm3, joomla, Joomla';

			$app = JFactory::getApplication();
			$return = $jConfModel->save($jConfig);

			// Check the return value.
			if ($return === false) {
				// Save the data in the session.
				$app->setUserState('com_config.config.global.data', $jConfig);
				vmError(vmText::sprintf('JERROR_SAVE_FAILED', 'installComplete'));
				//return false;
			} else {
				// Set the success message.
				//vmInfo('COM_CONFIG_SAVE_SUCCESS');
			}
		}else {
			$msg = $this->_getMsgDangerousTools();
		}

		$this->setRedirect('index.php?option=com_virtuemart&view=updatesmigration&layout=insfinished&nosafepathcheck=1', $msg);
	}

	private function addMenuEntries($sample){

		$db = JFactory::getDbo();
		$q = 'SELECT extension_id FROM #__extensions WHERE `type` = "component" AND `element` = "com_virtuemart" ';
		$db ->setQuery($q);
		$extensionId = $db->loadResult();

		\JTable::addIncludePath(VMPATH_ROOT .'/administrator/components/com_menus/tables');
		\JModelLegacy::addIncludePath(VMPATH_ROOT .'/administrator/components/com_menus/models', 'MenusModel');
		$menuModel = \JModelLegacy::getInstance('Item', 'MenusModel', array());

		$this->addBackendMenuEntries($menuModel, $extensionId, $sample);

		//if(!$sample) return;

		$q = 'SELECT m.* FROM `#__menu` as m /*LEFT JOIN `#__menu_types` as mt on m.menutype = mt.menutype */ ';
		$q .= 'WHERE home = "1" ';//and language = "*"';
		$q .= ' LIMIT 10';
		$db->setQuery($q);
		$feHomeMenus = $db->loadAssocList();




		//$feHomeMenu['id'] = 0;

		foreach($feHomeMenus as $feHomeMenu){

			$feHomeMenu['link'] = 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=0&virtuemart_manufacturer_id=0';
			$feHomeMenu['component_id'] = $extensionId;
			$feHomeMenu['params'] = '{"show_store_desc":"","categorylayout":"","showcategory_desc":"","showcategory":"","categories_per_row":"","showproducts":"","showsearch":"","productsublayout":"","products_per_row":"","featured":"","featured_rows":"","discontinued":"","discontinued_rows":"","latest":"","latest_rows":"","topten":"","topten_rows":"","recent":"","recent_rows":"","stf_itemid":"","stf_categorylayout":"","stf_show_store_desc":"","stf_showcategory_desc":"","stf_showcategory":"","stf_categories_per_row":"","stf_showproducts":"","stf_showsearch":"","stf_productsublayout":"","stf_products_per_row":"","stf_featured":"","stf_featured_rows":"","stf_discontinued":"","stf_discontinued_rows":"","stf_latest":"","stf_latest_rows":"","stf_topten":"","stf_topten_rows":"","stf_recent":"","stf_recent_rows":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"1","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}';
			$feHomeMenu['menuordering'] = $feHomeMenu['id'];
			unset($feHomeMenu['lft']);unset($feHomeMenu['rgt']);

			$menuModel->save($feHomeMenu);

			$aliasSuffix = '';
			if($feHomeMenu['language']!='*'){
				$aliasSuffix = '-'.strtolower($feHomeMenu['language']);
			}
			$feHomeMenu['home'] = 0;
			$this->storeMenuEntry($menuModel,$feHomeMenu,'Account','account'.$aliasSuffix,'account','index.php?option=com_virtuemart&view=user&layout=edit','','{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}');

			$this->storeMenuEntry($menuModel,$feHomeMenu,'Orders','orders'.$aliasSuffix,'orders','index.php?option=com_virtuemart&view=orders&layout=list','','{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}');

			$this->storeMenuEntry($menuModel,$feHomeMenu,'Cart','cart'.$aliasSuffix,'cart','index.php?option=com_virtuemart&view=cart','','{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}');
		}

		//Update Modules
		$q = 'UPDATE #__modules SET `position`="position-1" WHERE module="mod_menu" and access="1" and client_id=0';
		$db ->setQuery($q);
		$db->execute();
		//vmdebug('addMenuEntries',$data);
	}

	private function addBackendMenuEntries($menuModel, $extensionId){

		$db = JFactory::getDbo();
		//We need a menutype for the BE, else the store function overrides the client_id=1
		$q = 'SELECT `id` FROM `#__menu_types` WHERE menutype="main" and client_id="1" ';
		$db->setQuery($q);
		$res = $db->loadResult();

		if(!$res){
			$q = "INSERT INTO `#__menu_types` (`menutype`, `title`, `description`, client_id) VALUES ('main', 'Main Menu', 'The admin menu for the site',1)";
			$db->setQuery($q);
			$db->execute();
		}

		$data = array();
		//`id`, `menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`

		$data['id'] = 0;
		$data['menutype'] = 'main';
		$data['note'] = '';
		$data['type'] = 'component';
		$data['published'] = 1;
		$data['parent_id'] = 1;
		$data['level'] = 1;
		$data['component_id'] = $extensionId;
		$data['checked_out'] = 0;
		$data['checked_out_time'] = '0000-00-00 00:00:00';
		$data['browserNav'] = 0;
		$data['access'] = 1;
		$data['template_style_id'] = 0;
		//$data['params'] = '{}';
		$data['home'] = 0;
		$data['language'] = '*';
		$data['client_id'] = '1';

		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART','com-virtuemart','com-virtuemart','index.php?option=com_virtuemart','/components/com_virtuemart/assets/images/vmgeneral/menu_icon.png');

		$q = 'SELECT id FROM `#__menu` WHERE menutype ="main" and component_id="'.$extensionId.'" and path = "com-virtuemart" and client_id="1" and published="1"';
		$db->setQuery($q);
		$parent_id = $db->loadResult();

		$data['parent_id'] = $parent_id;
		$data['level'] = 2;

		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_CONTROL_PANEL','com-virtuemart-control-panel','com-virtuemart/com-virtuemart-control-panel','index.php?option=com_virtuemart&view=virtuemart','components/com_virtuemart/assets/images/icon_16/menu-icon16-report.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_CATEGORIES','com-virtuemart-menu-categories','com-virtuemart/com-virtuemart-menu-categories','index.php?option=com_virtuemart&view=category','components/com_virtuemart/assets/images/icon_16/menu-icon16-categories.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_PRODUCTS','com-virtuemart-menu-products','com-virtuemart/com-virtuemart-menu-products','index.php?option=com_virtuemart&view=product','components/com_virtuemart/assets/images/icon_16/menu-icon16-products.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_ORDERS','com-virtuemart-menu-orders','com-virtuemart/com-virtuemart-menu-orders','index.php?option=com_virtuemart&view=orders','components/com_virtuemart/assets/images/icon_16/menu-icon16-orders.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_REPORT','com-virtuemart-menu-report','com-virtuemart/com-virtuemart-menu-report','index.php?option=com_virtuemart&view=report','components/com_virtuemart/assets/images/icon_16/menu-icon16-report.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_USERS','com-virtuemart-menu-users','com-virtuemart/com-virtuemart-menu-users','index.php?option=com_virtuemart&view=user','components/com_virtuemart/assets/images/icon_16/menu-icon16-shoppers.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_MANUFACTURERS','com-virtuemart-menu-manufacturers','com-virtuemart/com-virtuemart-menu-manufacturers','index.php?option=com_virtuemart&view=manufacturer','components/com_virtuemart/assets/images/icon_16/menu-icon16-manufacturers.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_STORE','com-virtuemart-menu-store','com-virtuemart/com-virtuemart-menu-store','index.php?option=com_virtuemart&view=user&task=editshop','components/com_virtuemart/assets/images/icon_16/menu-icon16-shop.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_MEDIAFILES','com-virtuemart-menu-mediafiles','com-virtuemart/com-virtuemart-menu-mediafiles','index.php?option=com_virtuemart&view=media','components/com_virtuemart/assets/images/icon_16/menu-icon16-media.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_SHIPMENTMETHODS','com-virtuemart-menu-shipmentmethods','com-virtuemart/com-virtuemart-menu-shipmentmethods','index.php?option=com_virtuemart&view=shipmentmethod','components/com_virtuemart/assets/images/icon_16/menu-icon16-shipmentmethods.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_PAYMENTMETHODS','com-virtuemart-menu-paymentmethods','com-virtuemart/com-virtuemart-menu-paymentmethods','index.php?option=com_virtuemart&view=paymentmethod','components/com_virtuemart/assets/images/icon_16/menu-icon16-paymentmethods.png');
		$this->storeMenuEntry($menuModel,$data,'COM_VIRTUEMART_MENU_CONFIGURATION','com-virtuemart-menu-configuration','com-virtuemart/com-virtuemart-menu-configuration','index.php?option=com_virtuemart&view=config','components/com_virtuemart/assets/images/icon_16/menu-icon16-config.png');


		$q = 'SELECT extension_id FROM #__extensions WHERE `type` = "component" AND `element` = "com_virtuemart_allinone" ';
		$db ->setQuery($q);
		$extensionId = $db->loadResult();

		$data['parent_id'] = 1;
		$data['level'] = 1;
		$data['component_id'] = $extensionId;
		$this->storeMenuEntry($menuModel,$data,'VirtueMart AIO','virtuemart-aio','virtuemart-aio','index.php?option=com_virtuemart_allinone','class:component');

		$q = 'SELECT extension_id FROM #__extensions WHERE `type` = "component" AND `element` = "com_tcpdf" ';
		$db ->setQuery($q);
		$extensionId = $db->loadResult();

		$data['component_id'] = $extensionId;
		$this->storeMenuEntry($menuModel,$data,'TCPDF','tcpdf','tcpdf','index.php?option=com_virtuemart_tcpdf','class:component');

		//Lets remove the dummy, before it creates trouble
		if(!$res){
			$q = 'DELETE FROM `#__menu_types` WHERE menutype="main" and client_id="1" ';
			$db->setQuery($q);
			$db->execute();
		}

	}

	private function storeMenuEntry($menuModel,$data,$title,$alias,$path,$link,$img,$params = '{}'){

		$db = JFactory::getDbo();

		$menuModel->setState('item.id',0);
		$q = 'SELECT id FROM `#__menu` WHERE menutype ="'.$data['menutype'].'" and component_id="'.$data['component_id'].'" and alias = "'.$alias.'" and client_id="'.$data['client_id'].'" and published="1" AND language="'.$data['language'].'"';

		$db ->setQuery($q);
		$data['id'] = $db->loadResult();
		//if(!empty($data['id'])){
			$data['menuordering'] = $data['id'];
		/*} else {
			$data['menuordering'] = $data['id'];
		}*/
		unset($data['lft']);unset($data['rgt']);
		//vmdebug('storeMenuEntry ' .$q,$data['id'] );
		$data['title'] = $title;
		$data['alias'] = $alias;
		$data['path'] = $path;
		$data['link'] = $link;
		$data['img'] = $img;
		$data['params'] = $params;
		if(!$menuModel->save($data)){
			vmdebug('Error $menuModel->save storeMenuEntry ',$menuModel->getError(),$data);
		}
	}

	private function addAssetEntries(){

		\JTable::addIncludePath(VMPATH_ROOT .'/libraries/src/Table');
		$assetTable = JTable::getInstance('Asset');

		$assetData = array();
		$assetData['parent_id'] = "1";
		$assetData['level'] = "1";
		$assetData['name'] = "com_virtuemart";
		$assetData['title'] = "VIRTUEMART";
		$this->addAssetEntry($assetTable,$assetData);

		$assetData['name'] = "com_virtuemart_allinone";
		$assetData['title'] = "VirtueMart_allinone";
		$this->addAssetEntry($assetTable,$assetData);

		$assetData['name'] = "com_tcpdf";
		$assetData['title'] = "tcpdf";
		$this->addAssetEntry($assetTable,$assetData);

		$db = JFactory::getDbo();
		$q = 'SELECT id FROM #__assets WHERE `name`= "com_modules" ';
		$db ->setQuery($q);
		$modulesId = $db->loadResult();
		$assetData['parent_id'] = $modulesId;
		$assetData['level'] = "2";
		$assetData['name'] = "com_modules.module.100";
		$assetData['title'] = "VM - Administrator Module";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(100,$aId);

		$assetData['name'] = "com_modules.module.101";
		$assetData['title'] = "VM - Currencies Selector";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(101,$aId);

		$assetData['name'] = "com_modules.module.102";
		$assetData['title'] = "VM - Featured products";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(102,$aId);

		$assetData['name'] = "com_modules.module.103";
		$assetData['title'] = "VM - Search in Shop";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(103,$aId);

		$assetData['name'] = "com_modules.module.104";
		$assetData['title'] = "VM - Manufacturer";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(104,$aId);

		$assetData['name'] = "com_modules.module.105";
		$assetData['title'] = "VM - Shopping cart";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(105,$aId);

		$assetData['name'] = "com_modules.module.106";
		$assetData['title'] = "VM - Category";
		$aId = $this->addAssetEntry($assetTable,$assetData);
		$this->updateModuleAssetId(106,$aId);

		$assetTable->rebuild();
	}

	private function addAssetEntry($assetTable, $assetData){

		$db = JFactory::getDbo();
		$q = 'SELECT * FROM #__assets WHERE `name`= "'.$assetData['name'].'" ';
		$db ->setQuery($q);
		$asset = $db->loadAssoc();
		//vmdebug( 'addAssetEntry $asset ',$asset);
		$new = false;
		if(!$asset){
			$assetData['id'] = 0;
			$new = true;
		} else {
			$assetData = array_merge($asset,$assetData);
		}

		$assetData['parent_id'] = 1;
		$assetData['level'] = 1;
		if($new){
			$q = 'INSERT INTO #__assets (`parent_id`,`level`,`name`,`title`,`rules`) VALUES (1, 1, "'.$assetData['name'].'", "'.$assetData['title'].'", "{}")';
			$db->setQuery($q);
			$db->execute();
			return $db->insertid();;
		} else {
			$assetTable->reset();
			$assetTable->save($assetData);
			return $assetData['id'];
		}

	}

	private function updateModuleAssetId($mId,$aId){
		$db = JFactory::getDbo();
		$q = 'UPDATE `#__modules` SET `asset_id`= "'.$aId.'" WHERE  `id`='.$mId.';';
		$db->setQuery($q);
		if(!$db->execute()){
			$m = 'Error updateModuleAssetId ';
			vmError($m, $m.$q.$db->getError());
		}
	}

	/**
	 * This is executing the update table commands to adjust tables to the latest layout
	 * @author Max Milbers
	 */
	function updateDatabase(){
		vRequest::vmCheckToken();
		if(!class_exists('com_virtuemartInstallerScript')) require(VMPATH_ADMIN . DS . 'install' . DS . 'script.virtuemart.php');
		$updater = new com_virtuemartInstallerScript();
		$updater->update(false);
		$this->setRedirect($this->redirectPath, 'Database updated');
	}

	function optimizeDatabase(){
		vRequest::vmCheckToken();
		$db = JFactory::getDbo();

		$tables = array('virtuemart_products','virtuemart_product_categories','virtuemart_product_manufacturers','virtuemart_categories');

		foreach($tables as $table){
			$q = 'OPTIMIZE TABLE' . $db->quoteName('#__'.$table);
			$db->setQuery($q);
			$db->execute();
		}

		$this->setRedirect($this->redirectPath, 'Database updated');
	}

	/**
	 * This is executing the update table commands to adjust joomla tables to the latest layout
	 * @author Max Milbers
	 */
	function updateDatabaseJoomla(){
		vRequest::vmCheckToken();
		if(JVM_VERSION<3){
			$p = VMPATH_ADMIN.DS.'install'.DS.'joomla2.sql';
		} else {
			$p = '';
		}
		//$p = VMPATH_ROOT.DS.'installation'.DS.'sql'.DS.'mysql'.DS.'joomla.sql';
		$msg = 'You are using joomla 3, or File '.$p.' not found';
		if(file_exists($p)){
			if(!class_exists('GenericTableUpdater')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'tableupdater.php');
			$updater = new GenericTableUpdater();
			$updater->updateMyVmTables($p,'_');
			$msg = 'Joomla Database updated';
		}
		$this->setRedirect($this->redirectPath, $msg);
	}

	/**
	 * Delete the config stored in the database and renews it using the file
	 *
	 * @auhtor Max Milbers
	 */
	function renewConfig(){

		$this->checkPermissionForTools();

		//if(VmConfig::get('dangeroustools', true)){
		$model = $this->getModel('config');
		$model -> deleteConfig();
		//	}
		$this->setRedirect($this->redirectPath, 'Configuration is now restored by file');
	}

	/**
	 * This function resets the flag in the config that dangerous tools can't be executed anylonger
	 * This is a security feature
	 *
	 * @author Max Milbers
	 */
	function setDangerousToolsOff(){

		if(!class_exists('VirtueMartModelConfig')) require(VMPATH_ADMIN .'/models/config.php');
		$res  = VirtueMartModelConfig::checkConfigTableExists();
		if(!empty($res)){
			$model = $this->getModel('config');
			$model->setDangerousToolsOff();
		}

	}

	/**
	 * Sends the message to the user that the tools are disabled.
	 *
	 * @author Max Milbers
	 */
	function _getMsgDangerousTools(){
		vmLanguage::loadJLang('com_virtuemart_config');
		$link = JURI::root() . 'administrator/index.php?option=com_virtuemart&view=config';
		$msg = vmText::sprintf('COM_VIRTUEMART_SYSTEM_DANGEROUS_TOOL_DISABLED', vmText::_('COM_VIRTUEMART_ADMIN_CFG_DANGEROUS_TOOLS'), $link);
		return $msg;
	}

	function portMedia(){

		$this->checkPermissionForTools();

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->portMedia();

		$this->setRedirect($this->redirectPath, $result);
	}

	function migrateGeneralFromVmOne(){

		$this->checkPermissionForTools();

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->migrateGeneral();
		if($result){
			$msg = 'Migration general finished';
		} else {
			$msg = 'Migration general was interrupted by max_execution time, please restart';
		}
		$this->setRedirect($this->redirectPath, $result);

	}

	function migrateUsersFromVmOne(){

		$this->checkPermissionForTools();

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->migrateUsers();
		if($result){
			$msg = 'Migration users finished';
		} else {
			$msg = 'Migration users was interrupted by max_execution time, please restart';
		}

		$this->setRedirect($this->redirectPath, $result);

	}

	function migrateProductsFromVmOne(){

		$this->checkPermissionForTools();

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->migrateProducts();
		if($result){
			$msg = 'Migration products finished';
		} else {
			$msg = 'Migration products was interrupted by max_execution time, please restart';
		}
		$this->setRedirect($this->redirectPath, $result);

	}

	function migrateOrdersFromVmOne(){

		$this->checkPermissionForTools();

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->migrateOrders();
		if($result){
			$msg = 'Migration orders finished';
		} else {
			$msg = 'Migration orders was interrupted by max_execution time, please restart';
		}
		$this->setRedirect($this->redirectPath, $result);

	}

	/**
	 * Is doing all migrator steps in one row
	 *
	 * @author Max Milbers
	 */
	function migrateAllInOne(){

		$this->checkPermissionForTools();

		if(!VmConfig::get('dangeroustools', true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath, $msg);
			return false;
		}

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->migrateAllInOne();
		if($result){
			$msg = 'Migration finished';
		} else {
			$msg = 'Migration was interrupted by max_execution time, please restart';
		}
		$this->setRedirect($this->redirectPath, $msg);
	}

	function portVmAttributes(){

		$this->checkPermissionForTools();

		if(!VmConfig::get('dangeroustools', true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath, $msg);
			return false;
		}

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->portVm1Attributes();
		if($result){
			$msg = 'Migration Vm2 attributes finished';
		} else {
			$msg = 'Migration was interrupted by max_execution time, please restart';
		}
		$this->setRedirect($this->redirectPath, $msg);
	}

	function portVmRelatedProducts(){

		$this->checkPermissionForTools();

		if(!VmConfig::get('dangeroustools', true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath, $msg);
			return false;
		}

		$this->storeMigrationOptionsInSession();
		if(!class_exists('Migrator')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'migrator.php');
		$migrator = new Migrator();
		$result = $migrator->portVm1RelatedProducts();
		if($result){
			$msg = 'Migration Vm2 related products finished';
		} else {
			$msg = 'Migration was interrupted by max_execution time, please restart';
		}
		$this->setRedirect($this->redirectPath, $msg);
	}


	function storeMigrationOptionsInSession(){

		$session = JFactory::getSession();

		$session->set('migration_task', vRequest::getString('task',''), 'vm');
		$session->set('migration_default_category_browse', vRequest::getString('migration_default_category_browse',''), 'vm');
		$session->set('migration_default_category_fly', vRequest::getString('migration_default_category_fly',''), 'vm');
	}


	function resetThumbs(){

		$this->checkPermissionForTools();

		if(!VmConfig::get('dangeroustools', true)){
			$msg = $this->_getMsgDangerousTools();
			$this->setRedirect($this->redirectPath, $msg);
			return false;
		}

		$model = VmModel::getModel('updatesMigration');
		$result = $model->resetThumbs();
		$this->setRedirect($this->redirectPath, $result);
	}
}

