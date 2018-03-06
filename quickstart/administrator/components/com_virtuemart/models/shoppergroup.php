<?php
/**
*
* Data model for shopper group
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus Öhler
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: shoppergroup.php 9420 2017-01-12 09:35:36Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shopper group
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus Öhler
 */
class VirtueMartModelShopperGroup extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_shoppergroup_id');
		$this->setMainTable('shoppergroups');

	}

    /**
     * Retrieve the detail record for the current $id if the data has not already been loaded.
     *
     * @author Markus Öhler
     */
    function getShopperGroup($id = 0) {

		return $this->getData($id);

	}


    /**
     * Retireve a list of shopper groups from the database.
     *
     * @author Markus Öhler
     * @param boolean $onlyPublished
     * @param boolean $noLimit True if no record count limit is used, false otherwise
     * @return object List of shopper group objects
     */
    function getShopperGroups($onlyPublished=false, $noLimit = false) {

		vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);
	    $query = 'SELECT * FROM `#__virtuemart_shoppergroups`  ';
		if($onlyPublished){
			$query .= ' WHERE `published` = "1" ';
		}
		$query .= 'ORDER BY `virtuemart_vendor_id`,`shopper_group_name` ';

		if ($noLimit) {
			$this->_data = $this->_getList($query);
		}
		else {
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

	    return $this->_data;
    }


	/**
	 *
	 * Get default shoppergroup for anonymous and non anonymous
	 * @param unknown_type $kind
	 */
	function getDefault($kind = 1, $onlyPublished = FALSE, $vendorId = 1){

		static $default = array();
		$kind = $kind + 1;
		if(!isset($default[$vendorId][$kind])){
			$q = 'SELECT * FROM `#__virtuemart_shoppergroups` WHERE `default` = "'.$kind.'" AND (`virtuemart_vendor_id` = "'.$vendorId.'" OR `shared` = "1") ';
			if($onlyPublished){
				$q .= ' AND `published`="1" ';
			}
			$db = JFactory::getDBO();
			$db->setQuery($q);

			if(!$res = $db->loadObject()){
				$app = JFactory::getApplication();
				$app->enqueueMessage('Attention no standard shopper group set '.$db->getErrorMsg());
				$default[$vendorId][$kind] = false;
			} else {
				if(!$res = $this->getShopperGroup($res->virtuemart_shoppergroup_id)){

				}
				vmLanguage::loadJLang('com_virtuemart_shoppers',TRUE);
				$res->shopper_group_name = vmText::_($res->shopper_group_name);
				$res->shopper_group_desc = vmText::_($res->shopper_group_desc);
				//vmdebug('my default shoppergroup ',$res);
				$default[$vendorId][$kind] =  $res;
			}
		}

		return $default[$vendorId][$kind];

	}

	function appendShopperGroups(&$shopperGroups,$user,$onlyPublished = FALSE,$vendorId=1){

		$this->mergeSessionSgrps($shopperGroups);

		$testshopperGroups = array();
		foreach($shopperGroups as $groupId){
			$group = $this->getData($groupId);
			if(!$group->sgrp_additional){
				$testshopperGroups[] = $groupId;
			}
		}

		if(count($testshopperGroups)<1){

			$_defaultShopperGroup = $this->getDefault($user->guest,$onlyPublished,$vendorId);
			if(!in_array($_defaultShopperGroup->virtuemart_shoppergroup_id,$shopperGroups)){
				$shopperGroups[] = $_defaultShopperGroup->virtuemart_shoppergroup_id;
			}
		}

		$this->removeSessionSgrps($shopperGroups);

	}

	function mergeSessionSgrps(&$ids){
		$session = JFactory::getSession();
		$shoppergroup_ids = $session->get('vm_shoppergroups_add',array(),'vm');

		$ids = array_merge($ids,(array)$shoppergroup_ids);
		$ids = array_unique($ids);
		//$session->set('vm_shoppergroups_add',array(),'vm');
		//vmdebug('mergeSessionSgrps',$shoppergroup_ids,$ids);
	}

	function removeSessionSgrps(&$ids){
		$session = JFactory::getSession();
		$shoppergroup_ids_remove = $session->get('vm_shoppergroups_remove',0,'vm');
		if($shoppergroup_ids_remove!==0){

			if(!is_array($shoppergroup_ids_remove)){
				$shoppergroup_ids_remove = (array) $shoppergroup_ids_remove;
			}

			foreach($shoppergroup_ids_remove as $k => $id){
				if(in_array($id,$ids)){
					$key=array_search($id, $ids);
					if($key!==FALSE){
						unset($ids[$key]);
					}
				}
			}
		}

	}

	function store(&$data){
		if(!vmAccess::manager('shoppergroup.edit')){
			vmWarn('Insufficient permissions to store shoppergroup');
			return false;
		}
		return parent::store($data);
	}

	function remove($ids){

		if(!vmAccess::manager('shoppergroup.edit')){
			vmWarn('Insufficient permissions to delete shoppergroup');
			return false;
		}

		$table = $this->getTable($this->_maintablename);

		$defaultSgId = $this->getDefault(0);
		$anonymSgId = $this->getDefault(1);
		$db = JFactory::getDBO();
		foreach($ids as $id){

			//Test if shoppergroup is default
			if($id == $defaultSgId->virtuemart_shoppergroup_id){

				$db->setQuery('SELECT shopper_group_name FROM `#__virtuemart_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
				$name = $db->loadResult();
				vmError(vmText::sprintf('COM_VIRTUEMART_SHOPPERGROUP_DELETE_CANT_DEFAULT',vmText::_($name),$id));
				continue;
			}

			//Test if shoppergroup is default
			if($id == $anonymSgId->virtuemart_shoppergroup_id){
				$db->setQuery('SELECT shopper_group_name FROM `#__virtuemart_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
				$name = $db->loadResult();
				vmError(vmText::sprintf('COM_VIRTUEMART_SHOPPERGROUP_DELETE_CANT_DEFAULT',vmText::_($name),$id));
				continue;
			}

			//Test if shoppergroup has members
			$db->setQuery('SELECT * FROM `#__virtuemart_vmuser_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
			if($db->loadResult()){
				$db->setQuery('SELECT shopper_group_name FROM `#__virtuemart_shoppergroups`  WHERE `virtuemart_shoppergroup_id` = "'.(int)$id.'"');
				$name = $db->loadResult();
				vmError(vmText::sprintf('COM_VIRTUEMART_SHOPPERGROUP_DELETE_CANT_WITH_MEMBERS',vmText::_($name),$id));
				continue;
			}

			if (!$table->delete($id)) {
				vmError(get_class( $this ).'::remove '.$id);
				return false;
		    }
		}

		return true;
	}

	/**
	 * Retrieves the Shopper Group Info of the SG specified by $id
	 *
	 * @param int $id
	 * @param boolean $default_group
	 * @return array
	 */
  	static function getShoppergroupById($id, $default_group = false) {
    	$virtuemart_vendor_id = 1;
    	$db = JFactory::getDBO();

    	$q =  'SELECT `#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`, `#__virtuemart_shoppergroups`.`shopper_group_name`, `default` AS default_shopper_group FROM `#__virtuemart_shoppergroups`';

    	if (!empty($id) && !$default_group) {
      		$q .= ', `#__virtuemart_vmuser_shoppergroups`';
      		$q .= ' WHERE `#__virtuemart_vmuser_shoppergroups`.`virtuemart_user_id`="'.(int)$id.'" AND ';
      		$q .= '`#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`=`#__virtuemart_vmuser_shoppergroups`.`virtuemart_shoppergroup_id`';
    	}
    	else {
    		$q .= ' WHERE `#__virtuemart_shoppergroups`.`virtuemart_vendor_id`="'.(int)$virtuemart_vendor_id.'" AND `default`="2"';
    	}

    	$db->setQuery($q);
    	return $db->loadAssocList();
  	}

}
// pure php no closing tag