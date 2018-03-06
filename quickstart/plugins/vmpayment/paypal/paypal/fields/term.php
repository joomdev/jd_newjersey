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


defined('_JEXEC') or die();

jimport('joomla.form.formfield');

class JFormFieldTerm extends JFormField {

	protected $type = 'Term';

	protected function getInput() {

		$max = 52;
		$options = array();
		for ($i = 1; $i <= $max; $i++) {
			$options[] = JHTML::_('select.option', $i, $i);
		}

		return JHTML::_('select.genericlist', $options, $this->name, 'size="1"', 'value', 'text', $this->value);

	}
}