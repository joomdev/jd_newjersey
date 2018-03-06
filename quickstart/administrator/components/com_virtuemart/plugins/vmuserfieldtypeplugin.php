<?php

/**
 * Abstract plugin class to extend the userfields
 *
 * @version $Id: vmuserfieldplugin.php 4634 2011-11-09 21:07:44Z Milbo $
 * @package VirtueMart
 * @subpackage vmplugins
 * @copyright Copyright (C) 2011-2011 VirtueMart Team - All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL 2,
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 *
 * @author Max Milbers
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!class_exists('vmPlugin')) require(VMPATH_PLUGINLIBS .'/vmplugin.php');

abstract class vmUserfieldPlugin extends vmPlugin {

	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
	 * add params fields in object
	 */
	function AddUserfieldParameter($params){
		if(is_array($params)){
			vmTable::bindParameterable($this,'userfield_params',$params);
		} else {
			$this->userfield_params = $params;
			vmTable::bindParameterable($this,'userfield_params',$this->_varsToPushParam);
		}
	}

	/**
	 * add params fields in object by name
	 */
	function addUserfieldParameterByPlgName($plgName){

		if(empty($this->_db)) $this->_db = JFactory::getDBO();
		$q = 'SELECT `userfield_params` FROM `#__virtuemart_userfields` WHERE `type` = "plugin' . $plgName .'"';
		$this->_db->setQuery($q);
		$this->userfield_params = $this->_db->loadResult();
		vmTable::bindParameterable($this,'userfield_params',$this->_varsToPushParam);
	}

}
