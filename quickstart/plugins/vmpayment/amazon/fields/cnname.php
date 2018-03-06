<?php
/**
 *
 * Amazon payment plugin
 *
 * @author Max Milbers
 * @version $Id: ipnurl.php 9185 2016-02-25 13:51:01Z Milbo $
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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.form.formfield');

class JFormFieldCnname extends JFormField {

	var $type = 'cnname';
	var $class = 'cnname level3';

	protected function getInput() {

		$cname = $this->value;
		if(empty($cname)){
			$this->value = $_SERVER['HTTP_HOST']; //$_SERVER['SERVER_NAME'] //REQUEST_URI
		}
		return '<input type="text" class="required" value="'.$this->value.'" id="params_cnname" name="params[cnname]">';

	}
}