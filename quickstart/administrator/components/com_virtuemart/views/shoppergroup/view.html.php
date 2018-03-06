<?php
/**
 *
 * Shopper group View
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9420 2017-01-12 09:35:36Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for maintaining the list of shopper groups
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler
 */
class VirtuemartViewShopperGroup extends VmViewAdmin {

	function display($tpl = null) {

		// Load the helper(s)
		if (!class_exists('VmHTML')) require(VMPATH_ADMIN.DS.'helpers'.DS.'html.php');

		$model = VmModel::getModel();

		$layoutName = $this->getLayout();

		$task = vRequest::getCmd('task',$layoutName);
		$this->assignRef('task', $task);

		if ($layoutName == 'edit') {
			//For shoppergroup specific price display
			vmLanguage::loadJLang('com_virtuemart_config');
			vmLanguage::loadJLang('com_virtuemart_shoppers',true);
			$shoppergroup = $model->getShopperGroup();
			$this->SetViewTitle('SHOPPERGROUP',$shoppergroup->shopper_group_name);

			if($this->showVendors()){
				$this->vendorList = ShopFunctions::renderVendorList($shoppergroup->virtuemart_vendor_id);
			}

			$this->assignRef('shoppergroup',	$shoppergroup);

			$this->addStandardEditViewCommands();

		} else {
			$this->SetViewTitle();

			$showVendors = $this->showVendors();
			$this->assignRef('showVendors',$showVendors);

			$this->addStandardDefaultViewCommands();
			$this->addStandardDefaultViewLists($model);

			$shoppergroups = $model->getShopperGroups(false, true);
			$this->assignRef('shoppergroups',	$shoppergroups);

			$pagination = $model->getPagination();
			$this->assignRef('sgrppagination', $pagination);

		}
		parent::display($tpl);
	}

} // pure php no closing tag
