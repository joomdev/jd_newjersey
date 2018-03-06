<?php
defined('_JEXEC') or die();

/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  - Elements
 * @author Valérie Isaksen
 * @version $Id: languages.php 8229 2014-08-23 16:56:12Z alatak $
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
if (!class_exists('VmConfig')) {
	require(VMPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
}

if (!class_exists('ShopFunctions')) {
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
}
if (!class_exists('TableCategories')) {
	require(VMPATH_ADMIN . DS . 'tables' . DS . 'categories.php');
}

if (!class_exists('VmElements')) {
	require(VMPATH_ADMIN . DS . 'elements' . DS . 'vmelements.php');
}
jimport('joomla.form.formfield');

class JFormFieldLanguages extends JFormField {

	var $type = 'Languages';


	protected function getInput() {

		$activeLangs = array();
		$language = JFactory::getLanguage();
		$jLangs = $language->getKnownLanguages(JPATH_BASE);

		foreach ($jLangs as $jLang) {
			$jlangTag = strtolower(strtr($jLang['tag'], '-', '_'));
			$activeLangs[] = JHTML::_('select.option', $jLang['tag'], $jLang['name']);
		}
		return JHTML::_('select.genericlist', $activeLangs, $this->name, 'size="1"', 'value', 'text', $this->value);

	}

}

