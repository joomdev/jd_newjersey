<?php
/**
*
* Category Model
*
* @package	VirtueMart
* @subpackage Category
* @author Max Milbers
* @author jseros, RickG
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: category.php 9805 2018-03-23 09:40:01Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if(!class_exists('VmModel'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmmodel.php');

/**
 * Model for product categories
 */
class VirtueMartModelCategory extends VmModel {

	private $_category_tree;
	public $_cleanCache = true ;

	static $_validOrderingFields = array('category_name','c.ordering,category_name','category_description','c.category_shared','c.published');

	/**
	 * constructs a VmModel
	 * setMainTable defines the maintable of the model
	 */
	function __construct() {
		parent::__construct();
		$this->setMainTable('categories');

		$this->addvalidOrderingFieldName(self::$_validOrderingFields);

		$toCheck = VmConfig::get('browse_cat_orderby_field','category_name');
		if(!in_array($toCheck, $this->_validOrderingFieldName)){
			$toCheck = 'category_name';
		}
		$this->_selectedOrdering = $toCheck;
		$this->_selectedOrderingDir = VmConfig::get('cat_brws_orderby_dir', 'ASC');
		$this->setToggleName('shared');
	}


	public function checkIfCached($virtuemart_category_id,$childs=TRUE){
		return !empty($this->_cache[$virtuemart_category_id][(int)$childs]);
	}

	/**
	 * Retrieve the detail record for the current $id if the data has not already been loaded.
	 *
	 * @author RickG, jseros, Max Milbers
	 */
	public function getCategory($virtuemart_category_id=0,$childs=TRUE, $fe = true){

		if(!empty($virtuemart_category_id)) $this->_id = (int)$virtuemart_category_id;
		$childs = (int)$childs;
		if (empty($this->_cache[$this->_id][$childs.VmLanguage::$currLangTag])) {

			if($childs and !empty($this->_cache[$this->_id][0])){
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag] = clone($this->_cache[$this->_id][0]);
			} else if(!$childs and !empty($this->_cache[$this->_id][1])){
				$t = clone($this->_cache[$this->_id][1]);
				$t->children = false;
				$t->haschildren = null;
				$t->productcount = false;
				$t->parents = false;
				$this->_cache[$this->_id][0] = $t;
				//vmdebug('Use category already loaded with children');
				return $t;
			} else {
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag] = $this->getTable('categories');
				if(!empty($this->_id)){
					$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->load($this->_id);

					$xrefTable = $this->getTable('category_medias');
					$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->virtuemart_media_id = $xrefTable->load((int)$this->_id);
				} else {
					$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->virtuemart_media_id = false;
				}


				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->categorytemplate = $this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->category_template;
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->categorylayout = $this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->category_layout;
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->productlayout = $this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->category_product_layout;
			}

			$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->children = false;
			$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->haschildren = null;
			$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->productcount = false;
			$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->parents = false;
			if($childs){
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->haschildren = $this->hasChildren($this->_id);

				/* Get children if they exist */
				if ($this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->haschildren) {
					//$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->children = $this->getCategories( true, $this->_id );
					$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->children = $this->getChildCategoryList($this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->virtuemart_vendor_id, $this->_id );
				}


				/* Get the product count */
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->productcount = $this->countProducts($this->_id);

				/* Get parent for breatcrumb */
				$this->_cache[$this->_id][$childs.VmLanguage::$currLangTag]->parents = $this->getParentsList($this->_id);
			}

		}

		return $this->_cache[$this->_id][$childs.VmLanguage::$currLangTag];
	}

	/**
	 * Get the list of child categories for a given category, is cached
	 *
	 * @param int $virtuemart_category_id Category id to check for child categories
	 * @return object List of objects containing the child categories
	 *
	 */
	public function getChildCategoryList($vendorId, $virtuemart_category_id,$selectedOrdering = null, $orderDir = null, $useCache = true) {

		if(empty($this) or get_class($this)!='VirtueMartModelCategory'){
			$useCache = false;
		}

		if($selectedOrdering===null){
			if($useCache){
				$selectedOrdering = $this->_selectedOrdering;
			} else {
				$selectedOrdering = VmConfig::get('browse_cat_orderby_field','category_name');
			}
		}

		if(trim($selectedOrdering) == 'c.ordering'){
			$selectedOrdering = 'c.ordering,category_name';
		}

		if(!in_array($selectedOrdering, self::$_validOrderingFields)){
			$selectedOrdering = 'c.ordering,category_name';
		}



		if($orderDir===null){
			if($useCache){
				$orderDir = $this->_selectedOrderingDir;
			} else {
				$orderDir = VmConfig::get('cat_brws_orderby_dir', 'ASC');
			}
		}

		$validOrderingDir = array('ASC','DESC');
		if(!in_array(strtoupper($orderDir), $validOrderingDir)){
			$orderDir = 'ASC';
		}

		static $_childCategoryList = array ();

		$key = (int)$vendorId.'_'.(int)$virtuemart_category_id.$selectedOrdering.$orderDir.VmLanguage::$currLangTag ;
		//We have here our internal key to preven calling of the cache
		if (! array_key_exists ($key,$_childCategoryList)){
			vmSetStartTime('com_virtuemart_cat_childs');

			if($useCache){
				$cache = VmConfig::getCache('com_virtuemart_cat_childs','callback');
				$cache->setCaching(true);
				//vmdebug('Calling cache getChildCategoryListObject');
				$_childCategoryList[$key] = $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryListObject' ),$vendorId, $virtuemart_category_id, $selectedOrdering, $orderDir,VmLanguage::$currLangTag);
			} else {
				$_childCategoryList[$key] = VirtueMartModelCategory::getChildCategoryListObject($vendorId, $virtuemart_category_id, $selectedOrdering, $orderDir,VmLanguage::$currLangTag);
			}

			//vmTime('Time to load cats '.(int)$useCache,'com_virtuemart_cat_childs');
		}

		return $_childCategoryList[$key];
	}

	/**
	 * Be aware we need the lang to assure that the cache works properly. The cache needs all parameters
	 * in the function call to use the right hash
	 *
	 * @author Max Milbers
	 * @param $vendorId
	 * @param $virtuemart_category_id
	 * @param null $selectedOrdering
	 * @param null $orderDir
	 * @param $lang
	 * @return mixed
	 */
	static public function getChildCategoryListObject($vendorId, $virtuemart_category_id,$selectedOrdering = null, $orderDir = null,$lang=false) {

		$query = '';

		$langFields = array('category_name','category_description','metadesc','metakey','customtitle','slug');
		$query .= 'SELECT c.virtuemart_category_id, '.implode(', ',self::joinLangSelectFields($langFields));
		$query .= ' FROM #__virtuemart_categories as c '.implode(' ',self::joinLangTables('#__virtuemart_categories','c','virtuemart_category_id'));

		$query .= ' LEFT JOIN `#__virtuemart_category_categories` as cx on c.`virtuemart_category_id` = cx.`category_child_id` ';
		$query .= ' WHERE cx.`category_parent_id` = ' . (int)$virtuemart_category_id . ' ';
		if(empty($vendorId) and VmConfig::get('multix')!='none'){
			$query .= ' AND c.`shared` = 1' ;
		} else if(!empty($vendorId)){
			$query .= ' AND c.`virtuemart_vendor_id` = ' . (int)$vendorId ;
		}

		$query .= ' AND c.`published` = 1 ';
		$query .= ' ORDER BY '.$selectedOrdering.' '.$orderDir;

		$db = JFactory::getDBO();
		$db->setQuery( $query);
		$childList = $db->loadObjectList();
		if(!empty($childList)){
			if(!class_exists('TableCategory_medias'))require(VMPATH_ADMIN.DS.'tables'.DS.'category_medias.php');
			foreach($childList as $child){
				$xrefTable = new TableCategory_medias($db);
				$child->virtuemart_media_id = $xrefTable->load($child->virtuemart_category_id);
			}
		}

		return $childList;
	}


	public function getCategoryTree($parentId=0, $level = 0, $onlyPublished = true,$keyword = '', $limitStart = '',$limit = ''){

		$sortedCats = array();

		if($limitStart === '' or $limit === ''){
			$limits = $this->setPaginationLimits();
			if($limitStart === '') $limitStart = $limits[0];
			if($limit === '') $limit = $limits[1];
		}

		$this->_noLimit = true;
		if($keyword!=''){
			$sortedCats = self::getCategories($onlyPublished, false, false, $keyword);

			if(!empty($sortedCats)){
				$siblingCount = count($sortedCats);
				foreach ($sortedCats as $key => &$category) {
					$category->siblingCount = $siblingCount;
				}
			}
		} else {
			$this->rekurseCats($parentId,$level,$onlyPublished,$keyword,$sortedCats);
		}

		$this->_noLimit = false;
		$this->_total = count($sortedCats);

		$this->_limitStart = $limitStart;
		$this->_limit = $limit;

		$this->getPagination();

		if(empty($limit)){
			return $sortedCats;
		} else {
			$sortedCats = array_slice($sortedCats, $limitStart,$limit);
			//vmdebug('my $sortedCats sliced by  '.$limitStart.' '.$limit,$sortedCats);
			return $sortedCats;
		}

	}

	public function rekurseCats($virtuemart_category_id,$level,$onlyPublished,$keyword,&$sortedCats,$deep=0){
		$level++;

		if(($deep===0 or $level <= $deep) and $childs = $this->hasChildren($virtuemart_category_id)){

			$childCats = self::getCategories($onlyPublished, $virtuemart_category_id, false, $keyword);
			if(!empty($childCats)){

				$siblingCount = count($childCats);
				foreach ($childCats as $key => $category) {
					$category->level = $level;
					$category->siblingCount = $siblingCount;
					$sortedCats[] = $category;
					$this->rekurseCats($category->virtuemart_category_id,$level,$onlyPublished,$keyword,$sortedCats,$deep);
				}
			}
		}
	}


	public function getCategories($onlyPublished = true, $parentId = false, $childId = false, $keyword = "", $vendorId = false) {

		static $cats = array();

		$select = ' c.`virtuemart_category_id`, c.`ordering`, c.`published`, cx.`category_child_id`, cx.`category_parent_id`, c.`shared` ';

		$joins = ' FROM `#__virtuemart_categories` as c ';

		$where = array();

		if( $onlyPublished ) {
			$where[] = " c.`published` = 1 ";
		}
		if( $parentId !== false ){
			$where[] = ' cx.`category_parent_id` = '. (int)$parentId;
		}

		if( $childId !== false ){
			$where[] = ' cx.`category_child_id` = '. (int)$childId;
		}

		if($vendorId===false){
			$vendorId = vmAccess::isSuperVendor();
		}

		$app = JFactory::getApplication();
		$isSite = true;
		if($app->isAdmin() or (vRequest::getInt('manage',false) and vmAccess::manager('manage')) ){
			$isSite = false;
		}

		if($vendorId!=1){
			if($isSite and $vendorId==0){
				$vendorId = 1;
			}
			$where[] = ' (c.`virtuemart_vendor_id` = "'. (int)$vendorId. '" OR c.`shared` = "1") ';
		}

		$langFields = array('category_description','category_name');

		$select .= ', '.implode(', ',self::joinLangSelectFields($langFields));
		$joins .= implode(' ',self::joinLangTables($this->_maintable,'c','virtuemart_category_id'));

		$joins .= ' LEFT JOIN `#__virtuemart_category_categories` AS cx ON c.`virtuemart_category_id` = cx.`category_child_id`';


		$whereOr = array();
		if( !empty( $keyword ) ) {
			$db = JFactory::getDBO();
			$keyword = $db->escape( $keyword, true );
			$keyword =  '"%' .str_replace(array(' ','-'),'%', $keyword). '%"';
			//$keyword = $db->escape( $keyword, true );
			$fields = self::joinLangLikeFields($langFields,$keyword);
			$whereOr = array_merge($whereOr, $fields);
		}

		$whereString = '';
		if (count($where) > 0 or count($whereOr)){
			$whereString = ' WHERE ';
			if (count($where) > 0){
				$whereString .= implode(' AND ', $where);
				if (count($whereOr) > 0){
					$whereString .= ' AND ';
				}
			}
			if (count($whereOr) > 0){
				$whereString .= '('.implode(' OR ', $whereOr).')';
			}
		} else {
			$whereString = 'WHERE 1 ';
		}

		if(trim($this->_selectedOrdering) == 'c.ordering'){
			$this->_selectedOrdering = 'c.ordering, category_name';
		}
		$ordering = $this->_getOrdering();

		$hash = md5($keyword.'.'.(int)$parentId.VmLanguage::$currLangTag.(int)$childId.$this->_selectedOrderingDir.(int)$vendorId.$this->_selectedOrdering);
		if(!isset($cats[$hash])){
			$cats[$hash] = $this->_category_tree = $this->exeSortSearchListQuery(0,$select,$joins,$whereString,'GROUP BY virtuemart_category_id',$ordering );
		}

		return $cats[$hash];

	}

	/**
	 * count the products in a category
	 *
	 * @author Max Milbers
	 * @return array list of categories product is in
	 */
	public function countProducts($cat_id=0) {

		$db = JFactory::getDBO();
		$vendorId = 1;
		if ($cat_id > 0) {
			$q = 'SELECT count(`p`.virtuemart_product_id) AS total	
  FROM `#__virtuemart_product_categories` as `pc`
  LEFT JOIN `#__virtuemart_products` as `p` ON `pc`.virtuemart_product_id = `p`.virtuemart_product_id
  WHERE `pc`.`virtuemart_category_id` = "'.(int)$cat_id.'"
  AND `p`.`virtuemart_vendor_id` = "'.(int)$vendorId.'"
  AND `p`.`published` = "1" ';
			$db->setQuery($q);
			$count = $db->loadResult();
		} else $count=0 ;

		return $count;
	}


	/**
	 * Order any category
	 *
	 * @author jseros
	 * @param  int $id category id
	 * @param  int $movement movement number
	 * @return bool
	 */
	public function orderCategory($id, $movement){
		//retrieving the category table object
		//and loading data
		$row = $this->getTable('categories');
		$row->load($id);

		$query = 'SELECT `category_parent_id` FROM `#__virtuemart_category_categories` WHERE `category_child_id` = '. (int)$row->virtuemart_category_id ;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$parent = $db->loadObject();

		if (!$row->move( $movement, $parent->category_parent_id)) {
			return false;
		}

		return true;
	}


	/**
	 * Order category group
	 *
	 * @author jseros
	 * @param  array $cats categories to order
	 * @return bool
	 */
	public function setOrder($cats, $order){
		$total		= count( $cats );
		$groupings	= array();
		$row = $this->getTable('categories');

		$query = 'SELECT `category_parent_id` FROM `#__virtuemart_categories` c
				  LEFT JOIN `#__virtuemart_category_categories` cx
				  ON c.`virtuemart_category_id` = cx.`category_child_id`
			      WHERE c.`virtuemart_category_id` = %s';

		$db = JFactory::getDBO();
		// update ordering values
		for( $i=0; $i < $total; $i++ ) {

			$row->load( $cats[$i] );
			$db->setQuery( sprintf($query,  (int)$cats[$i] ), 0 ,1 );
			$parent = $db->loadObject();

			$groupings[] = $parent->category_parent_id;
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->toggle('ordering',$row->ordering)) {
					return false;
				}
			}
		}

		// execute reorder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder($group);
		}

		$this->clearCategoryRelatedCaches();

		return true;
	}

	/**
	 * Retrieve the detail record for the parent category of $categoryd
	 *
	 * @author jseros
	 * @param int $categoryId Child category id
	 * @return JTable parent category data
	 */
	public function getParentCategory( $categoryId = 0 ){
		$data = $this->getRelationInfo( $categoryId );
		$parentId = isset($data->category_parent_id) ? $data->category_parent_id : 0;

		$parent = $this->getTable('categories');
		$parent->load((int) $parentId);

		return $parent;
	}


	/**
	 * Retrieve category child-parent relation record
	 *
	 * @author jseros
	 * @param int $virtuemart_category_id
	 * @return object Record of parent relation
	 */
	public function getRelationInfo( $virtuemart_category_id = 0 ){

		$db = JFactory::getDBO();
		$query = 'SELECT `category_parent_id`, `ordering`
    			  FROM `#__virtuemart_category_categories`
    			  WHERE `category_child_id` = '. (int)$virtuemart_category_id;
		$db->setQuery($query);

		return $db->loadObject();
	}


	/**
	 * Bind the post data to the category table and save it
	 *
	 * @author jseros, Max Milbers
	 * @return int category id stored
	 */
	public function store(&$data) {

		vRequest::vmCheckToken();

		if(!vmAccess::manager('category.edit')){
			vmWarn('Insufficient permission to store category');
			return false;
		} else if( empty($data['virtuemart_category_id']) and !vmAccess::manager('category.create')){
			vmWarn('Insufficient permission to create category');
			return false;
		}

		$table = $this->getTable('categories');

		if ( !array_key_exists ('category_template' , $data ) ){
			$data['category_template'] = $data['category_layout'] = $data['category_product_layout'] = '' ;
		}

		$data['category_template'] = $data['categorytemplate'];
		$data['category_layout'] = $data['categorylayout'];
		$data['category_product_layout'] = $data['productlayout'];

		$table->bindChecknStore($data);

		if(!empty($data['virtuemart_category_id'])){
			$xdata['category_child_id'] = (int)$data['virtuemart_category_id'];
			$xdata['category_parent_id'] = empty($data['category_parent_id'])? 0:(int)$data['category_parent_id'];
			$xdata['ordering'] = empty($data['ordering'])? 0: (int)$data['ordering'];

			$table = $this->getTable('category_categories');

			$table->bindChecknStore($xdata);

		}

		// Process the images
		$mediaModel = VmModel::getModel('Media');
		$file_id = $mediaModel->storeMedia($data,'category');

		$this->clearCategoryRelatedCaches();


		return $data['virtuemart_category_id'] ;
	}

	/**
	 * Delete all categories selected
	 *
	 * @author jseros
	 * @param  array $cids categories to remove
	 * @return boolean if the item remove was successful
	 */
	public function remove($cids) {

		vRequest::vmCheckToken();

		if(!vmAccess::manager('category.delete')){
			vmWarn('Insufficient permissions to delete category');
			return false;
		}

		$table = $this->getTable('categories');

		foreach($cids as &$cid) {

			if (!$table->delete($cid)) {
				return false;
			}

			$db = JFactory::getDbo();
			$q = 'SELECT `virtuemart_customfield_id` FROM `#__virtuemart_product_customfields` as pc ';
			$q .= 'LEFT JOIN `#__virtuemart_customs`as c ON pc.`virtuemart_custom_id` = c.`virtuemart_custom_id` WHERE pc.`customfield_value` = "' . $cid . '" AND `field_type`= "Z"';
			$db->setQuery($q);
			$list = $db->loadColumn();

			if ($list) {
				$listInString = implode(',',$list);
				//Delete media xref
				$query = 'DELETE FROM `#__virtuemart_product_customfields` WHERE `virtuemart_customfield_id` IN ('. $listInString .') ';
				$db->setQuery($query);
				if(!$db->execute()){
					vmError( $db->getErrorMsg() );
				}
			}
		}

		$cidInString = implode(',',$cids);

		//Delete media xref
		$query = 'DELETE FROM `#__virtuemart_category_medias` WHERE `virtuemart_category_id` IN ('. $cidInString .') ';
		$db->setQuery($query);
		if(!$db->execute()){
			vmError( $db->getErrorMsg() );
		}

		//deleting product relations
		$query = 'DELETE FROM `#__virtuemart_product_categories` WHERE `virtuemart_category_id` IN ('. $cidInString .') ';
		$db->setQuery($query);

		if(!$db->execute()){
			vmError( $db->getErrorMsg() );
		}

		//deleting category relations
		$query = 'DELETE FROM `#__virtuemart_category_categories` WHERE `category_child_id` IN ('. $cidInString .') ';
		$db->setQuery($query);

		if(!$db->execute()){
			vmError( $db->getErrorMsg() );
		}

		//updating parent relations
		$query = 'UPDATE `#__virtuemart_category_categories` SET `category_parent_id` = 0 WHERE `category_parent_id` IN ('. $cidInString .') ';
		$db->setQuery($query);

		if(!$db->execute()){
			vmError( $db->getErrorMsg() );
		}

		$this->clearCategoryRelatedCaches();

		return true;
	}

	public function clearCategoryRelatedCaches(){

		$cache = VmConfig::getCache();
		$cache->clean('com_virtuemart_cats');
		$cache->clean('com_virtuemart_cat_childs');
		$cache->clean('mod_virtuemart_product');
		$cache->clean('mod_virtuemart_category');
	}

	/**
	 * Checks for children of the category $virtuemart_category_id
	 *
	 * @param int $virtuemart_category_id the category ID to check
	 * @return boolean true when the category has childs, false when not
	 */
	public function hasChildren($virtuemart_category_id) {

		static $hasChildrenCache=array();
		if(!isset($hasChildrenCache[$virtuemart_category_id])){
			$db = JFactory::getDBO();
			$q = "SELECT `category_child_id`
			FROM `#__virtuemart_category_categories`
			WHERE `category_parent_id` = ".(int)$virtuemart_category_id;
			$db->setQuery($q);
			$db->execute();
			if ($db->getAffectedRows() > 0){
				$hasChildrenCache[$virtuemart_category_id] = true;
			} else {
				$hasChildrenCache[$virtuemart_category_id] = false;
			}
		}
		return $hasChildrenCache[$virtuemart_category_id];
	}

	/**
	 * Creates a bulleted of the childen of this category if they exist
	 *
	 * @todo Add vendor ID
	 * @param int $virtuemart_category_id the category ID to create the list of
	 * @return array containing the child categories
	 */
	public function getParentsList($virtuemart_category_id) {

		$db = JFactory::getDBO();
		$menu = JFactory::getApplication()->getMenu();
		$parents = array();
		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();
		} else {
			$menuItem = $menu->getItem($query['Itemid']);
		}
		$menuCatid = (empty($menuItem->query['virtuemart_category_id'])) ? 0 : $menuItem->query['virtuemart_category_id'];
		if ($menuCatid == $virtuemart_category_id) return ;
		$parents_id = array_reverse($this->getCategoryRecurse($virtuemart_category_id,$menuCatid));


		//$useFb = vmLanguage::getUseLangFallback();
		//$useFb2 = vmLanguage::getUseLangFallbackSecondary();

		$langFields = array('virtuemart_category_id','category_name');

		$select = 'SELECT '.implode(', ',self::joinLangSelectFields($langFields)).', published';
		$joins = 'FROM `#__virtuemart_categories` as c '.implode(' ',self::joinLangTables('#__virtuemart_categories','c','virtuemart_category_id'));

		$where = 'WHERE '.implode(', ',self::joinLangSelectFields(array('virtuemart_category_id'),false)).' = ';
		$q = $select.' '.$joins.' '.$where;

		foreach ($parents_id as $id ) {
			$db->setQuery($q.(int)$id);
			if($db->getErrorMsg()){
				vmError('Error in sql ',$db->getErrorMsg());
			}
			if($cat=$db->loadObject()){
				$parents[] = $cat;
			} else {
				if(VmConfig::$echoAdmin){
					vmWarn('category with id '.(int)$id.' is missing the main language ');
				}

			}
		}

		return $parents;
	}

	private $categoryRecursed = 0;

	public function getCategoryRecurse($virtuemart_category_id,$catMenuId,$idsArr=true ) {

		static $resId = array();

		if(empty($virtuemart_category_id)) return array();

		if($idsArr and !is_array($idsArr)){
			$idsArr = array();
			$this->categoryRecursed = 0;
		} else if($this->categoryRecursed>10){
			vmWarn('Stopped getCategoryRecurse after 10 rekursions');
			return false;
		}

		$hash = $virtuemart_category_id.'c'.$catMenuId;

		if(isset($resId[$hash])){
			$ids = $resId[$hash];
		} else if (!empty($virtuemart_category_id)){
			$db	= JFactory::getDBO();
			$q = "SELECT `category_child_id` AS `child`, `category_parent_id` AS `parent`
				FROM  #__virtuemart_category_categories AS `xref`
				WHERE `xref`.`category_child_id`= ".(int)$virtuemart_category_id;
			$db->setQuery($q);
			$ids = $resId[$hash] = $db->loadObject();
		}

		if (isset ($ids->child)) {
			$idsArr[] = $ids->child;
			if($ids->parent != 0 and $catMenuId != $virtuemart_category_id and $catMenuId != $ids->parent) {
				$this->categoryRecursed++;
				$idsArr = $this->getCategoryRecurse($ids->parent,$catMenuId,$idsArr);
			}
		}
		return $idsArr ;
	}


	function toggle($field,$val = NULL, $cidname = 0,$tablename = 0, $view = false  ) {
		$result = parent::toggle($field,$val, $cidname, $tablename, $view );
		$this->clearCategoryRelatedCaches();
		return $result;
	}

}