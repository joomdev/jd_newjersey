<?php
defined('_JEXEC') or die('Restricted access');
/**
 * Class to update the tables according to the sql files
 *
 * @version $Id: tableupdater.php 4657 2011-11-10 12:06:03Z Milbo $
 * @package VirtueMart
 * @subpackage core
 * @author Max Milbers
 * @copyright Copyright (C) 2011- 2016 by the virtuemart team - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 2, see COPYRIGHT.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * http://virtuemart.net
 */

if(!class_exists('VmModel')) require VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php';

class GenericTableUpdater extends VmModel{

	public function __construct(){

		$this->_app = JFactory::getApplication();
		$this->_db = JFactory::getDbo();
		// 		$this->_oldToNew = new stdClass();
		$this->starttime = microtime(true);

		$max_execution_time = VmConfig::getExecutionTime();
		$jrmax_execution_time= vRequest::getInt('max_execution_time',900);

		if(!empty($jrmax_execution_time)){
			// 			vmdebug('$jrmax_execution_time',$jrmax_execution_time);
			if($max_execution_time!==$jrmax_execution_time) @ini_set( 'max_execution_time', $jrmax_execution_time );
		}

		$this->maxScriptTime = VmConfig::getExecutionTime() * 0.90-1;	//Lets use 10% of the execution time as reserve to store the progress

		VmConfig::ensureMemoryLimit(256);

		$this->maxMemoryLimit = (VmConfig::getMemoryLimit() * 0.99 * 1048576) - 6291456;	//6 MB Reserve

		$config = JFactory::getConfig();
		$this->_prefix = $config->get('dbprefix');

		$this->reCreaPri = VmConfig::get('reCreaPri',0);
		$this->reCreaKey = VmConfig::get('reCreaKey',1);
	}

	var $tables = array( 	'products'=>'virtuemart_product_id',
									'vendors'=>'virtuemart_vendor_id',
									'categories'=>'virtuemart_category_id',
									'manufacturers'=>'virtuemart_manufacturer_id',
									'manufacturercategories'=>'virtuemart_manufacturercategories_id',

									'paymentmethods'=>'virtuemart_paymentmethod_id',
									'shipmentmethods'=>'virtuemart_shipmentmethod_id');

	/**
	 *
	 *
	 * @author Max Milbers
	 * @param unknown_type $config
	 */
	public function createLanguageTables($langs=0){

		if(empty($langs)){
			$langs = VmConfig::get('active_languages',array(VmConfig::$jDefLang));
			if(empty($langs)){
				$langs = (array)VmConfig::$defaultLang;
			}
		}

		foreach($langs as $i => $lang){
			$lang = strtolower(strtr($lang,'-','_'));
			if(empty($lang))unset($langs[$i]);
		}

		$langTables = array();
		//Todo add the mb_ stuff here
		// 		vmTime('my langs <pre>'.print_r($langs,1).'</pre>');
		$i = 0;
		foreach($this->tables as $table=>$tblKey){

// 			if($i>1) continue;
			$className = 'Table'.ucfirst ($table);
			if(!class_exists($className)) require(VMPATH_ADMIN.DS.'tables'.DS.$table.'.php');
			$tableName = '#__virtuemart_'.$table;

			$langTable = $this->getTable($table);
			$translatableFields = $langTable->getTranslatableFields();
			if(empty($translatableFields)) continue;

			$fields = array();
			$lines = array();
			$linedefault = "NOT NULL DEFAULT ''";
			//Text has no default
			$linedefaulttext = "NOT NULL";

			$fields[$tblKey] = 'int(1) UNSIGNED NOT NULL';
// 			vmdebug('createLanguageTables ',$translatableFields);
			//set exceptions from normal shema here !
			//Be aware that you can use this config settings, when declaring them in the virtuemart.cfg
			if(VmConfig::get('dblayoutstrict',true)){
				if($table=='products'){
					$fields['product_s_desc'] = 'varchar('.VmConfig::get('dbpsdescsize',2000).') '.$linedefault;
					$fields['product_desc'] = 'varchar('.VmConfig::get('dbpdescsize',18400).') '.$linedefault;

					$key = array_search('product_desc', $translatableFields);
					unset($translatableFields[$key]);

					$key = array_search('product_s_desc', $translatableFields);
					unset($translatableFields[$key]);

				} else if($table=='vendors'){
					//This makes too much trouble with the vendor stuff, so we use simply text for it
// 					$fields['vendor_store_desc'] = 'varchar('.VmConfig::get('dbvdescsize',1800).') '.$linedefault;
// 					$fields['vendor_terms_of_service'] = 'varchar('.VmConfig::get('dbtossize',18100).') '.$linedefault;
// 					$fields['vendor_legal_info'] = 'varchar('.VmConfig::get('dblegalsize',1100).') '.$linedefault;

					$fields['vendor_store_desc'] = 'text '.$linedefaulttext;
					$fields['vendor_terms_of_service'] = 'text '.$linedefaulttext;
					$fields['vendor_legal_info'] = 'text '.$linedefaulttext;

					$fields['vendor_letter_css'] = 'text '.$linedefaulttext;
					$fields['vendor_letter_header_html'] = "varchar(8000) NOT NULL DEFAULT '<h1>{vm:vendorname}</h1><p>{vm:vendoraddress}</p>'";
					$fields['vendor_letter_footer_html'] = "varchar(8000) NOT NULL DEFAULT '<p>{vm:vendorlegalinfo}<br />Page {vm:pagenum}/{vm:pagecount}</p>'";


					$key = array_search('vendor_store_desc', $translatableFields);
					unset($translatableFields[$key]);

					$key = array_search('vendor_terms_of_service', $translatableFields);
					unset($translatableFields[$key]);

					$key = array_search('vendor_legal_info', $translatableFields);
					unset($translatableFields[$key]);
					
					$key = array_search('vendor_letter_css', $translatableFields);
					unset($translatableFields[$key]);

					$key = array_search('vendor_letter_header_html', $translatableFields);
					unset($translatableFields[$key]);

					$key = array_search('vendor_letter_footer_html', $translatableFields);
					unset($translatableFields[$key]);

				}
			} else {
				vmdebug('dblayoutstrict false');
				$fields['vendor_terms_of_service'] = 'text '.$linedefaulttext;
				$key = array_search('vendor_terms_of_service', $translatableFields);
				unset($translatableFields[$key]);

				$fields['vendor_legal_info'] = 'text '.$linedefaulttext;
				$key = array_search('vendor_legal_info', $translatableFields);
				unset($translatableFields[$key]);
			}

// 		vmdebug('createLanguageTables ',$translatableFields);
			foreach($translatableFields as $k => $name){
				if(strpos($name,'name') !==false ){
					$fields[$name] = 'varchar('.VmConfig::get('dbnamesize',180).') '.$linedefault;
				} else if(strpos($name,'metadesc')!==false ){
					$fields[$name] = 'varchar('.VmConfig::get('dbmetasize',400).') '.$linedefault;
				} else if(strpos($name,'metatitle')!==false ){
					$fields[$name] = 'varchar('.VmConfig::get('dbmetasize',100).') '.$linedefault;
				} else if(strpos($name,'metakey')!==false ){
					$fields[$name] = 'varchar('.VmConfig::get('dbmetasize',400).') '.$linedefault;
				} else if(strpos($name,'metaauthor')!==false ){
					$fields[$name] = 'varchar(64) '.$linedefault;
				} else if(strpos($name,'slug')!==false ){
					$fields[$name] = 'varchar('.VmConfig::get('dbslugsize',192).') '.$linedefault;
					$slug = true;
				}else if(strpos($name,'phone')!==false) {
					$fields[$name] = 'varchar(26) '.$linedefault;
				}else if(strpos($name,'desc')!==false) {
					if(VmConfig::get('dblayoutstrict',true)){
						$fields[$name] = 'varchar('.VmConfig::get('dbdescsize',19000).') '.$linedefault;
					} else {
						$fields[$name] = 'text '.$linedefaulttext;
					}

				} else {
					$fields[$name] = 'varchar(255) '.$linedefault;
				}

			}
			$lines[0] =	$fields;


			if($slug){
				$lines[1][$tblKey] = 'PRIMARY KEY (`'.$tblKey.'`)';
				$lines[1]['slug'] = 'UNIQUE KEY `slug` (`slug`)';
				//a slug must anyway be unique and so one index for both is faster
				//testing revealed that it is slower
				//$lines[1][$tblKey] = 'PRIMARY KEY (`'.$tblKey.'`,`slug`)';
			} else {
				$lines[1][$tblKey] = 'PRIMARY KEY (`'.$tblKey.'`)';
			}

			$table[3] = '';
			foreach($langs as $lang){
				// 				$lang = strtr($lang,'-','_');
				$lang = strtolower(strtr($lang,'-','_'));
				$tbl_lang = $tableName.'_'.$lang;
				$langTables[$tbl_lang] = $lines;
			}

			$i++;

		}
		$this->reCreaPri = 1;
		$ret = $this->updateMyVmTables($langTables);
		// 		vmTime('done creation of lang tables');
		return $ret;

	}

	public function getTablesBySql($file){

		if(!file_exists($file)){
			vmError('Could not execute sql, could not find file '.$file);
			return false;
		}
		$data = fopen($file, 'r');

		$tables = array();
		$tableDefStarted = false;
		while ($line = fgets ($data)) {
			$line = trim($line);
			if (empty($line)) continue; // Empty line

			if (strpos($line, '#') === 0) continue; // Commentline
			if (strpos($line, '--') === 0) continue; // Commentline

			if(strpos($line,'CREATE TABLE IF NOT EXISTS')!==false){
				$tableDefStarted = true;
				$fieldLines = array();
				$tableKeys = array();
				$start = strpos($line,'`');

				$tablename = trim(substr($line,$start+1,-3));
				// 				vmdebug('my $tablename ',$start,$end,$line);
			} else if($tableDefStarted && (strpos($line,'KEY')!==false or strpos($line,'UNIQUE')!==false)){

				$start = strpos($line,"`");
				$temp = substr($line,$start+1);
				$end = strpos($temp,"`");
				$keyName = substr($temp,0,$end);

				if(strrpos($line,',')==strlen($line)-1){
					$line = substr($line,0,-1);
				}
				$tableKeys[$keyName] = $line;

			} else if(strpos($line,'ENGINE')!==false){
				$tableDefStarted = false;

				$tl = strtolower($line);
				if(strpos($tl,'myisam')!==false){
					$engine = 'MyISAM';
				} else if(strpos($tl,'innodb')!==false){
					$engine = 'InnoDB';
				} else if(strpos($tl,'memory')!==false){
					$engine = 'Memory';
				} else {
					$engine = '';
				}

				$start = strpos($line,"COMMENT='");
				$temp = substr($line,$start+9);
				$end = strpos($temp,"'");
				$comment = substr($temp,0,$end);

				$tables[$tablename] = array($fieldLines, $tableKeys,$comment,$engine);
			} else if($tableDefStarted){

				$start = strpos($line,"`");
				$temp = substr($line,$start+1);
				$end = strpos($temp,"`");
				$keyName = substr($temp,0,$end);

				if(empty($keyName)){
					$m = 'getTablesBySql empty $keyName line: '.$line .' file: '. $file;
					//vmError($m,$m);
					//$tableDefStarted = false;
				} else {

					$line = trim(substr($line,$end+2));
					if(strrpos($line,',')==strlen($line)-1){
						$line = substr($line,0,-1);
					}

					$fieldLines[$keyName] = $line;
				}
			}
		}
		fclose($data);
		return $tables;
	}

	public function updateMyVmTables($file = 0, $like ='_virtuemart_'){

		if(empty($file)){
			$file = VMPATH_ADMIN.DS.'install'.DS.'install.sql';
		}

		if(is_array($file)){
			$tables = $file;
		} else {

			$tables = $this->getTablesBySql($file);
		}

 		//vmdebug('updateMyVmTables $tables',$tables); return false;
		// 	vmdebug('Parsed tables',$tables); //return;
		$this->_db->setQuery('SHOW TABLES LIKE "%'.$like.'%"');
		if (!$existingtables = $this->_db->loadColumn()) {
			vmError('updateMyVmTables '.$this->_db->getErrorMsg());
			return false;
		}

		$i = 0;
		$demandedTables = array();
		//TODO ignore admin menu table
		foreach ($tables as $tablename => $table){

// 			if($i>2) continue;

			$tablename = str_replace('#__',$this->_prefix,$tablename);
			$demandedTables[] = $tablename;
			if(in_array($tablename,$existingtables)){

				/*$q = 'LOCK TABLES `'.$tablename.'` WRITE';
				$this->_db->setQuery($q);
				$this->_db->execute();*/

				if(!isset($table[3])) $table[3] = 'MyISAM';

				if(empty($this->reCreaKey)) $table[1] = false;
				$this->alterColumns($tablename,$table);

				usleep(10);
				$this->optimizeTable($tablename);
				usleep(10);

				/*$q = 'UNLOCK TABLES';
				$this->_db->setQuery($q);
				$this->_db->execute();*/
			} else {
				$this->createTable($tablename,$table);
			}
			$i++;

		}

	}

	public function optimizeTable($tablename){
		//There is a bug, which can make your table unaccessable
		/*$q ='OPTIMIZE TABLE '.$tablename;
		$this->_db->setQuery($q);
		$res1 = $this->_db->execute();*/

		$q = 'Show Index FROM '.$tablename;
		$this->_db->setQuery($q);
		$res2 = $this->_db->loadAssocList();
		//vmdebug('Optimised table '.$tablename,$res1,$res2);
		/*foreach($res2 as $m){
			vmdebug($tablename.': '.$m['Key_name'].' '.$m['Cardinality']);
		}*/
	}

	public function createTable($tablename,$table){

		$q = 'CREATE TABLE IF NOT EXISTS `'.$tablename.'` (
				';
		foreach($table[0] as $fieldname => $alterCommand){
			$q .= '`'.$fieldname.'` '.$alterCommand.',
			';
		}

		foreach($table[1] as $name => $value){
				$q .= $value.',
						';
		}

		$q = substr(trim($q),0,-1);
		$comment = '';
		if(!empty($table[3])){
			$comment = " COMMENT='".$table[3]."'";
		}
		$q .= ") ENGINE=MyISAM  DEFAULT CHARSET=utf8 ".$comment." AUTO_INCREMENT=1 ;";

		$this->_db->setQuery($q);
		if(!$this->_db->execute()){
			vmError('createTable ERROR :'.$this->_db->getErrorMsg() );
		} else {
			vmInfo('created table '.$tablename);
		}
// 		$this->_app->enqueueMessage($q);
	}

	public function dropTables($todelete){
		if(empty($todelete)) return;
		$q = 'DROP ';// .implode(',',$todelete);
		foreach($todelete as $tablename){
			$tablename = str_replace('#__',$this->_prefix,$tablename);
			$q .= $tablename.', ';
		}
		$q = substr($q,0,-1);

		// 		$this->_db->setQuery($q);
		// 		if(!$this->_db->query()){
		// 			$this->_app->enqueueMessage('dropTables ERROR :'.$this->_db->getErrorMsg() );
		// 		}
		$this->_app->enqueueMessage($q);
	}


	private function alterKey($tablename,$keys){

		if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
			vmWarn('compareUpdateTable alterKey not finished, please rise execution time and update tables again');
			return false;
		}

		$demandedFieldNames = array();
		foreach($keys as $i=>$line){
			$demandedFieldNames[] = $i;
		}

		$query = "SHOW INDEXES  FROM `".$tablename."` ";	//SHOW {INDEX | INDEXES | KEYS}
		$this->_db->setQuery($query);
		$eKeys = $this->_db->loadObjectList();

		$tkeys=array();
		$keyT = $keys;

		//Lets check if something changed
		foreach($keyT as $name =>$value) {
			$k = new stdClass();
			if($p = strpos( $value, 'PRIMARY' ) !== false) {
				if(strpos( $value, '`' ) !== false) {
					$spl = explode('`', $value);
					$k->Key_name =  'PRIMARY';
					$k->Column_name = $spl[1];
					$k->Non_unique=0;
					$tkeys[$k->Key_name] = $k;
					continue;
				}
			}

			if($p = strpos( $value, 'UNIQUE' ) !== false) {
				$k->Non_unique=0;
				$value = trim(substr($value,$p));
			} else {
				$k->Non_unique=1;
			}

			if(strpos( $value, '`' ) !== false) {
				$spl = explode('`', $value);

				//We dont prevent drop and add of double keys
				if(!isset($spl[5])) {

					$k->Key_name = $spl[1];
					$k->Column_name = $spl[3];
					$tkeys[$k->Column_name] = $k;
				}
				/*$tkeys[$k->Column_name] = $k;
				if(isset($spl[5])) {
					$k2 = clone($k);

					$k2->Column_name = $spl[5];
					$tkeys[$k2->Column_name] = $k2;
				}*/
			}
		}

		foreach($eKeys as $i => $eKey) {

			if($eKey->Key_name == 'PRIMARY'){
				$t = $tkeys['PRIMARY'];
				if($eKey->Column_name==$t->Column_name and $eKey->Non_unique==$t->Non_unique){
					unset($keys[$eKey->Column_name]);
					unset($eKeys[$i]);
				} else {
					vmdebug('my primary',$eKey);
				}
			} else {

				if(isset($tkeys[$eKey->Column_name])) {
					$t = $tkeys[$eKey->Column_name];
					if($eKey->Key_name==$t->Key_name and $eKey->Non_unique==$t->Non_unique){
						unset($keys[$eKey->Key_name]);
						unset($eKeys[$i]);
					} else {
						//vmdebug('my $tkeys',$eKey,$t,$keys[$eKey->Key_name]);
					}
				}

			}
		}

		$ok=true;

		$primaryFound = false;
		foreach($eKeys as $i => $eKey) {

			if(strpos( $eKey->Key_name, 'PRIMARY' ) !== false) {
				$primaryFound = true;
				continue;
			}
			if(empty($eKey->Key_name)) continue;

			$query = "SHOW INDEXES  FROM `".$tablename."` ";
			$this->_db->setQuery($query);
			$eKeyNamesNOW = $this->_db->loadColumn(2);

			if(!in_array($eKey->Key_name,$eKeyNamesNOW)) continue;

			$query = 'ALTER TABLE `'.$tablename.'` DROP INDEX `'.$eKey->Key_name.'` ';

			$this->_db->setQuery($query);

			$ok =$this->_db->execute();
			if(!$ok){
				$this->_app->enqueueMessage('alterTable DROP INDEX '.$tablename.'.'.$eKey->Key_name.' :'.$this->_db->getErrorMsg() );
			} else {
				//$dropped++;
				//vmdebug('alterKey: Dropped KEY `'.$eKey->Key_name.'` in table `'.$tablename.'`');
			}
		}

		foreach($keys as $name =>$value){

			if($primaryFound and strpos($value,'PRIMARY')!==false){
				if(strpos($value,'PRIMARY')!==false){
					continue;
				}
			}

			$query = "ALTER TABLE `".$tablename."` ADD ".$value ;
			$action = 'ADD';

			if(!empty($query)){
				$this->_db->setQuery($query);
				if(!$this->_db->execute()){
					$this->_app = JFactory::getApplication();
					$this->_app->enqueueMessage('alterKey '.$action.' INDEX '.$name.': '.$this->_db->getErrorMsg() );
				} else {
 					//vmdebug('alterKey: a:'.$action.' KEY `'.$name.'` in table `'.$tablename.'` '.$this->_db->getQuery());
				}
			}
		}

	}

	function reCreateKeyByTableAttributes($keyAttribs){

		$oldkey ='';

		if(!empty($keyAttribs->Key_name) && !empty($keyAttribs->Column_name) ){
			if(!$keyAttribs->Non_unique){
				$oldkey = 'UNIQUE ';
				//$oldkey = 'PRIMARY KEY (`'.$keyAttribs->Column_name.'`)';
			}
			//else {
				$oldkey .= 'KEY `'.$keyAttribs->Key_name.'` (`'.$keyAttribs->Column_name.'`)';
			//}
		} else {
			vmdebug('reCreateKeyByTableAttributes $keyAttribs empty?',$keyAttribs);
		}

		// 		if(empty($keyAttribs->Cardinality)){
		// 			vmdebug('Cardinality : '.$keyAttribs->Cardinality.' '.$oldkey);
		// 		}

		return $oldkey;
	}

	/**
	 * @author Max Milbers
	 * @param unknown_type $tablename
	 * @param unknown_type $fields
	 * @param unknown_type $command
	 */
	public function alterColumns($tablename,$tableDef){

		$after =' FIRST';
		$dropped = 0;
		$altered = 0;
		$added = 0;
		$toRepeat = false;
		$this->_app = JFactory::getApplication();


		$fields = isset($tableDef[0]) ? $tableDef[0]:false;
		$keys = isset($tableDef[1]) ? $tableDef[1]:false;
		$engine = isset($tableDef[3]) ? $tableDef[3]:false;

		$demandFieldNames = array();
		foreach($fields as $i=>$line){
			$demandFieldNames[] = $i;
		}

		$q = 'SHOW FULL COLUMNS  FROM `'.$tablename.'` ';	//$q = 'SHOW CREATE TABLE '.$this->_tbl;
		$this->_db->setQuery($q);
		$fullColumns = $this->_db->loadObjectList();
		$columns = $this->_db->loadColumn(0);

		//vmdebug('alterColumns',$fullColumns);
		//Attention user_infos is not in here, because it an contain customised fields. #__virtuemart_order_userinfos #__virtuemart_userinfos
		//This is currently not working as intended, because the config is not deleted before, it is better to create an extra command for this, when we need it later


		foreach($fields as $fieldname => $alterCommand){

			if((microtime(true)-$this->starttime) >= ($this->maxScriptTime)){
				vmWarn('alterColumns alterKey not finished, please rise execution time and update tables again');
				return false;
			}
			$query='';
			$action = '';

			if(empty($alterCommand)){
				vmdebug('empty alter command '.$fieldname);
				continue;
			}

			if(in_array($fieldname,$columns)){

				$key=array_search($fieldname, $columns);
				$oldColumn = $this->reCreateColumnByTableAttributes($fullColumns[$key]);

// 					while (strpos($oldColumn,'  ')){
// 						str_replace('  ', ' ', $oldColumn);
// 					}
				while (strpos($alterCommand,'  ')){
					$alterCommand = str_replace('  ', ' ', trim($alterCommand));
				}

				$oldColumn = strtoupper($oldColumn);
				$alterCommand = strtoupper(trim($alterCommand));

				if ($oldColumn != $alterCommand ) {
					$pr = '';
					//If the field is an auto_increment, we add to the sql the creation of the primary key
					if( (strpos($alterCommand,'AUTO_INCREMENT')!==false xor strpos($oldColumn,'AUTO_INCREMENT')!==false)){
						$pr = ', ADD PRIMARY KEY (`'.$fieldname.'`)';
						//This function drops the key only if existing
						$this->dropPrimaryKey($tablename);
					}

					$query = 'ALTER TABLE `'.$tablename.'` CHANGE COLUMN `'.$fieldname.'` `'.$fieldname.'` '.$alterCommand.' '.$after.$pr;
					$action = 'CHANGE';
					$altered++;
					vmdebug('alterColumns '.$tablename.' from to,',$fieldname,$oldColumn,$alterCommand);
				}
			}
			else {
				$pr = '';
				if(strpos($alterCommand,'AUTO_INCREMENT')!==false ){
					$pr = ', ADD PRIMARY KEY (`'.$fieldname.'`)';
					$this->dropPrimaryKey($tablename);
				}
				$query = 'ALTER TABLE `'.$tablename.'` ADD `'.$fieldname.'` '.$alterCommand.' '.$after.$pr;
				$action = 'ADD';
				$added++;
 				vmdebug('alterColumns ADD '.$query);
			}

			if (!empty($query)) {
				$this->_db->setQuery($query);
				$msg = 'alterTable '.$action.' '.$tablename.'.'.$fieldname;
				if(!$this->_db->execute() ){
					vmError( $msg, $msg.$query );
				} else {
					vmInfo( $msg );
				}
			}
			$after = ' AFTER `'.$fieldname.'` ';
		}

		if($keys){
			$this->alterKey($tablename,$keys,false);
			if($toRepeat){
				vmdebug('Created keys, writing now field with autoincrement',$tablename,$toRepeat);
				$this->alterColumns($tablename,$toRepeat);
			}
		}

		if(VmConfig::get('updEngine',true)){
			$q = 'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_NAME = "'.$tablename.'" ';
			$this->_db->setQuery( $q );
			$exEngine = $this->_db->loadResult();

			if(!empty($engine) and strtoupper( $exEngine ) != strtoupper( $engine )) {
				$q = 'ALTER TABLE '.$tablename.' ENGINE='.$engine;
				$this->_db->setQuery( $q );
				$this->_db->execute();
				vmdebug( 'Changed engine '.$exEngine.' of table '.$tablename.' to '.$engine, $exEngine );
			}
		}

		if($dropped != 0 or $altered !=0 or $added!=0){
			$this->_app->enqueueMessage('Table updated: Tablename '.$tablename.' dropped: '.$dropped.' altered: '.$altered.' added: '.$added);
			$err = $this->_db->getErrorMsg();
			if(!empty($err)){
				vmError('Tableupdater updating table '.$tablename.' throws error '.$err);
			}
		}

		return true;

	}

	/**
	 * This function drops the key only if existing and removes before the auto_increment attribute from the column
	 * @author Max Milbers
	 * @param $tablename
	 * @return bool
	 */
	public function dropPrimaryKey($tablename){

		$q = 'SHOW INDEXES FROM `'.$tablename.'` WHERE Key_name = "PRIMARY";';
		$this->_db->setQuery($q);
		$this->_db->execute();
		$res = $this->_db->loadAssoc();

		if($res){
			//We check if there is an auto_increment field and disable it
			$q = 'SHOW FULL COLUMNS  FROM `'.$tablename.'` WHERE Extra = "auto_increment";';	//$q = 'SHOW CREATE TABLE '.$this->_tbl;
			$this->_db->setQuery($q);
			$column = $this->_db->loadObject();
			if($column){
				$old = $this->reCreateColumnByTableAttributes($column);
				$old = trim(str_replace('AUTO_INCREMENT', '',$old));
				$q = 'ALTER TABLE `'.$tablename.'` CHANGE COLUMN `'.$column->Field.'` `'.$column->Field.'` '.$old;
				$this->_db->setQuery($q);
				if(!$this->_db->execute() ){
					vmError( 'Could not alter auto_increment column dropping primary '.$q );
				}
			}

			$q = 'ALTER TABLE `'.$tablename.'`	DROP PRIMARY KEY;';
			$this->_db->setQuery($q);

			if(!$this->_db->execute() ){
				vmError( 'Could not drop Primary for CHANGE '.$q );
			} else {
				vmdebug('dropPrimaryKey '.$tablename);
			}
		}
		return true;
	}

	public function deleteColumns($tablename,$fields){

		$dropped = 0;
		$q = 'SHOW FULL COLUMNS  FROM `'.$tablename.'` ';	//$q = 'SHOW CREATE TABLE '.$this->_tbl;
		$this->_db->setQuery($q);
		//$fullColumns = $this->_db->loadObjectList();
		$columns = $this->_db->loadColumn(0);

		$demandFieldNames = array();
		foreach($fields as $i=>$line){
			$demandFieldNames[] = $i;
		}

		$upDelCols = (int) VmConfig::get('updelcols',0);
		if($upDelCols==1 and !($tablename==$this->_prefix.'virtuemart_userfields' or $tablename==$this->_prefix.'virtuemart_userinfos' or $tablename==$this->_prefix.'virtuemart_order_userinfos')){

			foreach($columns as $fieldname){

				if(!in_array($fieldname, $demandFieldNames)){
					$query = 'ALTER TABLE `'.$tablename.'` DROP COLUMN `'.$fieldname.'` ';
					$action = 'DROP';
					$dropped++;

					$this->_db->setQuery($query);
					if(!$this->_db->execute()){
						vmError('alterTable '.$action.' '.$tablename.'.'.$fieldname.' :'.$this->_db->getErrorMsg() );
					}
				}
			}
		}
		return $dropped;
	}

	private function reCreateColumnByTableAttributes($fullColumn){

		$oldColumn = $fullColumn->Type;

		if(!empty($fullColumn->Null)){
			$oldColumn .= $this->notnull($fullColumn->Null).$this->getdefault($fullColumn->Default);
		}
		$oldColumn .= $this->formatExtra($fullColumn->Extra).$this->formatComment($fullColumn->Comment);

		return trim($oldColumn);
	}

	private function formatComment($comment){
		if(!empty($comment)){
			return ' COMMENT \''.$comment.'\'';
		} else {
			return '';
		}

	}

	private function notnull($string){
		if ($string=='NO') {
			return  ' NOT NULL';
		} else {
			return '';
		}
	}

	private function formatExtra($extra){
		if (!empty($extra)) {
			return ' '.strtoupper(trim($extra));
		} else {
			return '';
		}
	}

	private function primarykey($string){

		if ($string=='PRI') {
			return  ' AUTO_INCREMENT';
		} else {
			return '';
		}
	}

	private function getdefault($string){
		if (isset($string)) {
			if(strpos($string,'CURRENT_TIMESTAMP')!==FALSE){
				return  " DEFAULT ".trim($string);
			} else {
				return  " DEFAULT '".trim($string)."'";
			}

		} else {
			return '';
		}
	}

	function loadCountListContinue($q,$startLimit,$maxItems,$msg){

		$continue = true;
		$this->_db->setQuery($q);
		if(!$this->_db->execute()){
			vmError($msg.' db error '. $this->_db->getErrorMsg());
			vmError($msg.' db error '. $this->_db->getQuery());
			$entries = array();
			$continue = false;
		} else {
			$entries = $this->_db->loadAssocList();
			$count = count($entries);
			vmInfo($msg. ' found '.$count.' vm1 entries for migration ');
			$startLimit += $maxItems;
			if($count<$maxItems){
				$continue = false;
			}
		}

		return array($entries,$startLimit,$continue);
	}
}


