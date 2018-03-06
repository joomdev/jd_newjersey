<?php
defined('_JEXEC') or die('Restricted access');

/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: creditcards.php 9560 2017-05-30 14:13:21Z Milbo $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */


if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
if (!class_exists('ShopFunctions')) {
	require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
}
if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

if (!class_exists('RealexHelperRealex')) {
	require(VMPATH_ROOT . DS.'plugins'. DS .'vmpayment'. DS .'realex_hpp_api'. DS .'realex_hpp_api'. DS .'helpers'. DS .'helper.php');
}
/**
 * @copyright    Copyright (C) 2009 Open Source Matters. All rights reserved.
 * @license    GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a multiple item select element
 *
 */
JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldCreditCards extends JFormFieldList {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */

	public $type = 'creditcards';

	protected function getOptions() {

		$creditcards = RealexHelperRealex::getRealexCreditCards();

		$prefix = 'VMPAYMENT_REALEX_HPP_API_CC_';

		foreach ($creditcards as $creditcard) {
			$options[] = JHtml::_('select.option', $creditcard, vmText::_($prefix . strtoupper($creditcard)));
		}

		return $options;
	}

}

