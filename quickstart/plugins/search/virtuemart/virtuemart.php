<?php
/**
 *
 * A search plugin for com_search
 *
 * @author Valérie Isaksen
 * @author Samuel Mehrbrodt
 * @author Franz-Peter Scherer
 * @version $Id: authorize.php 5122 2011-12-18 22:24:49Z alatak $
 * @package VirtueMart
 * @subpackage search
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved. 2012 The VirtueMart Team
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
// no direct access
defined ('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);


// Get the product => categories.
class plgSearchVirtuemart extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
		VmConfig::loadConfig();
		$this->loadLanguage();
	}

	function onContentSearchAreas () {
		$this->loadLanguage();
		static $areas = array(
		'products' => 'PLG_SEARCH_VIRTUEMART_PRODUCTS'
		);
		return $areas;
	}



	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db = JFactory::getDbo();

		if (is_array($areas)) {
			if (!array_intersect ($areas, array_keys ($this->onContentSearchAreas()))) {
				return array();
			}
		}
		$limit = $this->params->get('search_limit', 50);
		switch($this->params->get('subtitledisplay', '1')) {
			case '1':
				$category_field = 'category_name';
				break;
			case '2':
				$category_field = 'customtitle';
				break;
		}

		$search_product_description = (bool) $this->params->get('enable_product_description_search', TRUE);
		$search_product_s_description = (bool) $this->params->get('enable_product_short_description_search', TRUE);
		$search_customfields = (bool) $this->params->get('enable_customfields', TRUE);
		$customfield_ids_condition = "";
		if ($search_customfields) {
			$value = trim($this->params->get('customfields', ""));

			// Remove all spaces
			$value = str_replace(' ', '', $value);
			if (!empty($value)){
				$customfield_ids = explode(",", $value);

				// Make sure we have only integers
				foreach($customfield_ids as &$id) {
					$id = intval($id);
				}
				// The custom field ID must be either in the list specified or NULL.
				$customfield_ids_condition = "AND cf.virtuemart_custom_id IN (" .
				implode(',', $customfield_ids) . ")";
			}

		}

		$text = trim($text);
		if (empty($text))
			return array();



		switch ($phrase)
		{
			case 'exact':
				$text      = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2   = array();
				$wheres2[] = 'p.product_sku LIKE ' . $text;
				$wheres2[] = 'pd.product_name LIKE ' . $text;
				if ($search_product_s_description)
					$wheres2[] = 'pd.product_s_desc LIKE ' . $text;
				if ($search_product_description)
					$wheres2[] = 'pd.product_desc LIKE ' . $text;
//				$wheres2[] = 'pd.metadesc LIKE ' . $text;
//				$wheres2[] = 'pd.metakey LIKE ' . $text;
				if ($search_customfields)
					$wheres2[] = "(cf.customfield_value LIKE $text $customfield_ids_condition)";
				$where     = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word      = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2   = array();
					$wheres2[] = 'LOWER(pd.product_name) LIKE LOWER(' . $word . ')';
					if ($search_product_s_description)
						$wheres2[] = 'LOWER(pd.product_s_desc) LIKE LOWER(' . $word . ')';
					if ($search_product_description)
						$wheres2[] = 'LOWER(pd.product_desc) LIKE LOWER(' . $word . ')';
//					$wheres2[] = 'LOWER(pd.metadesc) LIKE LOWER(' . $word . ')';
//					$wheres2[] = 'LOWER(pd.metakey) LIKE LOWER(' . $word . ')';
					if ($search_customfields)
						$wheres2[] = 'cf.customfield_value LIKE LOWER(' . $word . ')';
					$wheres[]  = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase === 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$orderBy = 'created ASC';
				break;
			case 'popular':
				$orderBy = 'hits DESC';
				break;
			case 'alpha':
				$orderBy = 'pd.product_name ASC';
				break;
			case 'category':
				$orderBy = 'cd.category_name ASC, a.product_name ASC';
			case 'newest':
				$orderBy = 'created DESC';
				break;
			default :
				$orderBy = 'product_id ASC';
				break;
		}



		$shopper_group_condition="";
		$currentVMuser = VmModel::getModel('user')->getCurrentUser();
		$virtuemart_shoppergroup_ids = (array)$currentVMuser->shopper_groups;

		if (is_array($virtuemart_shoppergroup_ids)) {
			$sgrgroups = array();
			foreach($virtuemart_shoppergroup_ids as $virtuemart_shoppergroup_id) {
				$sgrgroups[] = 'psgr.virtuemart_shoppergroup_id= "' . $virtuemart_shoppergroup_id . '" ';
			}
			$sgrgroups[] = 'psgr.virtuemart_shoppergroup_id IS NULL ';
			$shopper_group_condition = " AND ( " . implode (' OR ', $sgrgroups) . " ) ";
		}
		$uncategorized_products_condition = VmConfig::get('show_uncat_child_products') ?
		'' : ' AND c.virtuemart_category_id > 0 ';


		$query = " SELECT pd.virtuemart_product_id as product_id, " .
		" CONCAT( pd.product_name, ' (', p.product_sku, ')' ) AS title, pd.product_s_desc AS text, p.product_sales as hits, p.created_on AS created, cd.virtuemart_category_id as cat_id" .
		" FROM #__virtuemart_products AS p" .
		" INNER JOIN #__virtuemart_products_" . VmConfig::$vmlang . " AS pd ON pd.virtuemart_product_id = p.virtuemart_product_id " .
		" LEFT JOIN #__virtuemart_product_shoppergroups as psgr ON pd.virtuemart_product_id = psgr.virtuemart_product_id " .
		" LEFT JOIN #__virtuemart_product_categories as xref ON xref.virtuemart_product_id = pd.virtuemart_product_id " .
		" LEFT JOIN #__virtuemart_product_customfields AS cf ON pd.virtuemart_product_id = cf.virtuemart_product_id " .
		" LEFT JOIN #__virtuemart_categories as c ON c.virtuemart_category_id = xref.virtuemart_category_id " .
		" LEFT JOIN #__virtuemart_categories_" . VmConfig::$vmlang . " AS cd ON cd.virtuemart_category_id = c.virtuemart_category_id " .
		" WHERE {$where} " .
		" {$shopper_group_condition} " .
		" {$uncategorized_products_condition}  " .
		" AND p.published = 1" .
		" GROUP BY title" .
		" ORDER BY {$orderBy}";

		$db->setQuery($query, 0, $limit);

		$results = $db->loadObjectList();

		$rs = array();



		if (empty($results))
		{
			return $rs;
		}

		foreach ($results as $result) {


			$c = self::getCategoryNames($result->product_id, $category_field);
			if (!empty($result->title)) {
				$result->title = html_entity_decode($result->title);
			}

			if (empty ($c->cat_id)) {
				$result->href = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' .$result->product_id;

			} else {
				$result->href = 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' .$result->product_id . '&virtuemart_category_id=' . $c->cat_id;

			}

			$result->section = $c->cat_name;
			$result->browsernav = 2;
			$rs[] = $result;
		}

		return $rs;
	}

	protected static function getCategoryNames($id, $category_field)
	{
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);
		$q = "SELECT GROUP_CONCAT(cd." . $category_field . " separator ', ') as cat_name, c.virtuemart_category_id as cat_id " .
		" FROM #__virtuemart_product_categories AS cref " .
		" JOIN #__virtuemart_categories AS c " .
		" ON cref.virtuemart_category_id = c.virtuemart_category_id " .
		" JOIN #__virtuemart_categories_" . VmConfig::$vmlang . " AS cd " .
		" ON cd.virtuemart_category_id = cref.virtuemart_category_id " .
		" WHERE c.published = 1 AND cref.virtuemart_product_id = " . $id . " ";
		$db->setQuery($q);
		$category = $db->loadObject();
		return $category;
	}
}
