<?php

/**
 *
 * @package	VirtueMart
 * @subpackage   Models Fields
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: $
 */
defined('JPATH_BASE') or die;

if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');

jimport('joomla.form.formfield');

/**
 * Supports a modal product picker.
 *
 *
 */
class JFormFieldVmLoadLang extends JFormField {

	var $type = 'loadlang';

	/**
	 * Method to load vm language files, just use the name field, takes also a comma seperated list
	 */
	function getInput() {

		$langs = explode(',',$this->fieldname);
		foreach($langs as $lang){
			vmLanguage::loadJLang(trim($lang));
		}
	}
}