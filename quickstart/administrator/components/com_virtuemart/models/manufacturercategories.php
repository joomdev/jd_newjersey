<?php
/**
*
* Manufacturer category model
*
* @package	VirtueMart
* @subpackage Manufacturer category
* @author Patrick Kohl
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: manufacturercategories.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model class for manufacturer category
 *
 * @package	VirtueMart
 * @subpackage Manufacturer category
 * @author
 */
class VirtuemartModelManufacturercategories extends VmModel {


	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 * @author Max Milbers
	 */
	function __construct() {
		parent::__construct('virtuemart_manufacturercategories_id');
		$this->setMainTable('manufacturercategories');
		$this->addvalidOrderingFieldName(array('mf_category_name'));
	}

	/**
	 * Delete all record ids selected
     *
     * @return boolean True is the remove was successful, false otherwise.
     */
	function remove($categoryIds) {
		if(!vmAccess::manager('manufacturercategories')){
			vmWarn('Insufficient permissions to delete manufacturer category');
			return false;
		}

    	$table = $this->getTable('manufacturercategories');

    	foreach($categoryIds as $categoryId) {
       		if($table->checkManufacturer($categoryId)) {
	    		if (!$table->delete($categoryId)) {
	            		return false;
	       		}
       		}
       		else {
				vmError(get_class( $this ).'::remove '.$categoryId.' failed');
       			return false;
       		}
    	}
    	return true;
	}

	function store(&$data){
		if(!vmAccess::manager('manufacturercategories')){
			vmWarn('Insufficient permissions to store manufacturer category');
			return false;
		}
		return parent::store($data);
	}

	/**
	 * Retireve a list of countries from the database.
	 *
     * @param string $onlyPuiblished True to only retreive the published categories, false otherwise
     * @param string $noLimit True if no record count limit is used, false otherwise
	 * @return object List of manufacturer categories objects
	 */
	function getManufacturerCategories($onlyPublished=false, $noLimit=false)
	{
		$this->_noLimit = $noLimit;
		$select = '* FROM `#__virtuemart_manufacturercategories_'.VmConfig::$vmlang.'` as l';
		$joinedTables = ' JOIN `#__virtuemart_manufacturercategories` as mc using (`virtuemart_manufacturercategories_id`)';
		$where = array();
		if ($onlyPublished) {
			$where[] = ' `#__virtuemart_manufacturercategories`.`published` = 1';
		}

//		$query .= ' ORDER BY `#__virtuemart_manufacturercategories`.`mf_category_name`';

		$whereString = '';
		if (count($where) > 0) $whereString = ' WHERE '.implode(' AND ', $where) ;
		if ( vRequest::getCmd('view') == 'manufacturercategories') {
			$ordering = $this->_getOrdering();
		} else {
			$ordering = ' order by mf_category_name DESC';
		}
		return $this->_data = $this->exeSortSearchListQuery(0,$select,$whereString,$joinedTables,$ordering);

	}

	/**
	 * Build category filter
	 *
	 * @return object List of category to build filter select box
	 */
	function getCategoryFilter(){
		$db = JFactory::getDBO();
		$query = 'SELECT `virtuemart_manufacturercategories_id` as `value`, `mf_category_name` as text'
				.' FROM `#__virtuemart_manufacturercategories_'.VmConfig::$vmlang.'`';
		$db->setQuery($query);

		$categoryFilter[] = JHtml::_('select.option',  '0', '- '. vmText::_('COM_VIRTUEMART_SELECT_MANUFACTURER_CATEGORY') .' -' );
		$categoryFilter = array_merge($categoryFilter, (array)$db->loadObjectList());

		return $categoryFilter;

	}
}

// pure php no closing tag