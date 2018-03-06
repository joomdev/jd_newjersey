<?php 
/**
*
* Translate controller
*
* @package	VirtueMart
* @subpackage Translate
* @author Patrick Kohl
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 2, see COPYRIGHT.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: translate.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


if(!class_exists('VmController'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Translate Controller
 *
 * @package    VirtueMart
 * @subpackage Translate
 * @author Patrick Kohl
 */
class VirtuemartControllerTranslate extends VmController {

	var $check 	= null;
	var $fields = null;


	function __construct() {
		parent::__construct();

	}
	/* stAn - if this function was in the model, it could be public as well */
	
	private function getData($id, $lang, $viewKey, $dblang, $json) {

		$tables = array ('category' =>'categories','product' =>'products','manufacturer' =>'manufacturers','manufacturercategories' =>'manufacturercategories','vendor' =>'vendors', 'paymentmethod' =>'paymentmethods', 'shipmentmethod' =>'shipmentmethods');
		$tableName = '#__virtuemart_'.$tables[$viewKey].'_'.$dblang;
 
		$m = VmModel::getModel('coupon');
		$table = $m->getTable($tables[$viewKey]);
		if (empty($table)) {
		    $json['fields'] = 'error' ;
			$json['msg'] = 'Table not found '.$viewKey;
			return $json;
		}
		//Todo create method to load lang fields only
		$table->load($id);

		$vs = $table->loadFieldValues();
		$lf = $table->getTranslatableFields();

		$json['fields'] = array();
		foreach($lf as $v){
			if(isset($vs[$v])){
				$json['fields'][$v] = $vs[$v];
			}
		}

		//if ($json['fields'] = $db->loadAssoc()) {
		if ($table->getLoaded()) {
			$json['structure'] = 'filled' ;
			$json['msg'] = vmText::_('COM_VIRTUEMART_SELECTED_LANG').':'.$lang;
			$json['byfallback'] = $table->_loadedWithLangFallback;

		} else {
			$db = JFactory::getDBO();

			$json['structure'] = 'empty' ;
			$db->setQuery('SHOW COLUMNS FROM '.$tableName);
			$tableDescribe = $db->loadAssocList();
			array_shift($tableDescribe);
			$fields=array();
			foreach ($tableDescribe as $key =>$val) $fields[$val['Field']] = $val['Field'] ;
			$json['fields'] = $fields;
			VmLanguage::loadJLang('com_virtuemart');
			$json['msg'] = vmText::sprintf('COM_VIRTUEMART_LANG_IS_EMPTY',$lang ,vmText::_('COM_VIRTUEMART_'.strtoupper( $viewKey)) ) ;
		}
		return $json; 
	}
	/**
	 * Paste the table  in json format
	 *
	 */
	public function paste() {

		// TODO Test user ?
		$json= array();

		
		if (!vRequest::vmCheckToken(-1)) {
			$json['fields'] = 'error' ;
			$json['msg'] = 'Invalid Token';
			$json['structure'] = 'empty' ;
			echo json_encode($json) ;
			jexit(  );
		}

		$lang = vRequest::getVar('lg');
		$langs = VmConfig::get('active_languages',array(VmConfig::$jDefLang)) ;

		if (!in_array($lang, $langs) ) {
			$json['msg'] = 'Invalid language ! '.$lang;
			$json['langs'] = $langs ;
			echo json_encode($json) ;
			jexit( );
		}
		vmLanguage::setLanguageByTag($lang);
		/*$lang = strtolower( $lang);

		$dblang= strtr($lang,'-','_');
		VmConfig::$vmlang = $dblang;*/
		
		//$id = vRequest::getInt('id',0);
		$id = vRequest::getVar('id',0);
		if (is_array($id)) {
			if (count($id) == 1) {
				 $id = (int)reset($id); 
			}
			else {
			foreach ($id as $k=>$v) { 
			  $id[$k] = (int)$v; 
			  if (empty($id[$k])) unset($id[$k]); 
			}
			}
		}
		else {
			$id = (int)$id; 
		}

		$viewKey = vRequest::getCmd('editView');
		// TODO temp trick for vendor
		if ($viewKey == 'vendor') $id = 1 ;
		
		
		$tables = array ('category' =>'categories','product' =>'products','manufacturer' =>'manufacturers','manufacturercategories' =>'manufacturercategories','vendor' =>'vendors', 'paymentmethod' =>'paymentmethods', 'shipmentmethod' =>'shipmentmethods');

		if ( !array_key_exists($viewKey, $tables) ) {
			$json['msg'] ="Invalid view ". $viewKey;
			echo json_encode($json);
			jExit();
		}
		
		if (!is_array($id)) {
			$json = $this->getData($id, $lang, $viewKey, VmConfig::$vmlang, $json);
		}
		else {
			$json['multiple'] = array(); 
			foreach ($id as $myid) {
				$tomerge =  array();
				$tomerge = $this->getData($myid, $lang, $viewKey, VmConfig::$vmlang,$tomerge);
				$tomerge['requested_id'] = $myid;
				$json['lang'] =  VmConfig::$vmlang;
				$json['multiple'][] = $tomerge;
			}
			
			
		}

		echo vmJsApi::safe_json_encode($json);
		jExit();

	}


}

//pure php no tag
