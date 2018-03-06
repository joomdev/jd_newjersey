<?php
/**
*
* Data module for shop countries
*
* @package	VirtueMart
* @subpackage Country
* @author Max Milbers
* @author RickG
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: country.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel')) require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for shop countries
 *
 * @package	VirtueMart
 * @subpackage Country
 */
class VirtueMartModelCountry extends VmModel {

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('countries');
		array_unshift($this->_validOrderingFieldName,'country_name');
		$this->_selectedOrdering = 'country_name';
		$this->_selectedOrderingDir = 'ASC';

	}

    /**
     * Retreive a country record given a country code.
     *
     * @author RickG, Max Milbers
     * @param string $code Country code to lookup
     * @return object Country object from database
     */
    static function getCountryByCode($code) {

		if(empty($code)) return false;
		$db = JFactory::getDBO();

		$countryCodeLength = strlen($code);
		switch ($countryCodeLength) {
			case 2:
				$fieldname = 'country_2_code';
			break;
			case 3:
				$fieldname = 'country_3_code';
			break;
			default:
				$fieldname = 'country_name';
		}

		static $countries = array();

		if(!isset($countries[$code])){
			$query = 'SELECT *';
			$query .= ' FROM `#__virtuemart_countries`';
			$query .= ' WHERE `' . $fieldname . '` = "' . $db->escape ($code) . '"';
			$db->setQuery($query);
			$countries[$code] = $db->loadObject();
		}

		return $countries[$code];
    }

    /**
     * Retrieve a list of countries from the database.
     *
     * @author RickG
     * @author Max Milbers
     * @param string $onlyPublished True to only retrieve the publish countries, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of country objects
     */
    function getCountries($onlyPublished=true, $noLimit=false, $filterCountry = false) {

		static $countries = array();
		$where = array();
		$this->_noLimit = $noLimit;

		if ($onlyPublished) $where[] = '`published` = 1';

		if($filterCountry){
			$db = JFactory::getDBO();
			$filterCountryS = '"%' . $db->escape( $filterCountry, true ) . '%"' ;
			$where[] = '`country_name` LIKE '.$filterCountryS.' OR `country_2_code` LIKE '.$filterCountryS.' OR `country_3_code` LIKE '.$filterCountryS;
		}

		$whereString = '';
		if (count($where) > 0) $whereString = ' WHERE '.implode(' AND ', $where) ;

		$ordering = $this->_getOrdering();
		$hash = $filterCountry.(int)$onlyPublished.$ordering.(int)$noLimit;
		if(!isset($countries[$hash])){
			$countries[$hash] = $this->_data = $this->exeSortSearchListQuery(0,'*',' FROM `#__virtuemart_countries`',$whereString,'',$ordering);
		}
		return $countries[$hash];
    }

	function store(&$data){
		if(!vmAccess::manager('country')){
			vmWarn('Insufficient permissions to store country');
			return false;
		}
		return parent::store($data);
	}

	function remove($ids){
		if(!vmAccess::manager('country')){
			vmWarn('Insufficient permissions to remove country');
			return false;
		}
		return parent::remove($ids);
	}

}

//no closing tag pure php
