<?php
/**
 * @version $Id: klarnalabel.php 6369 2012-08-22 14:33:46Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('JPATH_BASE') or die();

/**
 * Renders a label element
 */
jimport('joomla.form.formfield');
class JFormFieldKlarnaLabel extends JFormField {

	var $type = 'KlarnaLabel';

	function getInput() {
		$class = !empty($this->class)? 'class="' . $this->class . '"' : 'class="text_area"';
		return '<label for="' . $this->name . '"' . $class . '>' . $this->value . '</label>';
	}
}