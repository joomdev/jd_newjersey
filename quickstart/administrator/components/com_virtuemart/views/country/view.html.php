<?php
/**
*
* Country View
*
* @package	VirtueMart
* @subpackage Country
* @author RickG
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
 * HTML View class for maintaining the list of countries
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartViewCountry extends VmViewAdmin {

    function display($tpl = null) {

		vmLanguage::loadJLang('com_virtuemart_countries');

		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		$model = VmModel::getModel('country');
		$zoneModel = VmModel::getModel('worldzones');
		$this->SetViewTitle();

		$layoutName = vRequest::getCmd('layout', 'default');
		if ($layoutName == 'edit') {
			$this->country = $model->getData();
			$this->wzsList = $zoneModel->getWorldZonesSelectList();
			$this->addStandardEditViewCommands();
		}
		else {

			$this->addStandardDefaultViewCommands(true,false);

			//First the view lists, it sets the state of the model
			$this->addStandardDefaultViewLists($model,0,'ASC');

			$filter_country = vRequest::getCmd('filter_country', false);
			$this->countries = $model->getCountries(false, false, $filter_country);
			$this->pagination = $model->getPagination();

		}

		parent::display($tpl);
    }

}
// pure php no closing tag
