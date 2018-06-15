<?php
/**
 * VirtueMart script file
 *
 * This file is executed during install/upgrade and uninstall
 *
 * @author Max Milbers, RickG, impleri
 * @package VirtueMart
 */
defined('_JEXEC') or die('Restricted access');







// hack to prevent defining these twice in 1.6 installation
if (!defined('_VM_SCRIPT_INCLUDED')) {
	define('_VM_SCRIPT_INCLUDED', true);


	/**
	 * VirtueMart custom installer class
	 */
	class com_virtuemartInstallerScript {


		/**
		 * method must be called after preflight
		 * Sets the paths and loads VMFramework config
		 */
		public function loadVm($fresh = true) {
 			$this->source_path = JInstaller::getInstance()->getPath('source');
			if(!empty($this->source_path)){
				defined('VMPATH_ROOT') or define('VMPATH_ROOT', $this->source_path);
			} else {
				defined('VMPATH_ROOT') or define('VMPATH_ROOT', JPATH_ROOT);
				$this->source_path = VMPATH_ROOT .'/administrator/components/com_virtuemart';
			}


			if(!class_exists('VmConfig')) require_once(VMPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
			VmConfig::loadConfig(true,$fresh);
			VmConfig::loadJLang('com_virtuemart');

			$this->path = VMPATH_ADMIN;

			VmTable::addIncludePath($this->path .'/tables');
			VmModel::addIncludePath($this->path .'/models');

			//Maybe it is possible to set this within the xml file note by Max Milbers
			VmConfig::ensureMemoryLimit(256);
			VmConfig::ensureExecutionTime(300);
		}

		public function checkIfUpdate(){

			$update = false;
			$this->_db = JFactory::getDBO();
			$q = 'SHOW TABLES LIKE "'.$this->_db->getPrefix().'virtuemart_adminmenuentries"'; //=>jos_virtuemart_shipment_plg_weight_countries
			$this->_db->setQuery($q);
			if($this->_db->loadResult()){

				$q = "SELECT count(id) AS idCount FROM `#__virtuemart_adminmenuentries`";
				$this->_db->setQuery($q);
				$result = $this->_db->loadResult();

				if (empty($result)) {
					$update = false;
				} else {
					$update = true;
				}
			} else {
				$update = false;
			}

			$this->update = $update;
			return $update;
		}


		/**
		 * Pre-process method (e.g. install/upgrade) and any header HTML
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 * @return boolean True if VM exists, null otherwise
		 */
		public function preflight ($type, $parent=null) {

			if(version_compare(JVERSION,'1.6.0','ge') and version_compare(JVERSION,'3.0.0','le')) {

				$this->_db = JFactory::getDbo();
				$q = 'SELECT extension_id FROM #__extensions WHERE `type` = "component" AND `element` = "com_virtuemart" ';
				$this->_db ->setQuery($q);
				$extensionId = $this->_db->loadResult();
				if($extensionId){
					$q = 'DELETE FROM `#__menu` WHERE `component_id` = "'.$extensionId.'" AND `client_id`="1" ';
					$this->_db -> setQuery($q);
					$this->_db -> execute();
				}
			}

			$config = JFactory::getConfig();
			$type = $config->get( 'dbtype' );
			if ($type != 'mysqli') {
				JFactory::getApplication()->enqueueMessage('To ensure seemless working with Virtuemart please use MySQLi as database type in Joomla configuration', 'warning');
				return false;
			}
		}


		/**
		 * Install script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function install ($loadVm = true) {

			if($this->checkIfUpdate()){
				return $this->update($loadVm);
			}

			$this->loadVm(true);

			$_REQUEST['install'] = 1;
			if(!class_exists('JFile')) require(VMPATH_LIBS .'/joomla/filesystem/file.php');
			if(!class_exists('JFolder')) require(VMPATH_LIBS .'/joomla/filesystem/folder.php');

			$this -> joomlaSessionDBToMediumText();

			// install essential and required data
			// should this be covered in install.sql (or 1.6's JInstaller::parseSchemaUpdates)?
			//			if(!class_exists('VirtueMartModelUpdatesMigration')) require(VMPATH_ADMIN.DS.'models'.DS.'updatesMigration.php');
			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'en-GB');//use default joomla
			$lang = strtolower(strtr($lang,'-','_'));

			if(!class_exists('VmModel')) require $this->path.DS.'helpers'.DS.'vmmodel.php';

			if(!class_exists('VirtueMartModelUpdatesMigration')) require($this->path . DS . 'models' . DS . 'updatesmigration.php');
			$model = VmModel::getModel('updatesmigration');
			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_essential_data.sql');
			$model->execSQLFile($this->path.DS.'install'.DS.'install_required_data.sql');

			$model->setStoreOwner();

			$this->createIndexFolder(VMPATH_ROOT .DS. 'images');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'shipment');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'payment');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'category');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'category'.DS.'resized');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'manufacturer');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'manufacturer'.DS.'resized');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'product');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'product'.DS.'resized');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'forSale');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'forSale'.DS.'invoices');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'forSale'.DS.'resized');
			$this->createIndexFolder(VMPATH_ROOT .DS. 'images'.DS.'virtuemart'.DS.'typeless');

			$this->setVmLanguages();
			$this->installLanguageTables();


			$this->checkAddDefaultShoppergroups();

			$model->updateJoomlaUpdateServer('component','com_virtuemart',$this->source_path.DS.'virtuemart.xml');

			$this->deleteSwfUploader();

			$this->displayFinished(false);

			//include($this->path.DS.'install'.DS.'install.virtuemart.html.php');

			// perhaps a redirect to updatesMigration here rather than the html file?
			//			$parent->getParent()->setRedirectURL('index.php?option=com_virtuemart&view=updatesMigration');

			return true;
		}


		/**
		 * creates a folder with empty html file
		 *
		 * @author Max Milbers
		 *
		 */
		public function createIndexFolder($path){

			if(JFolder::create($path)) {
				/*if(!JFile::exists($path .DS. 'index.html')){
					JFile::copy(VMPATH_ROOT.DS.'components'.DS.'index.html', $path .DS. 'index.html');
				}*/
				return true;
			}
			return false;
		}

		/**
		 * Update script
		 * Triggers after database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function update ($loadVm = true) {

			if(!$this->checkIfUpdate()){
				return $this->install($loadVm);
			}

			$this->loadVm(false);

			if(!class_exists('JFile')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'file.php');
			if(!class_exists('JFolder')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');

			//Delete Cache
			$cache = JFactory::getCache();
			$cache->clean();

			$this->_db = JFactory::getDBO();


			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'en-GB');//use default joomla
			$lang = strtolower(strtr($lang,'-','_'));

			if(!class_exists('VmModel')) require $this->path.DS.'helpers'.DS.'vmmodel.php';
			if(!class_exists('VirtueMartModelUpdatesMigration')) require($this->path . DS . 'models' . DS . 'updatesmigration.php');
			$model = VmModel::getModel('updatesmigration');
			//$model = new VirtueMartModelUpdatesMigration(); //JModel::getInstance('updatesmigration', 'VirtueMartModel');
			$model->execSQLFile($this->path.DS.'install'.DS.'install.sql');

			$this -> joomlaSessionDBToMediumText();

			$this->updateToVm3 = $this->isUpdateToVm3();


			$this->alterTable('#__virtuemart_product_prices',
				array(
				'product_price_vdate' => '`product_price_publish_up` datetime NOT NULL default \'0000-00-00 00:00:00\' NULL AFTER `product_currency`',
				'product_price_edate' => '`product_price_publish_down` datetime NOT NULL default \'0000-00-00 00:00:00\' AFTER `product_price_publish_up`'
			));
			$this->alterTable('#__virtuemart_customs',array(
				'custom_field_desc' => '`custom_desc` varchar(4095) COMMENT \'description or unit\'',
				'custom_params' => '`custom_params` text  NOT NULL'
			));
			$this->alterTable('#__virtuemart_product_customfields',array(
				'custom_value' => ' `customfield_value` varchar(2500) COMMENT \'field value\'',
				'custom_price' => ' `customfield_price` DECIMAL(15,6) COMMENT \'price\'',
				'custom_param' => ' `customfield_params` text COMMENT \'Param for Plugins\''
			));


			$this->alterTable('#__virtuemart_userfields',array(
				'params' => '`userfield_params` text',
			));

			$this->alterTable('#__virtuemart_orders',array(
				'customer_note' => '`oc_note` text NOT NULL DEFAULT "" COMMENT \'old customer notes\'',
			));

			if(!class_exists('GenericTableUpdater')) require($this->path . DS . 'helpers' . DS . 'tableupdater.php');
			$updater = new GenericTableUpdater();

			$updater->updateMyVmTables();
			$this->installLanguageTables();


			$this->checkAddDefaultShoppergroups();

			//$this->adjustDefaultOrderStates();
			$this->addMissingOrderstati();
			$this->adjustMenuParamsCategoryView();
			$this->fixOrdersVendorId();

			$this->updateAdminMenuEntries();

			if($this->updateToVm3){
				$this->migrateCustoms();
				$this->checkUserfields();
			}

			//copy sampel media
			$src = $this->path .DS. 'assets' .DS. 'images' .DS. 'vmsampleimages';
			if(JFolder::exists($src)){
				$dst = VMPATH_ROOT .DS. 'images' .DS. 'virtuemart';
				$this->recurse_copy($src,$dst);
			}

			//copy payment/shipment logos to new directory
			$dest = JPATH_ROOT .DS. 'images'.DS.'virtuemart';
			$src = JPATH_ROOT .DS. 'images'.DS.'stories'.DS.'virtuemart';
			if(JFolder::exists($src.DS.'payment') and !JFolder::exists($dest.DS.'payment')){
				$this->recurse_copy($src.DS.'payment',$dest.DS.'payment');
			}
			if(JFolder::exists($src.DS.'shipment') and !JFolder::exists($dest.DS.'shipment')){
				$this->recurse_copy($src.DS.'shipment',$dest.DS.'shipment');
			}


			$model->updateJoomlaUpdateServer('component','com_virtuemart', $this->source_path.DS.'virtuemart.xml');

			//fix joomla BE menu
			if(version_compare(JVERSION,'3.7.0','ge')) {
				$this->removeOldMenuLinks();
			} else {
				$this->checkFixJoomlaBEMenuEntries();
			}


			$this->deleteSwfUploader();

			$this->updateOldConfigEntries();
			if($loadVm) $this->displayFinished(true);

			return true;
		}

		private function installLanguageTables(){
			VmModel::getModel('config');
			VirtueMartModelConfig::installLanguageTables();
		}

		private function setVmLanguages(){
			$m = VmModel::getModel('config');
			$m->setVmLanguages();
		}

		private function updateOldConfigEntries(){

			$config = VmConfig::loadConfig();
			if(VmConfig::get('featured','none') == 'none'){
				$config->set('featured', $config->get('show_featured', 1));
				$config->set('discontinued', $config->get('show_discontinued', 0));
				$config->set('topten', $config->get('show_topTen', 0));
				$config->set('recent', $config->get('show_recent', 0));
				$config->set('latest', $config->get('show_latest', 0));

				$config->set('featured_rows', $config->get('featured_products_rows',1));
				$config->set('discontinued_rows', $config->get('discontinued_products_rows',1));
				$config->set('topten_rows', $config->get('topTen_products_rows',1));
				$config->set('recent_rows', $config->get('recent_products_rows',1));
				$config->set('latest_rows', $config->get('latest_products_rows',1));

				$config->set('omitLoaded_topten', $config->get('omitLoaded_topTen',1));
				$config->set('showcategory', $config->get('showCategory',1));

				$data['virtuemart_config_id'] = 1;
				$data['config'] = $config->toString();

				$confTable = VmModel::getModel('config')->getTable('configs');
				$confTable->bindChecknStore($data);

				VmConfig::loadConfig(true);
			}


		}

		private function deleteSwfUploader(){
			if(JVM_VERSION>0){
				if( JFolder::exists(VMPATH_ROOT. DS. 'media' .DS. 'system'. DS. 'swf')){
					JFolder::delete(VMPATH_ROOT. DS. 'media' .DS. 'system'. DS. 'swf');
				}
				if( JFile::exists(VMPATH_ROOT. DS. 'administrator' .DS. 'language' .DS. 'en-GB'. DS. 'en-GB.com_virtuemart.sys.ini')){
					JFile::delete(VMPATH_ROOT. DS. 'administrator' .DS. 'language' .DS. 'en-GB'. DS. 'en-GB.com_virtuemart.sys.ini');
				}

			}
		}

		private function isUpdateToVm3(){

			if(empty($this->_db)) {
				$this->_db = JFactory::getDBO();
			}

			$tablename = '#__virtuemart_product_customfields';
			$this->_db->setQuery('SHOW FULL COLUMNS  FROM `'.$tablename.'` ');
			//$fullColumns = $this->_db->loadObjectList();
			$columns = $this->_db->loadColumn(0);
			if(in_array('custom_value',$columns) or in_array('custom_price',$columns)){
				vmInfo('Upgrade of VM2 to VM3');
				return true;
			} else {
				vmdebug('Update of VM3');
				return false;
			}

		}

		private function fixOrdersVendorId(){

			$multix = Vmconfig::get('multix','none');

			if( $multix == 'none'){

				if(empty($this->_db)){
					$this->_db = JFactory::getDBO();
				}

				$q = 'SELECT `virtuemart_user_id` FROM #__virtuemart_orders WHERE virtuemart_vendor_id = "0" ';
				$this->_db->setQuery($q);
				$res = $this->_db->loadResult();

				if($res){
					//vmdebug('fixOrdersVendorId ',$res);
					$q = 'UPDATE #__virtuemart_orders SET `virtuemart_vendor_id`=1 WHERE virtuemart_vendor_id = "0" ';
					$this->_db->setQuery($q);
					$res = $this->_db->execute();
					$err = $this->_db->getErrorMsg();
					if(!empty($err)){
						vmError('fixOrdersVendorId update orders '.$err);
					}
					$q = 'UPDATE #__virtuemart_order_items SET `virtuemart_vendor_id`=1 WHERE virtuemart_vendor_id = "0" ';
					$this->_db->setQuery($q);
					$res = $this->_db->execute();
					$err = $this->_db->getErrorMsg();
					if(!empty($err)){
						vmError('fixOrdersVendorId update order_item '.$err);
					}
				}

			}

		}

		private function addMissingOrderstati(){

			if(empty($this->_db)){
				$this->_db = JFactory::getDBO();
			}

			$q = '';

			$qc = 'SELECT * FROM `#__virtuemart_orderstates` WHERE `order_status_code`="F"';
			$this->_db->setQuery($qc);
			$f = $this->_db->loadResult();
			if(!$f) {
				$q .= "(null, 'F', 'COM_VIRTUEMART_ORDER_STATUS_COMPLETED', '', 'R',7, 1)";
			}

			$qc = 'SELECT * FROM `#__virtuemart_orderstates` WHERE `order_status_code`="D"';
			$this->_db->setQuery($qc);
			$d = $this->_db->loadResult();

			if(!$d) {
				if(!empty($q)) {
					$q .= ',';
				}
				$q .= "(null, 'D', 'COM_VIRTUEMART_ORDER_STATUS_DENIED', '', 'A',8, 1)";
			}

			if(!empty($q)) {
				$qi = "INSERT INTO `#__virtuemart_orderstates` (`virtuemart_orderstate_id`, `order_status_code`, `order_status_name`, `order_status_description`, `order_stock_handle`, `ordering`, `virtuemart_vendor_id`) VALUES ".$q.";";

				$this->_db->setQuery($qi);

				if(!$this->_db->execute()){
					$app = JFactory::getApplication();
					$app->enqueueMessage('Error: Insert Orderstati '.$qi );
					$ok = false;
				}
			}
		}

		private function adjustMenuParamsCategoryView(){

			if(empty($this->_db)) $this->_db = JFactory::getDBO();

			$this->_db->setQuery('SELECT `extension_id` FROM `#__extensions` WHERE `type` = "component" AND `element`="com_virtuemart" and state="0"');
			$jId = $this->_db->loadResult();

			if($jId){

				$q = 'SELECT * FROM #__menu WHERE component_id = "'.$jId.'" AND client_id="0" and link like "%view=category%" ';
				$this->_db->setQuery($q);
				$menues = $this->_db->loadAssocList();
				//vmdebug('my menues',$menues);

				foreach($menues as $menu){
					$linkOrig = $menu['link'];
					$menu['link'] = 'index.php?option=com_virtuemart&view=category';
					$link = str_replace('index.php?option=com_virtuemart&view=category','',$linkOrig);
					if(strlen($link)>1){
						$registry = new JRegistry;
						$registry->loadString($menu['params']);

						$paramsLink = explode('&',$link);
						foreach($paramsLink as $param){
							if(strpos($param,'=')!==FALSE){
								$spl = explode('=',$param);
								if(!empty($spl[0]) and isset($spl[1])){
									if($spl[0]!='virtuemart_category_id' and $spl[0]!='virtuemart_manufacturer_id'){
										$registry->set($spl[0], $spl[1]);
									} else {
										$menu['link'] .= '&'.$spl[0].'='.$spl[1];
									}
								} else {
									vmdebug('Key empty ',$spl);
								}
							}
						}
						$params = (string)$registry;
					} else {
						$params = $menu['params'];
					}

					if($linkOrig!=$menu['link'] and $menu['params']!=$params){
						$q = 'UPDATE #__menu' .
						' SET link = "'.$menu['link'].'", params = "'.$this->_db->escape($params).'"'.
						' WHERE id = '.(int) $menu['id'];
						$this->_db->setQuery( $q);

						if (!$this->_db->query()) {
							$m = 'Updating vm category menu failed '.$q;
							vmError($m, $m);
						} else {
							vmdebug('Updated menu $menu '.$menu['id'],$linkOrig,$menu['link'],$param);
						}
					} else {
						//vmdebug('Menu dont need update '.$menu['id']);
					}

				}

				//For the moment, we do not convert old virtuemart views
				if(false){
					$q = 'SELECT * FROM #__menu WHERE component_id = "'.$jId.'" AND client_id="0" and link like "%view=virtuemart%" ';
					$this->_db->setQuery($q);
					$menues = $this->_db->loadAssocList();

					foreach($menues as $menu){
						$linkOrig = $menu['link'];
						$menu['link'] = 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=0&virtuemart_manufacturer_id=0';

						$registry = new JRegistry;
						$registry->loadString($menu['params']);

						if(strpos($linkOrig,'productsublayout')){
							vmdebug('Found productsublayout in the link');
							$productsublayout = str_replace('index.php?option=com_virtuemart&view=virtuemart&productsublayout=','',$linkOrig);
						} else {
							$productsublayout = Vmconfig::get('productsublayout',0 );
						}

						$paramNames = array(
						'categorylayout' => VmConfig::get('vmlayout', 0),
						'show_store_desc' => VmConfig::get('show_store_desc',1),
						'showcategory_desc' => VmConfig::get('showcategory_desc', 1),
						'showcategory' => VmConfig::get('show_categories',1),
						'categories_per_row' => VmConfig::get('homepage_categories_per_row',3),
						'showproducts' => '1',
						'showsearch' => '0',
						'productsublayout' => $productsublayout,
						'products_per_row' => VmConfig::get('homepage_products_per_row', 3),
						'featured' => VmConfig::get('show_featured',1),
						'featured_rows' => VmConfig::get('featured_products_rows',1),
						'discontinued' => VmConfig::get('show_discontinued',0),
						'discontinued_rows' => VmConfig::get('discontinued_products_rows',1),
						'latest' => VmConfig::get('show_latest',1),
						'latest_rows' => VmConfig::get('latest_products_rows',1),
						'topten' => VmConfig::get('show_topTen',1),
						'topten_rows' => VmConfig::get('topTen_products_rows',1),
						'recent' => VmConfig::get('show_recent',0),
						'recent_rows' => VmConfig::get('recent_products_rows',1));

						foreach($paramNames as $key => $default){
							$registry->set($key, $default);
						}

						$params = (string)$registry;

						if($linkOrig!=$menu['link'] and $menu['params']!=$params){
							$q = 'UPDATE #__menu' .
							' SET link = "'.$menu['link'].'", params = "'.$this->_db->escape($params).'"'.
							' WHERE id = '.(int) $menu['id'];
							$this->_db->setQuery( $q);

							if (!$this->_db->query()) {
								$m = 'Updating vm category menu failed '.$q;
								vmError($m, $m);
							} else {
								vmdebug('Updated menu $menu '.$menu['id'],$menu['link'],$param);
							}
						} else {
							vmdebug('Menu dont need update '.$menu['id']);
						}
					}
				}

			}
		}

		private function adjustDefaultOrderStates(){

			if(empty($this->_db)){
				$this->_db = JFactory::getDBO();
			}

			$order_stock_handles = array('P'=>'R', 'C'=>'R', 'X'=>'A', 'R'=>'A', 'S'=>'O');

			foreach($order_stock_handles as $k=>$v){

				$q = 'SELECT `order_stock_handle` FROM `#__virtuemart_orderstates`';
				$this->_db->setQuery($q);
				$res = $this->_db->execute();
				$err = $this->_db->getErrorMsg();
				if(empty($res) and empty($err) ){
					$q = 'UPDATE `#__virtuemart_orderstates` SET `order_stock_handle`="'.$v.'" WHERE  `order_status_code`="'.$k.'" ;';
					$this->_db->setQuery($q);

					if(!$this->_db->execute()){
						$app = JFactory::getApplication();
						$app->enqueueMessage('Error: Install alterTable '.$this->_db->getErrorMsg() );
						$ok = false;
					}
				}
			}

		}

		public function removeOldMenuLinks(){

			$db = JFactory::getDbo();
			$db->setQuery('SELECT `extension_id` FROM `#__extensions` WHERE `type` = "component" AND `element`="com_virtuemart" and state="0"');
			$jId = $db->loadResult();

			if($jId){
				$db = JFactory::getDbo();
				$db->setQuery('DELETE FROM `#__menu` WHERE `component_id` = "'.$jId.'" AND `type` = "component" AND `menutype`="vmadmin"');
				$db->execute();
			}

		}

		/**
		 *
		 */
		public function checkFixJoomlaBEMenuEntries(){

			$db = JFactory::getDbo();
			$db->setQuery('SELECT `extension_id` FROM `#__extensions` WHERE `type` = "component" AND `element`="com_virtuemart" and state="0"');
			$jId = $db->loadResult();

			if($jId){
				$mId = false;
				$qME = 'SELECT * FROM `#__menu` WHERE `component_id` = "'.$jId.'" AND `type` = "component" AND `parent_id` = "1" AND `client_id` = "1" AND id > 1';
				//now lets check if there are menue entries
				$db->setQuery($qME);
				$mEntries = $db->loadAssocList();

				if(!$mEntries){
					$db->setQuery('SELECT `id` FROM `#__menu` WHERE `path`="com-virtuemart" AND `type` = "component" AND `parent_id` = "1" AND `client_id` = "1" AND id > 1');
					if($id = $db->loadResult()){
						$db->setQuery('UPDATE `#__menu` SET `component_id`="'.$jId.'", `language`="*" WHERE `id` = "'.$id.'" ');
						$db->execute();

						$db->setQuery($qME);
						$mEntries = $db->loadAssocList();

					} else {
						vmError('Could not find VirtueMart submenues, please install VirtueMart again');
					}
				}

				if($mEntries){
					if(is_array($mEntries)){
						if(count($mEntries)>1){
							vmError('Multiple menues found');
						} else if(isset($mEntries[0])) {
							$mId = $mEntries[0]['id'];
						}
					}
				} else {
					vmError('Could not find VirtueMart submenues, please install VirtueMart again');
				}

				if($mId){
					$db->setQuery('UPDATE `#__menu` SET `component_id`="'.$jId.'", `language`="*", `menutype`="vmadmin" WHERE `parent_id` = "'.$mId.'" ');
					$db->execute();
					$db->setQuery('UPDATE `#__menu` SET `language`="*", `menutype`="vmadmin" WHERE `id` = "'.$mId.'" ');
					$db->execute();
				}

			}
		}

		private function updateAdminMenuEntries(){

			$sqlfile = VMPATH_ADMIN .DS. 'install' .DS. 'install_essential_data.sql';
			$db = JFactory::getDBO();
			$queries = $db->splitSql(file_get_contents($sqlfile));

			if (count($queries) == 0) {
				vmError('SQL file has no queries!');
				return false;
			}
			$query = trim($queries[0]);

			$q = 'SELECT * FROM `#__virtuemart_adminmenuentries` ';
			$db->setQuery($q);
			$existing = $db->loadAssocList();

			if($existing){

				$queryLines = explode("\n",$query);
				$oldIds = array();
				foreach($queryLines as $n=>$line){
					if(empty($line)){
						unset($queryLines[$n]);
					} else {
						$line = trim($line);
						if(empty($line) or strpos($line, '--' )===0){
							unset($queryLines[$n]);
						}

						if(strpos($line, 'CREATE' )===0 or strpos( $line, 'INSERT')===0){
							$open = strpos($line,'(')+1;
							$close = strrpos($line,')') - $open;
							$keyLine = substr($line,$open,$close);
							//vmdebug('Update Admin menu entries define ',$length,$open,$close,$line,$keyLine);
							$keys = explode(',',$keyLine);

						} else if(strpos($line, '(' )===0){
							$open = strpos($line,'(')+1;
							$close = strrpos($line,')') - $open;
							$valueLine = substr($line,$open,$close);
							$values = explode(',',$valueLine);

							foreach($existing as $entry){
								$name = '\''.$entry['name'].'\'';
								if($name==trim($values[3])){
									//The entry exists, lets update it
									$oldIds[$entry['id']] = $values;
									unset($queryLines[$n]);
								}
							}
						}
					}
				}


				if(count($queryLines)>1){
					$query = trim(implode("\n",$queryLines));
					$query = substr($query,0,-1).';';
				} else {
					$query = false;
				}

				if(count($oldIds)>0){

					$updateBase = 'UPDATE `#__virtuemart_adminmenuentries` SET ';
					foreach($oldIds as $id=>$values){
						$updateQuery = '';
						foreach($keys as $index => $key){
							if($key=='`id`'){
								continue;
							}
							$value = trim($values[$index]);
							if(strpos($value,'\'')===0){
								$value = substr($value,1,-1);
							}
							$updateQuery .= $key . ' = "'.$value.'", ';
						}
						$updateQuery = substr($updateQuery,0,-2);
						$updateQuery .= ' WHERE `id` = '.$id.';';
						$db->setQuery($updateBase.$updateQuery);
						if (!$db->execute()) {
							vmWarn( 'JInstaller::install: '.$sqlfile.' '.vmText::_('COM_VIRTUEMART_SQL_ERROR')." ".$db->stderr(true));
							$ok = false;
						}
						//vmdebug('Update Admin menu entries value $updateQuery',$updateBase.$updateQuery);
					}
				}

			}

			if(!empty($query)){
				$db->setQuery($query);
				if (!$db->execute()) {
					vmWarn( 'JInstaller::install: '.$sqlfile.' '.vmText::_('COM_VIRTUEMART_SQL_ERROR')." ".$db->stderr(true));
				}
			}

		}

		private function checkUserfields(){

			$model = VmModel::getModel('userfields');
			$field = $model->getUserfield('customer_note','name');

			$data = array ('type' => 'textarea'
			, 'maxlength' => 2500
			, 'cols' => 60
			, 'rows' => 1
			, 'name' => 'customer_note'
			, 'title' => 'COM_VIRTUEMART_CNOTES_CART'
			, 'description' => ''
			, 'default' => ''
			, 'required' => 0
			, 'cart' => 1
			, 'account' => 0
			, 'shipment' => 0
			, 'readonly' => 0
			, 'published' => 1
			);

			if(!empty($field->virtuemart_userfield_id)) {
				if($field->published){
					$field->cart = 1;
					$id = $model->store((array)$field);
				}
			} else {
				$id = $model->store($data);
			}

			if($id)	vmInfo('Created shopperfield customer_note');


			$field = $model->getUserfield('tos','name');

			$data = array ('type' => 'custom'
			, 'name' => 'tos'
			, 'title' => 'COM_VIRTUEMART_STORE_FORM_TOS'
			, 'description' => ''
			, 'required' => 1
			, 'cart' => 1
			, 'account' => 0
			, 'shipment' => 0
			, 'readonly' => 0
			, 'published' => 1
			);

			if(!empty($field->virtuemart_userfield_id)) {
				if($field->published){
					$field->cart = 1;
					$field->required = 1;
					$id = $model->store((array)$field);
				}
			} else {
				$id = $model->store($data);
			}

			if($id)	vmInfo('Created shopperfield tos for cart and account');

			$field = $model->getUserfield('agreed','name');
			if($field){
				$field ->published = 0;
				$id = $model->store($field);
				if($id)	vmInfo('Disabled shopperfield agreed, replaced by tos');
			}

		}

		private function migrateCustoms(){

			$db = JFactory::getDBO();
			$q = 'UPDATE `#__virtuemart_product_customfields` SET `published`= "1"  WHERE `published`="0" ';
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished update published '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `field_type`='S',`is_cart_attribute`=1,`is_input`=1,`is_list`='0' WHERE `field_type`='V'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `is_input`=1 WHERE `field_type`='M' AND `is_cart_attribute`=1";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `field_type`='S' WHERE `field_type`='I'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `field_type`='S', `custom_value`='JYES;JNO',`is_list`='1' WHERE `field_type`='B'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `layout_pos`='addtocart' WHERE `is_input`='1'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `layout_pos`='ontop',`is_cart_attribute`=1 WHERE `field_type`='A'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `layout_pos`='related_products' WHERE `field_type`='R'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `layout_pos`='related_categories' WHERE `field_type`='Z'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}

			$q = "UPDATE `#__virtuemart_customs` SET `field_type`='G' WHERE `field_type`='P'";
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if(!empty($err)){
				vmError('updateCustomfieldsPublished migrateCustoms '.$err);
			}
		}

		/**
		 * @author Max Milbers
		 * @param unknown_type $tablename
		 * @param unknown_type $fields
		 * @param unknown_type $command
		 */
		private function alterTable($tablename,$fields,$command='CHANGE'){

			$ok = true;

			if(empty($this->_db)){
				$this->_db = JFactory::getDBO();
			}

			$query = 'SHOW COLUMNS FROM `'.$tablename.'` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadColumn(0);

			foreach($fields as $fieldname => $alterCommand){
				if(in_array($fieldname,$columns)){
					$query = 'ALTER TABLE `'.$tablename.'` '.$command.' COLUMN `'.$fieldname.'` '.$alterCommand;

					$this->_db->setQuery($query);
					if(!$this->_db->execute()){
						$app = JFactory::getApplication();
						$app->enqueueMessage('Error: Install alterTable '.$this->_db->getErrorMsg() );
						$ok = false;
					}
				}
			}

			return $ok;
		}

		/**
		 *
		 * @author Max Milbers
		 * @param unknown_type $table
		 * @param unknown_type $field
		 * @param unknown_type $action
		 * @return boolean This gives true back, WHEN it altered the table, you may use this information to decide for extra post actions
		 */
		private function checkAddFieldToTable($table,$field,$fieldType){

			$query = 'SHOW COLUMNS FROM `'.$table.'` ';
			$this->_db->setQuery($query);
			$columns = $this->_db->loadColumn(0);

			if(!in_array($field,$columns)){


				$query = 'ALTER TABLE `'.$table.'` ADD '.$field.' '.$fieldType;
				$this->_db->setQuery($query);
				if(!$this->_db->execute()){
					$app = JFactory::getApplication();
					$app->enqueueMessage('Error: Install checkAddFieldToTable '.$this->_db->getErrorMsg() );
					return false;
				} else {
					vmdebug('checkAddFieldToTable added '.$field);
					return true;
				}
			}
			return false;
		}


		/**
		* Checks if both types of default shoppergroups are set
		* @author Max Milbers
		*/

		private function checkAddDefaultShoppergroups(){

			$q = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_shoppergroups` WHERE `default` = "1" ';

			$this->_db = JFactory::getDbo();
			$this->_db->setQuery($q);
			$res = $this->_db ->loadResult();

			if(empty($res)){
				$q = "INSERT INTO `#__virtuemart_shoppergroups` (`virtuemart_shoppergroup_id`, `virtuemart_vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`, `shared`) VALUES
								(NULL, 1, '-default-', 'This is the default shopper group.', 1, 1);";
				$this->_db->setQuery($q);
				$this->_db->execute();
			}

			$q = 'SELECT `virtuemart_shoppergroup_id` FROM `#__virtuemart_shoppergroups` WHERE `default` = "2" ';

			$this->_db->setQuery($q);
			$res = $this->_db ->loadResult();

			if(empty($res)){
				$q = "INSERT INTO `#__virtuemart_shoppergroups` (`virtuemart_shoppergroup_id`, `virtuemart_vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`, `shared`) VALUES
								(NULL, 1, '-anonymous-', 'Shopper group for anonymous shoppers', 2, 1);";
				$this->_db->setQuery($q);
				$this->_db->execute();
			}

		}


		private function joomlaSessionDBToMediumText(){

			if(version_compare(JVERSION,'1.6.0','ge')) {
				$fields = array('data'=>'`data` mediumtext NULL AFTER `time`');
				$this->alterTable('#__session',$fields);
			}
		}

		/**
		 * Uninstall script
		 * Triggers before database processing
		 *
		 * @param object JInstallerComponent parent
		 * @return boolean True on success
		 */
		public function uninstall ($parent=null) {

			if(empty($this->path)){
				$this->path = VMPATH_ADMIN;
			}
			//$this->loadVm();
			//include($this->path.DS.'install'.DS.'uninstall.virtuemart.html.php');

			return true;
		}

		/**
		 * Post-process method (e.g. footer HTML, redirect, etc)
		 *
		 * @param string Process type (i.e. install, uninstall, update)
		 * @param object JInstallerComponent parent
		 */
		public function postflight ($type, $parent=null) {
			$_REQUEST['install'] = 0;
			if ($type != 'uninstall') {
				$this->loadVm(false);
				//fix joomla BE menu
				$model = VmModel::getModel('updatesmigration');



				if(!class_exists('VirtueMartModelConfig')) require(VMPATH_ADMIN .'/models/config.php');
				$res  = VirtueMartModelConfig::checkConfigTableExists();

				if(!empty($res)){
					vRequest::setVar(JSession::getFormToken(), '1');
					$config = VmModel::getModel('config');

					$config->setDangerousToolsOff();
				}

			}


			return true;
		}

		/**
		 * copy all $src to $dst folder and remove it
		 *
		 * @author Max Milbers
		 * @param String $src path
		 * @param String $dst path
		 * @param String $type modules, plugins, languageBE, languageFE
		 */
		private function recurse_copy($src,$dst,$delete = true ) {

			$dir = '';
			if(JFolder::exists($src)){
				$dir = opendir($src);
				$this->createIndexFolder($dst);

				if(is_resource($dir)){
					while(false !== ( $file = readdir($dir)) ) {
						if (( $file != '.' ) && ( $file != '..' )) {
							if ( is_dir($src .DS. $file) ) {
								if(!JFolder::create($dst . DS . $file)){
									$app = JFactory::getApplication ();
									$app->enqueueMessage ('Couldnt create folder ' . $dst . DS . $file);
								}
								$this->recurse_copy($src .DS. $file,$dst .DS. $file);
							}
							else {
								if($delete and JFile::exists($dst .DS. $file)){
									if(!JFile::delete($dst .DS. $file)){
										$app = JFactory::getApplication();
										$app -> enqueueMessage('Couldnt delete '.$dst .DS. $file);
									}
								}
								if(!JFile::copy($src .DS. $file,$dst .DS. $file)){
									$app = JFactory::getApplication();
									$app -> enqueueMessage('Couldnt move '.$src .DS. $file.' to '.$dst .DS. $file);
								}
							}
						}
					}
					closedir($dir);
					//if (is_dir($src)) JFolder::delete($src);
					return true;
				}
			}

			$app = JFactory::getApplication();
			$app -> enqueueMessage('Couldnt read dir '.$dir.' source '.$src);

		}

		public function displayFinished($update){

			include(VMPATH_ADMIN.'/views/updatesmigration/tmpl/insfinished.php');

		}

	}

	/**
	 * Legacy j1.5 function to use the 1.6 class install/update
	 *
	 * @return boolean True on success
	 * @deprecated
	 */
	function com_install() {
		$vmInstall = new com_virtuemartInstallerScript();
		$upgrade = $vmInstall->checkIfUpdate();

		if(version_compare(JVERSION,'1.6.0','ge')) {
			// Joomla! 1.6 code here
		} else {
			// Joomla! 1.5 code here
			$method = ($upgrade) ? 'update' : 'install';
			$vmInstall->$method();
			$vmInstall->postflight($method);
		}


		return true;
	}

	/**
	 * Legacy j1.5 function to use the 1.6 class uninstall
	 *
	 * @return boolean True on success
	 * @deprecated
	 */
	function com_uninstall() {
		$vmInstall = new com_virtuemartInstallerScript();
		// 		$vmInstall->preflight('uninstall');

		if(version_compare(JVERSION,'1.6.0','ge')) {
			// Joomla! 1.6 code here
		} else {
			$vmInstall->uninstall();
			$vmInstall->postflight('uninstall');
		}

		return true;
	}

} // if(defined)

// pure php no tag
