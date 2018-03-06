<?php
defined('_JEXEC') or die();
/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

if (!class_exists('ShopFunctions'))
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');

if (!class_exists('TableManufacturers')) require(VMPATH_ADMIN . DS . 'tables' . DS . 'manufacturers.php');
if (!class_exists('VirtueMartModelManufacturer'))
	JLoader::import('manufacturer', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
/*
 * This element is used by the menu manager
 * Should be that way
 */

class JFormFieldManufacturersmenu extends JFormField {


	var $_name = 'manufacturersmenu';

	function getInput() {

		VmConfig::loadConfig();
		vmLanguage::loadJLang('com_virtuemart');
		$model = VmModel::getModel('Manufacturer');
		$manufacturers = $model->getManufacturers(true, true, false);

		return JHtml::_('select.genericlist', $manufacturers, $this->name, 'class="inputbox"   ', 'value', 'text', $this->value, $this->id);

	}

}