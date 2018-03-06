<?php
/**
 * @version $Id: klarnacountrylogo.php 6369 2012-08-22 14:33:46Z alatak $
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

class JFormFieldKlarnaCountryLogo extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'KlarnacountryLogo';

	function getInput() {
		$flag = $this->value;
		if ($this->value == 'NB') {
			$flag = 'NO';
		}
		$flagImg = JURI::root(TRUE) . '/media/mod_languages/images/' . strtolower($flag) . '.gif';

		return '<strong>' . vmText::_('VMPAYMENT_KLARNA_CONF_SETTINGS_' . $this->value) . '</strong><img style="margin-left: 5px;" src="' . $flagImg . '" />';

	}
}