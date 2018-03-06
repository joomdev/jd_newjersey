<?php
/**
 *
 * Paypal  payment plugin
 *
 * @author Jeremy Magne
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
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


defined('_JEXEC') or die('Restricted access');

if (!class_exists('ShopFunctions')) {
	require(VMPATH_ADMIN . DS.'helpers'.DS.'shopfunctions.php');
}
if (!class_exists('PaypalHelperPaypal')) {
	require(VMPATH_ROOT .   DS  .'plugins'. DS  .'vmpayment'. DS  .'paypal'. DS  .'paypal'. DS  .'helpers'. DS  .'paypal.php');
}

JFormHelper::loadFieldClass('list');
jimport('joomla.form.formfield');

class JFormFieldPaypalCreditcards extends JFormFieldList {

	protected $type = 'Paypalcreditcards';


	protected function getOptions() {

		$creditcards = PaypalHelperPaypal::getPaypalCreditCards();

		$prefix = 'VMPAYMENT_PAYPAL_CC_';

		foreach ($creditcards as $creditcard) {
			$options[] = JHtml::_('select.option', $creditcard, vmText::_($prefix . strtoupper($creditcard)));
		}

		return $options;
	}


}