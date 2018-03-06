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
 * @version $Id: updatesmigration.php 9651 2017-10-18 12:16:59Z Milbo $
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

			if($sample) $model->installSampleData($sid);

			if(!class_exists('VirtueMartModelConfig')) require_once(VMPATH_ADMIN .'/models/config.php');
			VirtueMartModelConfig::installVMconfigTable();

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

