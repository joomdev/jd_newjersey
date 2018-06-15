<?php
/**
*
* Data module for updates and migrations
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



/**
 * Model class for updates and migrations
 *
 * @package	VirtueMart
 * @subpackage updatesMigration
 * @author Max Milbers, RickG
 */
class VirtueMartModelUpdatesMigration extends VmModel {

    /**
     * Checks the VirtueMart Server for the latest available Version of VirtueMart
     *
     * @return string Example: 1.1.2
     */
    function getLatestVersion() {

    	if(!class_exists('VmConnector')) require(VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');

		$url = "http://virtuemart.net/index2.php?option=com_versions&catid=1&myVersion={".VmConfig::getInstalledVersion()."}&task=latestversionastext";
		$result = VmConnector::handleCommunication($url);

		return $result;
    }


    /**
     * @author Max Milbers
     */
    function determineStoreOwner() {
		if(!class_exists('VirtueMartModelVendor')) require(VMPATH_ADMIN.DS.'models'.DS.'vendor.php');
		$virtuemart_user_id = VirtueMartModelVendor::getUserIdByVendorId(1);
		if (isset($virtuemart_user_id) && $virtuemart_user_id > 0) {
		    $this->_user = JFactory::getUser($virtuemart_user_id);
		}
		else {
		    $this->_user = JFactory::getUser();
		}
		return $this->_user->id;
    }


    /**
     * @author Max Milbers
     */
	function setStoreOwner($userId=-1) {

		if(!vmAccess::manager('core')){
			vmError('You have no rights to performe this action');
			return false;
		}

		if (empty($userId)) {
			vmInfo('No uer id given, can not set vendor relation');
			return false;
		} else if($userId===-1){
			$userId = 0;
		}

		if (empty($userId)) {
			$userId = $this->determineStoreOwner();
			vmdebug('setStoreOwner $userId = '.$userId.' by determineStoreOwner');
		}

		$utable = $this->getTable('vmusers');

		if ( empty($userId)) return false;

		$db = JFactory::getDBO();

		$db->setQuery( 'SELECT `virtuemart_user_id` FROM `#__virtuemart_vmusers` WHERE `virtuemart_user_id` ="'.$userId.'" ');
		$vmuid = $db->loadResult();

		$db->setQuery( 'UPDATE `#__virtuemart_vmusers` SET `virtuemart_vendor_id` = "0", `user_is_vendor` = "0" WHERE `virtuemart_vendor_id` ="1" ');
		if ($db->execute() == false ) {
			vmWarn( 'UPDATE __vmusers failed for virtuemart_user_id '.$userId);
			return false;
		}

		if($vmuid){
			$data = array('virtuemart_user_id' => $userId, 'virtuemart_vendor_id' => "1", 'user_is_vendor' => "1");
			if($utable->bindChecknStore($data,true)){
				vmInfo('setStoreOwner VmUser updated new main vendor has user id  '.$userId);
			}
		} else {
			//Can not use the table here, it would set the virtuemart_vendor_id to zero if there is no entry
			$date = JFactory::getDate();
			$today = $date->toSQL();
			$db->setQuery('INSERT INTO `#__virtuemart_vmusers` (`virtuemart_user_id`,`virtuemart_vendor_id`,`user_is_vendor`,`created_on`)
					VALUES ("'.$userId.'","1","1","'.$today.'") ');
			if ($db->execute() == false ) {
				vmWarn( 'INSERT __vmusers failed for virtuemart_user_id '.$userId);
				return false;
			}
		}

	    return $userId;
    }



    /**
     * Installs sample data to the current database.
     *
     * @author Max Milbers, RickG
     * @params $userId User Id to add the userinfo and vendor sample data to
     */
    function installSampleData($userId = null) {

	if ($userId == null) {
	    $userId = $this->determineStoreOwner();
	}

	$fields['username'] =  $this->_user->username;
	$fields['virtuemart_user_id'] =  $userId;
	$fields['address_type'] =  'BT';
	// Don't change this company name; it's used in install_sample_data.sql
	$fields['company'] =  "Sample Company";
	$fields['title'] =  'Mr';
	$fields['last_name'] =  'John';
	$fields['first_name'] =  'Doe';
	$fields['middle_name'] =  '';
	$fields['phone_1'] =  '555-555-555';
	$fields['address_1'] =  'PO Box 123';
	$fields['city'] =  'Seattle';
	$fields['zip'] =  '98101';
	$fields['virtuemart_state_id'] =  '48';
	$fields['virtuemart_country_id'] =  '223';

	//Dont change this, atm everything is mapped to mainvendor with id=1
	$fields['user_is_vendor'] =  '1';
	$fields['virtuemart_vendor_id'] = '1';
	$fields['vendor_name'] =  'Sample Company';
		//quickndirty hack for vendor_phone
		vRequest::setVar('phone_1',$fields['phone_1']);
	//$fields['vendor_phone'] =  '555-555-1212';
	$fields['vendor_store_name'] =  "VirtueMart 3 Sample store";
	$fields['vendor_store_desc'] =  '<p>Welcome to VirtueMart the ecommerce managment system. The sample data give you a good insight of the possibilities with VirtueMart. The product description is directly the manual to configure the demonstrated features. \n </p><p>You see here the store description used to describe your store. Check it out!</p> <p>We were established in 1869 in a time when getting good clothes was expensive, but the quality was good. Now that only a select few of those authentic clothes survive, we have dedicated this store to bringing the experience alive for collectors and master carrier everywhere.</p>';
	$fields['virtuemart_media_id'] =  1;
	$fields['vendor_currency'] = '47';
	$fields['vendor_accepted_currencies'] = '52,26,47,144';
	$fields['vendor_terms_of_service'] =  '<h5>This is a demo store. Your orders will not proceed. You have not configured any terms of service yet. Click <a href="'.JURI::base(true).'/index.php?option=com_virtuemart&view=user&task=editshop">here</a> to change this text.</h5>';
	$fields['vendor_url'] = JURI::root();
	$fields['vendor_name'] =  'Sample Company';
	$fields['vendor_legal_info']="VAT-ID: XYZ-DEMO<br />Reg.Nr: DEMONUMBER";
	$fields['vendor_letter_css']='.vmdoc-header { }
.vmdoc-footer { }
';
	$fields['vendor_letter_header_html']='<h1>{vm:vendorname}</h1><p>{vm:vendoraddress}</p>';
	$fields['vendor_letter_header_image']='1';
	$fields['vendor_letter_footer_html']='{vm:vendorlegalinfo}<br /> Page {vm:pagenum}/{vm:pagecount}';
	if(!class_exists('VirtueMartModelUser')) require(VMPATH_ADMIN.DS.'models'.DS.'user.php');
	$usermodel = VmModel::getModel('user');
	$usermodel->setId($userId);

	//Save the VM user stuff
	if(!$usermodel->store($fields)){
		vmError(vmText::_('COM_VIRTUEMART_NOT_ABLE_TO_SAVE_USER_DATA')  );
	}

	if(!VmConfig::$vmlang){
		vmLanguage::initialise();
	}

	$lang = VmConfig::$vmlang;

	$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_sample_data_'.$lang.'.sql';
	if (!file_exists($filename)) {
		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_sample_data.sql';
	}

	//copy sampel media
	$src = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart' .DS. 'assets' .DS. 'images' .DS. 'vmsampleimages';
	// 			if(version_compare(JVERSION,'1.6.0','ge')) {
	$dst = VMPATH_ROOT .DS. 'images' .DS. 'virtuemart';

	$this->recurse_copy($src,$dst);

	if(!$this->execSQLFile($filename)){
		vmError(vmText::_('Problems execution of SQL File '.$filename));
	} else {

		$comdata['virtuemart_vendor_id'] = 1;
		$comdata['ordering'] = 1;
		$comdata['shared'] = 0;
		$comdata['published'] = 1;

		//update jplugin_id from shipment and payment
		$db = JFactory::getDBO();
		$q = 'SELECT `extension_id` FROM #__extensions WHERE element = "weight_countries" AND folder = "vmshipment"';
		$db->setQuery($q);
		$shipment_plg_id = $db->loadResult();

		if(!empty($shipment_plg_id)){

			$shipdata =& $comdata;
			$shipdata['shipment_jplugin_id'] = $shipment_plg_id;
			$shipdata['shipment_element'] = "weight_countries";
			$shipdata['shipment_params'] = 'shipment_logos=""|countries=""|zip_start=""|zip_stop=""|weight_start=""|weight_stop=""|weight_unit="KG"|nbproducts_start=0|nbproducts_stop=0|orderamount_start=""|orderamount_stop=""|cost="0"|package_fee="2.49"|tax_id="0"|free_shipment="500"';
			$shipdata['shipment_name'] = "Self pick-up";

			$table = $this->getTable('shipmentmethods');
			$table->bindChecknStore($shipdata);

			//Create table of the plugin
			$url = '/plugins/vmshipment/weight_countries';

			if (!class_exists ('plgVmShipmentWeight_countries')) require(VMPATH_ROOT . DS . $url . DS . 'weight_countries.php');
			$this->installPluginTable('plgVmShipmentWeight_countries','#__virtuemart_shipment_plg_weight_countries','Shipment Weight Countries Table');
		}

		$q = 'SELECT `extension_id` FROM #__extensions WHERE element = "standard" AND folder = "vmpayment"';
		$db->setQuery($q);
		$payment_plg_id = $db->loadResult();
		if(!empty($payment_plg_id)){

			$pdata =& $comdata;
			$pdata['payment_jplugin_id'] = $payment_plg_id;
			$pdata['payment_element'] = "standard";
			$pdata['payment_params'] = 'payment_logos=""|countries=""|payment_currency="0"|status_pending="U"|send_invoice_on_order_null="1"|min_amount=""|max_amount=""|cost_per_transaction="0.10"|cost_percent_total="1.5"|tax_id="0"|payment_info=""';
			$pdata['payment_name'] = "Cash on delivery";

			$table = $this->getTable('paymentmethods');
			$table->bindChecknStore($pdata);

			$url = '/plugins/vmpayment/standard';

			if (!class_exists ('plgVmPaymentStandard')) require(VMPATH_ROOT . DS . $url . DS . 'standard.php');
			$this->installPluginTable('plgVmPaymentStandard','#__virtuemart_payment_plg_standard','Payment Standard Table');
		}
		vmInfo(vmText::_('COM_VIRTUEMART_SAMPLE_DATA_INSTALLED'));
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
	static public function recurse_copy($src,$dst,$delete = true ) {

		if(!class_exists('JFolder'))
			require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
		$dir = '';
		if(JFolder::exists($src)){
			$dir = opendir($src);
			JFolder::create($dst);

			if(is_resource($dir)){
				while(false !== ( $file = readdir($dir)) ) {
					if (( $file != '.' ) && ( $file != '..' )) {
						if ( is_dir($src .DS. $file) ) {
							if(!JFolder::create($dst . DS . $file)){
								$app = JFactory::getApplication ();
								$app->enqueueMessage ('Couldnt create folder ' . $dst . DS . $file);
							}
							self::recurse_copy($src .DS. $file,$dst .DS. $file);
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

	function installPluginTable ($className,$tablename,$tableComment) {

		$query = "CREATE TABLE IF NOT EXISTS `" . $tablename . "` (";
		if(!empty($tablesFields)){
			foreach ($tablesFields as $fieldname => $fieldtype) {
				$query .= '`' . $fieldname . '` ' . $fieldtype . " , ";
			}
		} else {
			$SQLfields = call_user_func($className."::getTableSQLFields");
			//$SQLfields = $className::getTableSQLFields ();
		//	$loggablefields = $className::getTableSQLLoggablefields ();
			$loggablefields = call_user_func($className."::getTableSQLLoggablefields");
			foreach ($SQLfields as $fieldname => $fieldtype) {
				$query .= '`' . $fieldname . '` ' . $fieldtype . " , ";
			}
			foreach ($loggablefields as $fieldname => $fieldtype) {
				$query .= '`' . $fieldname . '` ' . $fieldtype . ", ";
			}
		}

		$query .= "	      PRIMARY KEY (`id`)
	    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='" . $tableComment . "' AUTO_INCREMENT=1 ;";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		if (!$db->execute ()) {
			vmError ( $className.'::onStoreInstallPluginTable: ' . vmText::_ ('COM_VIRTUEMART_SQL_ERROR') . ' ' . $db->stderr (TRUE));
		}

	}


    function restoreSystemDefaults() {

		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRemove', $this);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_required_data.sql';
		$this->execSQLFile($filename);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.sql';
		$this->execSQLFile($filename);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

			if(!class_exists('GenericTableUpdater')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'tableupdater.php');
		$updater = new GenericTableUpdater();
		$updater->createLanguageTables();


		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRestore', $this);
    }

    function restoreSystemTablesCompletly() {

		$this->removeAllVMTables();

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install.sql';
		$this->execSQLFile($filename);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_essential_data.sql';
		$this->execSQLFile($filename);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

		if(!class_exists('GenericTableUpdater')) require(VMPATH_ADMIN . DS . 'helpers' . DS . 'tableupdater.php');
		$updater = new GenericTableUpdater();
		$updater->createLanguageTables();

		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRestore', $this);
    }

    /**
     * Parse a sql file executing each sql statement found.
     *
     * @author Max Milbers
     */
    function execSQLFile($sqlfile ) {

		// Check that sql files exists before reading. Otherwise raise error for rollback
		if ( !file_exists($sqlfile) ) {
			vmError('execSQLFile, SQL file not found!');
			return false;
		}

		if(!class_exists('VmConfig')){
			require_once(VMPATH_ADMIN .'/helpers/config.php');
			VmConfig::loadConfig(false,true);
		}

		if(!VmConfig::$vmlang){
			$params = JComponentHelper::getParams('com_languages');
			$lang = $params->get('site', 'en-GB');//use default joomla
			$lang = strtolower(strtr($lang,'-','_'));
		} else {
			$lang = VmConfig::$vmlang;
		}

		// Create an array of queries from the sql file
		jimport('joomla.installer.helper');
		$db = JFactory::getDBO();
		$queries = $db->splitSql(file_get_contents($sqlfile));

		if (count($queries) == 0) {
		    vmError('SQL file has no queries!');
		    return false;
		}
		$ok = true;

		// Process each query in the $queries array (split out of sql file).
		foreach ($queries as $k=>$query) {
			if(empty($query)){
				vmWarn('execSQLFile Query was empty in file '.$sqlfile);
				continue;
			}
		    $query = trim($query);
			$queryLines = explode("\n",$query);
			//vmdebug('test',$queryLines);
			foreach($queryLines as $n=>$line){
				if(empty($line)){
					unset($queryLines[$n]);
				} else {
					if(strpos($line, 'CREATE' )!==false or strpos( $line, 'INSERT')!==false){
						$queryLines[$n] = str_replace('XLANG',$lang,$line);
					}
				}
			}
			$query = implode("\n",$queryLines);

			if(!empty($query)){

				$db->setQuery($query);
				if (!$db->execute()) {
				    vmWarn( 'JInstaller::install: '.$sqlfile.' '.vmText::_('COM_VIRTUEMART_SQL_ERROR')." ".$db->stderr(true));
				    $ok = false;
				}
		    }
		}

		return $ok;
    }

    /**
     * Delete all Virtuemart tables.
     *
     * @return True if successful, false otherwise
     */
    function removeAllVMTables() {
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();

		$prefix = $config->get('dbprefix').'virtuemart_%';
		$db->setQuery('SHOW TABLES LIKE "'.$prefix.'"');
		if (!$tables = $db->loadColumn()) {
			vmInfo ('removeAllVMTables no tables found '.$db->getErrorMsg());
		    return false;
		}

		$app = JFactory::getApplication();
		foreach ($tables as $table) {

		    $db->setQuery('DROP TABLE ' . $table);
		    if($db->execute()){
		    	$droppedTables[] = substr($table,strlen($prefix)-1);
		    } else {
		    	$errorTables[] = $table;
		    	$app->enqueueMessage('Error drop virtuemart table ' . $table);
		    }
		}


		if(!empty($droppedTables)){
			$app->enqueueMessage('Dropped virtuemart table ' . implode(', ',$droppedTables));
		}

	    if(!empty($errorTables)){
			$app->enqueueMessage('Error dropping virtuemart table ' . implode($errorTables,', '));
			return false;
		}

		//Delete VM menues
		$q = 'DELETE FROM #__menu WHERE `link` = "%option=com_virtuemart%" ';
		$db->setQuery($q);
		$db->execute();

		return true;
    }


    /**
     * Remove all the data from all Virutmeart tables.
     *
     * @return boolean True if successful, false otherwise.
     */
    function removeAllVMData() {
		JPluginHelper::importPlugin('vmextended');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onVmSqlRemove', $this);

		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall_data.sql';
		$this->execSQLFile($filename);
		$tables = array('categories','manufacturers','manufacturercategories','paymentmethods','products','shipmentmethods','vendors');
		$db = JFactory::getDBO();
		$prefix = $db->getPrefix();
		foreach ($tables as $table) {
			$query = 'SHOW TABLES LIKE "'.$prefix.'virtuemart_'.$table.'_%"';
			$db->setQuery($query);
			if($translatedTables= $db->loadColumn()) {
				foreach ($translatedTables as $translatedTable) {
					$db->setQuery('TRUNCATE TABLE `'.$translatedTable.'`');
					if($db->execute()) vmInfo( $translatedTable.' empty');
					else vmError($translatedTable.' language table Cannot be deleted');
				}
			} else vmInfo('No '.$table.' language table found to delete '.$query);
		}
		//"TRUNCATE TABLE IS FASTER and reset the primary Keys;

		//install required data again
		$filename = VMPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'install_required_data.sql';
		$this->execSQLFile($filename);

		return true;
    }

	/**
	 * @param $type= 'plugin'
	 * @param $element= 'textinput'
	 * @param $src = path . DS . 'plugins' . DS . $group . DS . $element;
	 *
	 */
	public function updateJoomlaUpdateServer( $type, $element, $dst, $group=''  ){

		$db = JFactory::getDBO();
		$extensionXmlFileName = self::getExtensionXmlFileName($type, $element, $dst );
		$xml=simplexml_load_file($extensionXmlFileName);

		// get extension id
		$query="SELECT `extension_id` FROM `#__extensions` WHERE `type`=".$db->quote($type)." AND `element`=".$db->quote($element);
		if ($group) {
			$query.=" AND `folder`=".$db->quote($group);
		}

		$db->setQuery($query);
		$extension_id=$db->loadResult();
		if(!$extension_id) {
			vmdebug('updateJoomlaUpdateServer no extension id ',$query);
			return;
		}
		// Is the extension already in the update table ?
		$query="SELECT * FROM `#__update_sites_extensions` WHERE `extension_id`=".$extension_id;
		$db->setQuery($query);
		$update_sites_extensions=$db->loadObject();
		//VmConfig::$echoDebug=true;


		// Update the version number for all
		if(isset($xml->version)) {
			$query="UPDATE `#__updates` SET `version`=".$db->quote((string)$xml->version)."
					         WHERE `extension_id`=".$extension_id;
			$db->setQuery($query);
			$db->query();
		}


		if(isset($xml->updateservers->server)) {
			if (!$update_sites_extensions) {

				$query="INSERT INTO `#__update_sites` SET `name`=".$db->quote((string)$xml->updateservers->server['name']).",
				        `type`=".$db->quote((string)$xml->updateservers->server['type']).",
				        `location`=".$db->quote((string)$xml->updateservers->server).", enabled=1 ";
				$db->setQuery($query);
				$db->query();

				$update_site_id=$db->insertId();

				$query="INSERT INTO `#__update_sites_extensions` SET `update_site_id`=".$update_site_id." , `extension_id`=".$extension_id;
				$db->setQuery($query);
				$db->query();
			} else {
				if(empty($update_sites_extensions->update_site_id)){
					vmWarn('Update site id not found for '.$element);
					vmdebug('Update site id not found for '.$element,$update_sites_extensions);
					return false;
				}
				$query="SELECT * FROM `#__update_sites` WHERE `update_site_id`=".$update_sites_extensions->update_site_id;
				$db->setQuery($query);
				$update_sites= $db->loadAssocList();
				//vmdebug('updateJoomlaUpdateServer',$update_sites);
				if(empty($update_sites)){
					vmdebug('No update sites found, they should be inserted');
					return false;
				}
				//Todo this is written with an array, but actually it is only tested to run with one server
				foreach($update_sites as $upSite){
					if (strcmp($upSite['location'], (string)$xml->updateservers->server) != 0) {
						// the extension was already there: we just update the server if different
						$query="UPDATE `#__update_sites` SET `location`=".$db->quote((string)$xml->updateservers->server['name'])."
					         WHERE update_site_id=".$update_sites_extensions->update_site_id;
						$db->setQuery($query);
						$db->query();
					}
				}

			}

		} else {
			echo ('<br />UPDATE SERVER NOT FOUND IN XML FILE:'.$extensionXmlFileName);
		}
	}

	/**
	 * @param $type= 'plugin'
	 * @param $element= 'textinput'
	 * @param $src = path . DS . 'plugins' . DS . $group . DS . $element;
	 */
	static function getExtensionXmlFileName($type, $element, $dst ){
		if ($type=='plugin') {
			$extensionXmlFileName=  $dst. DS . $element.  '.xml';
		} else if ($type=='module'){
			$extensionXmlFileName = $dst. DS . $element.DS . $element. '.xml';
		} else {
			$extensionXmlFileName = $dst;//;. DS . $element.DS . $element. '.xml';
		}
		return $extensionXmlFileName;
	}

	public function setSafePathCreateFolders($token = ''){

		if(!class_exists('JFolder')){
			require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
		}

		if(empty($token)){
			$safePath = shopFunctions::getSuggestedSafePath();
		} else {
			$safePath = VMPATH_ADMIN.'/'.$token.'/';
		}
		$safePath = str_replace('/',DS,$safePath);

		if(!class_exists('ShopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$invoice = $safePath.ShopFunctionsF::getInvoiceFolderName();

		//$invoice = shopFunctions::getInvoicePath($safePath);
		$encryptSafePath = $safePath. vmCrypt::ENCRYPT_SAFEPATH;

		JFolder::create($safePath,0755);vmdebug('setSafePathCreateFolders $safePath',$safePath);
		JFolder::create($invoice,0755);vmdebug('setSafePathCreateFolders $invoice',$invoice);
		JFolder::create($encryptSafePath,0755);vmdebug('setSafePathCreateFolders $encryptSafePath',$encryptSafePath);

		$config = VmConfig::loadConfig();
		//vmdebug('setSafePathCreateFolders set forSale_path ',$safePath,$config);
		$config->set('forSale_path', $safePath);

		$data['virtuemart_config_id'] = 1;
		$data['config'] = $config->toString();

		$confTable = $this->getTable('configs');
		//vmdebug('setSafePathCreateFolders set forSale_path ',$safePath,$data['config']);
		$confTable->bindChecknStore($data);

		VmConfig::loadConfig(true);

		return true;
	}

	/**
	 * This function deletes all stored thumbs and deletes the entries for all thumbs, usually this is need for shops
	 * older than vm2.0.22. The new pattern is now not storing the url as long it is not overwritten.
	 * Of course the function deletes all overwrites, but you can now relativly easy change the thumbsize in your shop
	 * @author Max Milbers
	 */
	function resetThumbs(){

		$db = JFactory::getDbo();
		$q = 'UPDATE `#__virtuemart_medias` SET `file_url_thumb`=""';

		$db->setQuery($q);
		$db->execute();
		$err = $db->getErrorMsg();
		if(!empty($err)){
			vmError('resetThumbs Update entries failed ',$err);
		}
		jimport('joomla.filesystem.folder');
		$tmpimg_resize_enable = VmConfig::get('img_resize_enable',1);

		VmConfig::set('img_resize_enable',0);
		$this->deleteMediaThumbFolder('media_category_path');
		$this->deleteMediaThumbFolder('media_product_path');
		$this->deleteMediaThumbFolder('media_manufacturer_path');
		$this->deleteMediaThumbFolder('media_vendor_path');
		$this->deleteMediaThumbFolder('forSale_path_thumb','');

		VmConfig::set('img_resize_enable',$tmpimg_resize_enable);
		return true;

	}

	/**
	 * Delets a thumb folder and recreates it, contains small nasty hack for the thumbnail folder of the "file for sale"
	 * @author Max Milbers
	 * @param $type
	 * @param string $resized
	 * @return bool
	 */
	private function deleteMediaThumbFolder($type,$resized='resized'){

		if(!empty($resized)) $resized = DS.$resized;
		$typePath = VmConfig::get($type);
		if(!empty($typePath)){
			if(!class_exists('JFolder')) require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
			$path = VMPATH_ROOT.DS.str_replace('/',DS,$typePath).$resized;
			$msg = JFolder::delete($path);
			if(!$msg){
				vmWarn('Problem deleting '.$type);
			}
			$msg = JFolder::create($path);
			return $msg;
		} else {

			return 'Config path for '.$type.' empty';
		}

	}

}

//pure php no tag
